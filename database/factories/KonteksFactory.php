<?php

namespace Database\Factories;

use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MrKonteks>
 */
class KonteksFactory extends Factory
{
    protected $model = MrKonteks::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'layanan_id'      => Layanan::factory(),
            'nama_instansi'   => 'Dinas ' . fake()->word() . ' Kabupaten Test',
            'nama_upr'        => 'UPR SPBE ' . fake()->word(),
            'tahun_penilaian' => (int) date('Y'),
            'created_by'      => User::factory()->operator(),
        ];
    }

    /**
     * State: konteks yang dimiliki oleh user tertentu (layanan + created_by satu owner).
     */
    public function ownedBy(User $user, Layanan $layanan): static
    {
        return $this->state(fn () => [
            'layanan_id'  => $layanan->id,
            'created_by'  => $user->id,
            'nama_instansi' => $user->nama_dinas ?? 'Dinas Kominfo Test',
        ]);
    }
}
