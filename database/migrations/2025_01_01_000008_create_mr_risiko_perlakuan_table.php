<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_risiko_perlakuan', function (Blueprint $table) {
            $table->id();
            // 1:1 dengan mr_risiko
            $table->foreignId('mr_risiko_id')->unique()->constrained('mr_risiko')->cascadeOnDelete();
            $table->enum('keputusan_perlakuan', [
                'Menerima risiko',
                'Mengurangi risiko',
                'Membagi risiko',
                'Menghindari risiko',
            ])->nullable();
            $table->text('deskripsi_detail_perlakuan')->nullable();
            $table->string('waktu_rencana_perlakuan')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_risiko_perlakuan');
    }
};
