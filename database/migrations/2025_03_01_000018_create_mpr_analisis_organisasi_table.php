<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_analisis_organisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->unique()->constrained('mpr_perubahan')->cascadeOnDelete();
            // Kesimpulan otomatis (MprKesimpulanOrganisasi)
            $table->foreignId('ref_ukuran_kompleksitas_id')
                ->nullable()
                ->constrained('ref_ukuran_kompleksitas', indexName: 'mpr_anal_org_ukuran_fk')
                ->nullOnDelete();
            // Snapshot definisi ukuran kompleksitas
            $table->text('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_analisis_organisasi');
    }
};
