<?php

namespace App\Livewire\Admin\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UserIndex extends Component
{
    public string $search     = '';
    public bool   $showModal  = false;
    public ?int   $editingId  = null;

    public string  $name       = '';
    public string  $email      = '';
    public string  $password   = '';
    public ?int    $role_id    = null;
    public string  $nama_dinas = '';
    public string  $alias      = '';

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'role_id', 'nama_dinas', 'alias']);
        $operatorRole = Role::where('name', 'operator')->first();
        $this->role_id = $operatorRole?->id;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId  = $user->id;
        $this->name       = $user->name;
        $this->email      = $user->email;
        $this->password   = '';
        $this->role_id    = $user->role_id;
        $this->nama_dinas = $user->nama_dinas ?? '';
        $this->alias      = $user->alias ?? '';
        $this->showModal  = true;
    }

    public function save(): void
    {
        $operatorRole = Role::where('name', 'operator')->first();
        $isOperator   = $this->role_id == $operatorRole?->id;

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $this->editingId,
            'role_id'   => 'required|exists:roles,id',
            'nama_dinas'=> $isOperator ? 'required|string|max:255' : 'nullable|string|max:255',
            'alias'     => 'nullable|string|max:50',
        ];

        if (! $this->editingId) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $this->validate($rules);

        $selectedRole = Role::find($this->role_id);
        // Admin tidak perlu nama_dinas/alias
        $finalNamaDinas = ($selectedRole && $selectedRole->name === 'admin') ? null : ($this->nama_dinas ?: null);
        $finalAlias     = ($selectedRole && $selectedRole->name === 'admin') ? null : ($this->alias ?: null);

        $data = [
            'name'       => $this->name,
            'email'      => $this->email,
            'role_id'    => $this->role_id,
            'nama_dinas' => $finalNamaDinas,
            'alias'      => $finalAlias,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        session()->flash('success', 'Data User berhasil disimpan.');
    }

    public function deleteUser(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('success', 'User berhasil dihapus.');
    }

    public function render()
    {
        $query = User::with(['role']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('nama_dinas', 'like', "%{$this->search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return view('livewire.admin.user.index', [
            'users'      => $users,
            'roles'      => Role::all(),
            'breadcrumb' => [
                'Admin'       => null,
                'Kelola User' => null,
            ],
        ]);
    }
}
