<?php

namespace Database\Factories;

use App\Models\Layanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Layanan>
 */
class LayananFactory extends Factory
{
    protected $model = Layanan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_layanan'   => fake()->sentence(3),
            'status_layanan' => fake()->randomElement(['berjalan', 'direncanakan', 'dihentikan']),
            'created_by'     => User::factory()->operator(),
        ];
    }

    /**
     * State: layanan dengan created_by user tertentu.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['created_by' => $user->id]);
    }
}
