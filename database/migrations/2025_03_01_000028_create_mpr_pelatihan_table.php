<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_pelatihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->string('nama_pelatihan');
            $table->string('target_peserta')->nullable();
            $table->unsignedInteger('target_jumlah')->nullable();
            $table->string('metode_pelatihan')->nullable();
            $table->date('tanggal_pelaksanaan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_pelatihan');
    }
};
