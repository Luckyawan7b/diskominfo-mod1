<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_indikator_keberhasilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')
                ->constrained('mpr_perubahan', indexName: 'mpr_ik_perubahan_fk')
                ->cascadeOnDelete();
            $table->text('indikator');
            $table->string('satuan')->nullable();
            $table->decimal('target', 12, 4)->nullable();
            $table->decimal('realisasi', 12, 4)->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_indikator_keberhasilan');
    }
};
