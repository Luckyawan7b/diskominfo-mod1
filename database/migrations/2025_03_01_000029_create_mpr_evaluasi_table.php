<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_evaluasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->unique()->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->enum('hasil', ['Berhasil', 'Tidak Berhasil'])->nullable();
            $table->text('lesson_learned')->nullable();
            $table->text('rekomendasi_solusi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_evaluasi');
    }
};
