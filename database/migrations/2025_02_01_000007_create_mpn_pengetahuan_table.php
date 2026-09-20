<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_pengetahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_konteks_id')->constrained('mpn_konteks')->cascadeOnDelete();
            $table->string('nama_sub_fitur'); // NOT NULL
            $table->boolean('layanan_prioritas')->default(false);
            $table->text('nama_pengetahuan'); // NOT NULL
            $table->enum('sudah_terdokumentasi', ['Sudah', 'Belum'])->nullable();
            $table->foreignId('ref_aspek_pemdi_id')->nullable()->constrained('ref_aspek_pemdi')->nullOnDelete();
            $table->foreignId('ref_indikator_pemdi_id')->nullable()->constrained('ref_indikator_pemdi')->nullOnDelete();
            $table->boolean('apakah_terdokumentasi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_pengetahuan');
    }
};
