<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PengaturanDesaSeeder::class,
            WilayahSeeder::class,
            RefPekerjaanSeeder::class,
            PendudukSeeder::class,
        ]);
    }
}
