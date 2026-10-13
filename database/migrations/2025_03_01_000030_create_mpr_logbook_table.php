<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_logbook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->unique()->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->string('unit_pelaksana')->nullable();
            $table->string('pic_pelaksana')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->string('bukti_url')->nullable();
            $table->enum('status_perubahan', ['Berhasil', 'Gagal', 'Dibatalkan'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_logbook');
    }
};
