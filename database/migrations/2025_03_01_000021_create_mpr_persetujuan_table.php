<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->enum('tahap', ['analisis_teknis', 'analisis_organisasi', 'pelaksanaan']);
            $table->enum('status', ['Disetujui', 'Tidak Disetujui'])->nullable();
            $table->string('pic_penyetuju')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();

            $table->unique(['mpr_perubahan_id', 'tahap'], 'mpr_persetujuan_tahap_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_persetujuan');
    }
};
