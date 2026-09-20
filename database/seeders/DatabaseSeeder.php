<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed urutan dependency:
     * roles → users (butuh roles)
     */
    public function run(): void
    {
        // ── Data master (wajib, semua environment) ──────────────────────────
        $this->call([
            RoleSeeder::class,
            RefAspekPemdiSeeder::class,
            RefIndikatorPemdiSeeder::class,
            RefMetodePengolahanSeeder::class,
        ]);

        // ── Data dev/testing (jangan jalankan di production) ─────────────────
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                UserSeeder::class,
                DemoDataSeeder::class,
            ]);
        }
    }
}
