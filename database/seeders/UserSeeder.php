<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed 1 admin + contoh operator dinas.
     * Jangan jalankan di production.
     */
    public function run(): void
    {
        $adminRole    = Role::where('name', 'admin')->firstOrFail();
        $operatorRole = Role::where('name', 'operator')->firstOrFail();

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@diskominfo.test'],
            [
                'name'       => 'Administrator',
                'password'   => Hash::make('password'),
                'role_id'    => $adminRole->id,
                'nama_dinas' => 'Dinas Komunikasi dan Informatika',
                'alias'      => 'Diskominfo',
            ]
        );

        // Operator dinas contoh
        $dinasList = [
            [
                'email'      => 'operator.diskominfo@diskominfo.test',
                'name'       => 'Operator Diskominfo',
                'nama_dinas' => 'Dinas Komunikasi dan Informatika',
                'alias'      => 'Diskominfo',
            ],
            [
                'email'      => 'operator.bappeda@diskominfo.test',
                'name'       => 'Operator Bappeda',
                'nama_dinas' => 'Badan Perencanaan Pembangunan Daerah',
                'alias'      => 'Bappeda',
            ],
            [
                'email'      => 'operator.dinkes@diskominfo.test',
                'name'       => 'Operator Dinkes',
                'nama_dinas' => 'Dinas Kesehatan',
                'alias'      => 'Dinkes',
            ],
        ];

        foreach ($dinasList as $dinas) {
            User::firstOrCreate(
                ['email' => $dinas['email']],
                [
                    'name'       => $dinas['name'],
                    'password'   => Hash::make('password'),
                    'role_id'    => $operatorRole->id,
                    'nama_dinas' => $dinas['nama_dinas'],
                    'alias'      => $dinas['alias'],
                ]
            );
        }
    }
}
