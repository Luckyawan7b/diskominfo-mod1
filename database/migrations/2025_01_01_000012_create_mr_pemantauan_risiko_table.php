<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_pemantauan_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mr_risiko_id')->constrained('mr_risiko')->cascadeOnDelete();
            $table->enum('periode', ['semester_1', 'semester_2']);
            $table->year('tahun');
            $table->text('hasil_pelaksanaan')->nullable();
            $table->text('data_dukung_catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Hanya 1 catatan pemantauan per risiko per periode per tahun
            $table->unique(['mr_risiko_id', 'periode', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_pemantauan_risiko');
    }
};
