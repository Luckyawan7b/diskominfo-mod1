<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_pemanfaatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_pengumpulan_id')->constrained('mpn_pengumpulan')->cascadeOnDelete();
            $table->date('tanggal_pemanfaatan')->nullable();
            $table->enum('jenis_pengguna', ['Publik', 'Internal'])->nullable();
            $table->string('unit_pengguna')->nullable();
            $table->text('tujuan_pemanfaatan')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_pemanfaatan');
    }
};
