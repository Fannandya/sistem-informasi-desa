<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerintahAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_buat_admin_membuat_akun(): void
    {
        $this->artisan('admin:buat', [
            '--nama' => 'Sekretaris Desa',
            '--email' => 'sekdes@desa.test',
        ])
            ->expectsQuestion('Kata sandi (minimal 8 karakter)', 'rahasia123')
            ->assertSuccessful();

        $admin = Admin::where('email', 'sekdes@desa.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('rahasia123', $admin->password));
    }

    public function test_perintah_buat_admin_menolak_email_ganda(): void
    {
        Admin::create(['nama' => 'A', 'email' => 'admin@desa.test', 'password' => 'rahasia123']);

        $this->artisan('admin:buat', [
            '--nama' => 'B',
            '--email' => 'admin@desa.test',
        ])
            ->expectsQuestion('Kata sandi (minimal 8 karakter)', 'rahasia123')
            ->assertFailed();

        $this->assertSame(1, Admin::count());
    }

    public function test_perintah_reset_sandi_mengubah_kata_sandi(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'admin@desa.test', 'password' => 'rahasia123']);

        $this->artisan('admin:reset-sandi', ['email' => 'admin@desa.test'])
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'sandibaru456')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('sandibaru456', $admin->fresh()->password));
    }

    public function test_perintah_reset_sandi_gagal_bila_email_tidak_ada(): void
    {
        $this->artisan('admin:reset-sandi', ['email' => 'bukan@siapa.test'])
            ->assertFailed();
    }

    public function test_perintah_daftar_admin_menampilkan_akun(): void
    {
        Admin::create(['nama' => 'Admin Desa', 'email' => 'admin@desa.test', 'password' => 'rahasia123']);

        $this->artisan('admin:daftar')
            ->expectsOutputToContain('admin@desa.test')
            ->assertSuccessful();
    }

    public function test_tidak_ada_rute_reset_kata_sandi_publik(): void
    {
        // Fitur 01: pemulihan akun hanya lewat artisan, bukan halaman web.
        $this->get('/admin/lupa-kata-sandi')->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/register')->assertNotFound();
    }
}
