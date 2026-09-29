<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghapus constraint UNIQUE dari kolom id_pengetahuan pada tabel mpn_pengumpulan.
     *
     * Latar belakang: MySQL UNIQUE index tidak mengabaikan baris yang sudah soft-deleted
     * (deleted_at IS NOT NULL), sehingga INSERT dengan ID yang pernah dipakai sebelumnya
     * (lalu dihapus) akan ditolak di level database, menyebabkan UniqueConstraintViolationException.
     *
     * Keunikan id_pengetahuan sekarang ditangani sepenuhnya di layer aplikasi (Laravel Validation)
     * dengan Rule::unique(...)->whereNull('deleted_at'), sehingga ID yang sudah
     * soft-deleted dapat digunakan kembali oleh data baru.
     */
    public function up(): void
    {
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            $table->dropUnique('mpn_pengumpulan_id_pengetahuan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hanya bisa di-restore jika tidak ada data duplikat di kolom id_pengetahuan
        Schema::table('mpn_pengumpulan', function (Blueprint $table) {
            $table->unique('id_pengetahuan');
        });
    }
};
