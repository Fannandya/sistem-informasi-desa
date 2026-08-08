<?php

namespace Database\Factories;

use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

class PendudukFactory extends Factory
{
    protected $model = Penduduk::class;

    public function definition(): array
    {
        $wilayah = Wilayah::first() ?? Wilayah::factory()->create();

        return [
            'nama_lengkap' => fake()->name(),
            'tanggal_lahir' => fake()->dateTimeBetween('-60 years', '-5 years'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'wilayah_id' => $wilayah->id,
            'pekerjaan_id' => null,
            'agama_id' => null,
            'pendidikan' => fake()->randomElement(['sd', 'smp', 'sma', 's1']),
            'status_perkawinan' => null,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => fake()->dateTimeBetween('-10 years', 'now'),
        ];
    }
}
