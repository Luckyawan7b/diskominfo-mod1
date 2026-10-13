<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_analisis_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            // Denormalisasi untuk cek keunikan; selalu ditimpa observer dari perubahan->konteks->layanan_id
            $table->foreignId('layanan_id')->constrained()->cascadeOnDelete();
            $table->string('kode_risiko', 100);
            $table->enum('sumber_risiko', ['baru', 'dari_mr'])->default('baru');
            $table->foreignId('mr_risiko_id')
                ->nullable()
                ->constrained('mr_risiko')
                ->nullOnDelete();
            $table->text('risiko');
            $table->text('penyebab')->nullable();
            $table->unsignedTinyInteger('level_kemungkinan')->nullable();
            $table->unsignedTinyInteger('level_dampak')->nullable();
            $table->unsignedTinyInteger('besaran_risiko')->nullable();
            $table->text('perencanaan_keberlangsungan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            // Bukan unique: unique MySQL tidak mengabaikan baris soft-deleted. Keunikan ditegakkan di aplikasi.
            $table->index(['layanan_id', 'kode_risiko'], 'mpr_risiko_layanan_kode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_analisis_risiko');
    }
};
