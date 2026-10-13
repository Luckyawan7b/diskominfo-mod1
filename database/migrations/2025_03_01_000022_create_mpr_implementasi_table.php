<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_implementasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->unique()->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('jangka_waktu')->nullable();
            $table->string('pic_implementasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_implementasi');
    }
};
