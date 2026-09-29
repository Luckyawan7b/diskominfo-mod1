<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            // Tambah kolom setelah id_pengetahuan
            $table->string('nama_pengetahuan', 255)->nullable()->after('id_pengetahuan');
        });

        // Backfill: isi nama_pengetahuan dari mpn_pengetahuan untuk data yang sudah ada
        DB::statement('
            UPDATE mpn_pengumpulan p
            JOIN mpn_pengetahuan k ON k.id = p.mpn_pengetahuan_id
            SET p.nama_pengetahuan = k.nama_pengetahuan
            WHERE p.nama_pengetahuan IS NULL
              AND p.deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            $table->dropColumn('nama_pengetahuan');
        });
    }
};
