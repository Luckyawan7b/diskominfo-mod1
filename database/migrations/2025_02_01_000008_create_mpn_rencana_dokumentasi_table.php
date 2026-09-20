<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_rencana_dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_pengetahuan_id')->unique()->constrained('mpn_pengetahuan')->cascadeOnDelete();
            $table->boolean('target_tahun_ini')->default(false);
            $table->string('pemilik_pengetahuan')->nullable();
            $table->boolean('tipe_teks')->default(false);
            $table->boolean('tipe_gambar')->default(false);
            $table->boolean('tipe_audio')->default(false);
            $table->boolean('tipe_video')->default(false);
            $table->string('penanggung_jawab')->nullable();
            $table->string('target_waktu_dokumentasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_rencana_dokumentasi');
    }
};
