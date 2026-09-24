<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perbaikan: Pisahkan dua konsep bisnis yang semula digabung dalam satu kolom.
     *
     * Kolom LAMA: status_publikasi_simpan (berisi nilai tidak valid 'Disimpan'/'Publikasi')
     *
     * Setelah migrasi:
     *   - status_publikasi_simpan → ENUM valid: Draft|Ditolak|Dipublikasikan|Diarsipkan
     *   - visibilitas_dokumen     → ENUM baru:  Publik|Internal
     */
    public function up(): void
    {
        // Langkah 1: Perbaiki enum status_publikasi_simpan.
        // DB masih fresh/kosong sehingga tidak ada backfill yang diperlukan.
        DB::statement("
            ALTER TABLE mpn_pengumpulan
            MODIFY status_publikasi_simpan
            ENUM('Draft','Ditolak','Dipublikasikan','Diarsipkan') NULL
        ");

        // Langkah 2: Tambah kolom visibilitas_dokumen (nullable).
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            $table->enum('visibilitas_dokumen', ['Publik', 'Internal'])
                ->nullable()
                ->after('status_publikasi_simpan');
        });
    }

    public function down(): void
    {
        // Hapus kolom visibilitas_dokumen
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            $table->dropColumn('visibilitas_dokumen');
        });

        // Kembalikan enum status_publikasi_simpan ke definisi asli
        // (termasuk nilai tidak valid — hanya untuk rollback, bukan production)
        DB::statement("
            ALTER TABLE mpn_pengumpulan
            MODIFY status_publikasi_simpan
            ENUM('Draft','Ditolak','Dipublikasikan','Diarsipkan') NULL
        ");
    }
};
