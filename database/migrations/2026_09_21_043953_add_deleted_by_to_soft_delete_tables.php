<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom deleted_by (nullable FK ke users) ke semua tabel yang pakai SoftDeletes,
     * supaya admin tahu siapa yang menghapus data.
     */
    public function up(): void
    {
        $tables = [
            'mr_risiko',
            'mr_konteks',
            'layanans',
            'mpn_konteks',
            'mpn_pengetahuan',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('deleted_by')
                    ->nullable()
                    ->after('deleted_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'mr_risiko',
            'mr_konteks',
            'layanans',
            'mpn_konteks',
            'mpn_pengetahuan',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeignIdFor(\App\Models\User::class, 'deleted_by');
                $t->dropColumn('deleted_by');
            });
        }
    }
};
