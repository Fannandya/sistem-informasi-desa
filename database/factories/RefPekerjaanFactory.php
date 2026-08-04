<?php

namespace Database\Factories;

use App\Models\RefPekerjaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefPekerjaanFactory extends Factory
{
    protected $model = RefPekerjaan::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->jobTitle(),
            'is_template' => fake()->boolean(20),
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
