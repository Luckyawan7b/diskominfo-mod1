<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_indikator_capaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_konteks_id')->constrained('mpn_konteks')->cascadeOnDelete();
            $table->smallInteger('urutan')->default(0);
            $table->string('indikator');
            $table->decimal('kondisi_as_is', 8, 2)->nullable();
            $table->decimal('kondisi_to_be', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_indikator_capaian');
    }
};
