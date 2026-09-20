<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_konteks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained()->cascadeOnDelete();
            $table->year('tahun_penilaian');
            $table->year('tahun_pelaksanaan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['layanan_id', 'tahun_penilaian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_konteks');
    }
};
