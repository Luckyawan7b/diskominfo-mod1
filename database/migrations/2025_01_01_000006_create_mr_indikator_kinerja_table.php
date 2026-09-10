<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_indikator_kinerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mr_sasaran_upr_id')->constrained('mr_sasaran_upr')->cascadeOnDelete();
            $table->string('indikator_kinerja')->nullable();
            $table->string('target_kinerja')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_indikator_kinerja');
    }
};
