<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_analisis_prioritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_konteks_id')->constrained('mpr_konteks')->cascadeOnDelete();
            // Snapshot otomatis dari layanans.nama (diisi hook creating model)
            $table->string('nama_layanan_prioritas');
            $table->text('kondisi_perlu_berubah');
            $table->text('as_is')->nullable();
            $table->text('to_be')->nullable();
            // Input manual, bukan auto-rank
            $table->unsignedSmallInteger('prioritas')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_analisis_prioritas');
    }
};
