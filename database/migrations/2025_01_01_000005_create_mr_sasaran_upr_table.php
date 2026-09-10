<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_sasaran_upr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mr_konteks_id')->constrained('mr_konteks')->cascadeOnDelete();
            $table->foreignId('ref_sasaran_nasional_id')->nullable()
                ->constrained('ref_sasaran_nasional')->nullOnDelete();
            $table->text('sasaran_upr')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_sasaran_upr');
    }
};
