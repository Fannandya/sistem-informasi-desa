<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'dusun' => "Dusun {$counter}",
            'rw' => fake()->numberBetween(1, 3),
            'rt' => fake()->numberBetween(1, 5),
            'urutan' => $counter,
        ];
    }
}
