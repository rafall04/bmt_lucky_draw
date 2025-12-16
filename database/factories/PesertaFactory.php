<?php

namespace Database\Factories;

use App\Models\Peserta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Peserta>
 */
class PesertaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_rekening' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'cabang' => fake()->city(),
            'status_menang' => false,
            'hadiah_didapat' => null,
            'waktu_menang' => null,
        ];
    }

    /**
     * Indicate that the peserta is a winner.
     */
    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_menang' => true,
            'waktu_menang' => now(),
            'hadiah_didapat' => fake()->randomElement(['Hadiah 1', 'Hadiah 2', 'Hadiah 3']),
        ]);
    }
}

