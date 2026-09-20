<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mr_konteks', function (Blueprint $table) {
            // Drop duplicate combination of (layanan_id, tahun_penilaian) if any exists,
            // though normally this is handled manually if data exists.
            // Assuming no conflicting production data since we use migrate:fresh.
            $table->unique(['layanan_id', 'tahun_penilaian'], 'mr_konteks_layanan_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mr_konteks', function (Blueprint $table) {
            $table->dropUnique('mr_konteks_layanan_tahun_unique');
        });
    }
};
