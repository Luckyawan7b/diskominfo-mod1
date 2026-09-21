<?php

namespace App\Livewire\Admin;

use App\Models\Layanan;
use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TrashIndex extends Component
{
    public string $activeTab = 'layanan';

    // Untuk force delete — konfirmasi ketik ulang nama item
    public ?int    $confirmingForceDeleteId   = null;
    public string  $confirmingForceDeleteType = '';
    public string  $confirmingForceDeleteName = '';
    public string  $forceDeleteInput          = '';

    /**
     * Whitelist mapping tab key → model class.
     * JANGAN terima nama class mentah dari request.
     */
    private function modelMap(): array
    {
        return [
            'layanan'       => Layanan::class,
            'mr_konteks'    => MrKonteks::class,
            'mr_risiko'     => MrRisiko::class,
            'mpn_konteks'   => MpnKonteks::class,
            'mpn_pengetahuan' => MpnPengetahuan::class,
            'user'          => User::class,
        ];
    }

    private function resolveModel(string $type): string
    {
        $map = $this->modelMap();

        if (! array_key_exists($type, $map)) {
            abort(400, 'Tipe data tidak valid.');
        }

        return $map[$type];
    }

    // ─── Restore ─────────────────────────────────────────────────────────────

    public function restore(string $type, int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $modelClass = $this->resolveModel($type);
        $modelClass::onlyTrashed()->findOrFail($id)->restore();

        session()->flash('success', 'Data berhasil dipulihkan.');
    }

    // ─── Force Delete ─────────────────────────────────────────────────────────

    public function openForceDeleteModal(string $type, int $id, string $name): void
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->confirmingForceDeleteId   = $id;
        $this->confirmingForceDeleteType = $type;
        $this->confirmingForceDeleteName = $name;
        $this->forceDeleteInput          = '';
    }

    public function closeForceDeleteModal(): void
    {
        $this->confirmingForceDeleteId   = null;
        $this->confirmingForceDeleteType = '';
        $this->confirmingForceDeleteName = '';
        $this->forceDeleteInput          = '';
    }

    public function executeForceDelete(): void
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($this->forceDeleteInput !== $this->confirmingForceDeleteName) {
            $this->addError('forceDeleteInput', 'Nama tidak cocok. Ketik ulang dengan tepat untuk mengkonfirmasi.');
            return;
        }

        $modelClass = $this->resolveModel($this->confirmingForceDeleteType);
        $modelClass::onlyTrashed()->findOrFail($this->confirmingForceDeleteId)->forceDelete();

        $this->closeForceDeleteModal();
        session()->flash('success', 'Data berhasil dihapus secara permanen.');
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $data = match ($this->activeTab) {
            'layanan'         => Layanan::onlyTrashed()->with('deletedBy')->latest('deleted_at')->get(),
            'mr_konteks'      => MrKonteks::onlyTrashed()->with(['deletedBy', 'layanan'])->latest('deleted_at')->get(),
            'mr_risiko'       => MrRisiko::onlyTrashed()->with(['deletedBy', 'konteks'])->latest('deleted_at')->get(),
            'mpn_konteks'     => MpnKonteks::onlyTrashed()->with(['deletedBy', 'layanan'])->latest('deleted_at')->get(),
            'mpn_pengetahuan' => MpnPengetahuan::onlyTrashed()->with(['deletedBy', 'konteks'])->latest('deleted_at')->get(),
            'user'            => User::onlyTrashed()->with('deletedBy')->latest('deleted_at')->get(),
            default           => collect(),
        };

        return view('livewire.admin.trash-index', [
            'trashedData' => $data,
            'breadcrumb'  => [
                'Admin'           => null,
                'Tempat Sampah'   => null,
            ],
        ]);
    }
}
