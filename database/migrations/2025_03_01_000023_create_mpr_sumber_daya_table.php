<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_sumber_daya', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            // mis. Manusia / TIK / Lainnya
            $table->string('jenis');
            $table->text('deskripsi');
            $table->decimal('alokasi_anggaran', 15, 2)->nullable();
            $table->string('skema_pembiayaan')->nullable();
            $table->string('pic')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_sumber_daya');
    }
};
