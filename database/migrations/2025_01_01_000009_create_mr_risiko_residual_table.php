<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_risiko_residual', function (Blueprint $table) {
            $table->id();
            // 1:1 dengan mr_risiko
            $table->foreignId('mr_risiko_id')->unique()->constrained('mr_risiko')->cascadeOnDelete();
            $table->unsignedTinyInteger('level_kemungkinan')->nullable(); // 1-5 setelah perlakuan
            $table->unsignedTinyInteger('level_dampak')->nullable();      // 1-5 setelah perlakuan
            $table->unsignedTinyInteger('besaran_risiko')->nullable();    // 1-25 setelah perlakuan
            $table->text('keterangan_residual')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_risiko_residual');
    }
};
