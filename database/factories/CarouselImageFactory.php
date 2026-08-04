<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\CarouselImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarouselImageFactory extends Factory
{
    protected $model = CarouselImage::class;

    public function definition(): array
    {
        return [
            'path' => 'carousel/test-' . fake()->uuid() . '.jpg',
            'judul' => fake()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'urutan' => fake()->numberBetween(0, 10),
            'tampil' => fake()->boolean(80),
            'admin_id' => Admin::factory(),
        ];
    }
}
