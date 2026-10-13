<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_konteks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained()->cascadeOnDelete();
            $table->string('nama_instansi');
            $table->string('nama_opd_terkait')->nullable();
            $table->year('tahun_penilaian');
            $table->string('periode_perencanaan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['layanan_id', 'tahun_penilaian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_konteks');
    }
};
