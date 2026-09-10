<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_kolom_tambahan', function (Blueprint $table) {
            $table->id();
            // 1:1 dengan mr_risiko (Bagian E: data SPBE Digital)
            $table->foreignId('mr_risiko_id')->unique()->constrained('mr_risiko')->cascadeOnDelete();
            $table->string('layanan_pendukung')->nullable();
            $table->enum('layanan_prioritas', ['Prioritas', 'Tematik', 'Instansional'])->nullable();
            $table->enum('pemilik_layanan', ['Pusat', 'Instansi lain', 'Milik sendiri'])->nullable();
            $table->enum('strategis_atau_operasional', ['Strategis', 'Operasional'])->nullable();
            $table->boolean('lintas_sektor')->default(false);
            $table->string('ippd_terkait')->nullable();
            $table->boolean('membutuhkan_perubahan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_kolom_tambahan');
    }
};
