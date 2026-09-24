<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrasiLokasiPenyimpanan extends Command
{
    protected $signature = "mpn:migrasi-lokasi-penyimpanan
                            {--dry-run : Tampilkan data yang akan dimigrasi tanpa mengubah database}";

    protected $description = "Migrasi data lama lokasi_penyimpanan_lain yang berupa teks bebas ke format dropdown baru.";

    private array $opsiResmi = [
        "Database Manajemen Risiko",
        "Database Manajemen Keberlangsungan",
        "Database Manajemen Relasi Pengguna",
        "Database Manajemen Perubahan",
        "SIMPAN",
        "Lainnya",
    ];

    public function handle(): int
    {
        $isDryRun = $this->option("dry-run");

        if ($isDryRun) {
            $this->warn("Mode DRY-RUN -- tidak ada perubahan yang akan disimpan ke database.");
        }

        $records = DB::table("mpn_pengumpulan")
            ->whereNotNull("lokasi_penyimpanan_lain")
            ->where("lokasi_penyimpanan_lain", "!=", "")
            ->whereNotIn("lokasi_penyimpanan_lain", $this->opsiResmi)
            ->select("id", "id_pengetahuan", "lokasi_penyimpanan_lain", "keterangan_lokasi_lainnya")
            ->get();

        if ($records->isEmpty()) {
            $this->info("Tidak ada data lama yang perlu dimigrasi.");
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$records->count()} record yang akan dimigrasi:");
        $this->table(
            ["ID", "ID Pengetahuan", "Nilai Lama (lokasi_penyimpanan_lain)", "Keterangan Sebelumnya"],
            $records->map(fn($r) => [
                $r->id,
                $r->id_pengetahuan,
                $r->lokasi_penyimpanan_lain,
                $r->keterangan_lokasi_lainnya ?? "(kosong)",
            ])->toArray()
        );

        if ($isDryRun) {
            $this->warn("Dry-run selesai. Jalankan tanpa --dry-run untuk mengeksekusi migrasi.");
            return self::SUCCESS;
        }

        if (!$this->confirm("Lanjutkan migrasi {$records->count()} record ke format baru?")) {
            $this->info("Migrasi dibatalkan.");
            return self::SUCCESS;
        }

        $berhasil = 0;
        $gagal    = 0;

        DB::transaction(function () use ($records, &$berhasil, &$gagal) {
            foreach ($records as $record) {
                try {
                    DB::table("mpn_pengumpulan")
                        ->where("id", $record->id)
                        ->update([
                            "keterangan_lokasi_lainnya" => $record->lokasi_penyimpanan_lain,
                            "lokasi_penyimpanan_lain"   => "Lainnya",
                            "updated_at"                => now(),
                        ]);
                    $berhasil++;
                    $this->line("  OK ID {$record->id} ({$record->id_pengetahuan}) -> lokasi=Lainnya, keterangan={$record->lokasi_penyimpanan_lain}");
                } catch (\Throwable $e) {
                    $gagal++;
                    $this->error("  GAGAL ID {$record->id}: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("Migrasi selesai: {$berhasil} record berhasil, {$gagal} gagal.");

        return $gagal === 0 ? self::SUCCESS : self::FAILURE;
    }
}