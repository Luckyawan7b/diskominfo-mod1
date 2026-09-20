<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_evaluasi_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_indikator_capaian_id')->unique()->constrained('mpn_indikator_capaian')->cascadeOnDelete();
            $table->decimal('realisasi', 8, 2)->nullable();
            $table->text('analisis')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('pelaksana_terkait')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_evaluasi_indikator');
    }
};
