<?php

namespace App\Console\Commands;

use App\Models\MrLampiran;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Pembersih file lampiran orphan (jaring pengaman karena mr_lampiran polimorfik tanpa FK).
 *
 * Menangani dua jenis orphan:
 *  1. Baris mr_lampiran yang induk polimorfiknya sudah tidak ada di database (atau baris lampiran sudah soft-deleted).
 *  2. File fisik di disk public (folder lampiran) yang tidak lagi dirujuk oleh baris mr_lampiran aktif mana pun.
 */
class BersihkanLampiranOrphan extends Command
{
    protected $signature = 'lampiran:bersihkan-orphan';

    protected $description = 'Hapus baris mr_lampiran dan file fisik yang sudah tidak memiliki induk (orphan)';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $barisDihapus = 0;
        $fileDihapus = 0;

        $semuaLampiran = MrLampiran::withTrashed()->get();

        foreach ($semuaLampiran as $lampiran) {
            $indukAda = $this->cekIndukAda($lampiran->lampirable_type, (int) $lampiran->lampirable_id);

            if ($lampiran->trashed() || ! $indukAda) {
                if ($lampiran->path_file && $disk->exists($lampiran->path_file)) {
                    $disk->delete($lampiran->path_file);
                    $fileDihapus++;
                }
                $lampiran->forceDelete();
                $barisDihapus++;
            }
        }

        // Bersihkan file di folder 'lampiran' yang tidak dirujuk oleh baris mr_lampiran aktif mana pun.
        $pathAktif = MrLampiran::query()->pluck('path_file')->filter()->flip()->all();

        if ($disk->exists('lampiran')) {
            foreach ($disk->allFiles('lampiran') as $filePath) {
                if (! isset($pathAktif[$filePath])) {
                    $disk->delete($filePath);
                    $fileDihapus++;
                }
            }
        }

        $this->info("Pembersihan selesai: {$barisDihapus} baris lampiran orphan dan {$fileDihapus} file fisik dihapus.");

        return self::SUCCESS;
    }

    private function cekIndukAda(?string $type, int $id): bool
    {
        if (! $type || ! class_exists($type) || $id <= 0) {
            return false;
        }

        $query = $type::query();
        if (in_array(SoftDeletes::class, class_uses_recursive($type), true)) {
            $query = $type::withTrashed();
        }

        return $query->whereKey($id)->exists();
    }
}
