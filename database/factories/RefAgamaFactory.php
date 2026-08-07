<?php

namespace Database\Factories;

use App\Models\RefAgama;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefAgamaFactory extends Factory
{
    protected $model = RefAgama::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->word(),
            'is_template' => false,
        ];
    }

    public function template(): self
    {
        return $this->state(['is_template' => true]);
    }

    public function custom(): self
    {
        return $this->state(['is_template' => false]);
    }
}
