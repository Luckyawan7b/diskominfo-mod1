<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mr_konteks_id')->constrained('mr_konteks')->cascadeOnDelete();
            $table->foreignId('mr_sasaran_upr_id')->nullable()->constrained('mr_sasaran_upr')->nullOnDelete();

            // Kategori risiko bertipe string / input teks (bukan dropdown)
            $table->string('kategori_risiko')->nullable();

            // Snapshot teks sasaran saat risiko dibuat
            $table->text('sasaran_pembangunan_nasional_snapshot')->nullable();
            $table->text('sasaran_upr_snapshot')->nullable();
            $table->string('indikator_kinerja_snapshot')->nullable();

            $table->string('kode_risiko');
            $table->text('peristiwa_risiko');
            $table->text('penyebab')->nullable();
            $table->text('dampak')->nullable();
            $table->enum('area_dampak', [
                'Penurunan Reputasi',
                'Keuangan',
                'Gangguan Terhadap Layanan Organisasi',
                'Penurunan Kinerja',
            ])->nullable();

            // Skala 1-5, diisi operator; besaran_risiko dihitung otomatis
            $table->unsignedTinyInteger('level_kemungkinan')->nullable();
            $table->unsignedTinyInteger('level_dampak')->nullable();
            $table->unsignedTinyInteger('besaran_risiko')->nullable();

            // Ranking prioritas diisi otomatis dari urutan besaran kritis risiko (besaran_risiko DESC)
            $table->unsignedSmallInteger('prioritas_risiko')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['mr_konteks_id', 'kode_risiko']);
            $table->index(['mr_konteks_id', 'besaran_risiko']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_risiko');
    }
};
