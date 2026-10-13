<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_ukuran_kompleksitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->text('definisi')->nullable();
            // Tidak Ada=0, Rendah=1, Sedang=2, Tinggi=3
            $table->unsignedTinyInteger('bobot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_ukuran_kompleksitas');
    }
};
