<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pemeriksaan paling dasar: aplikasi berhasil dinyalakan dan halaman muka
 * tampil meski database benar-benar kosong.
 *
 * Menggantikan tes bawaan Laravel, yang membuka "/" tanpa menyiapkan database
 * sehingga selalu gagal begitu halaman muka berisi data desa.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_muka_tampil_pada_pemasangan_baru(): void
    {
        // Belum ada seeder, belum ada konten apa pun — halaman muka tetap
        // harus tampil, bukan menampilkan error.
        $this->get('/')->assertOk();
    }

    public function test_halaman_kesehatan_aplikasi_tersedia(): void
    {
        $this->get('/up')->assertOk();
    }
}
