<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_alih_pengetahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_pengumpulan_id')->constrained('mpn_pengumpulan')->cascadeOnDelete();
            $table->string('tanggal_kegiatan')->nullable();
            $table->boolean('metode_pelatihan')->default(false);
            $table->boolean('metode_workshop')->default(false);
            $table->boolean('metode_sosialisasi')->default(false);
            $table->boolean('metode_mentoring')->default(false);
            $table->boolean('metode_sharing')->default(false);
            $table->boolean('metode_lainnya')->default(false);
            $table->text('keterangan_lainnya')->nullable();
            $table->string('penerima_pengetahuan')->nullable();
            $table->text('hasil_evaluasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_alih_pengetahuan');
    }
};
