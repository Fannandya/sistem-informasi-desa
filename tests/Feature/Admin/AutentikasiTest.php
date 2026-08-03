<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $sandi = 'rahasia123'): Admin
    {
        return Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => $sandi,
        ]);
    }

    public function test_halaman_admin_menolak_tamu(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/kata-sandi')->assertRedirect(route('admin.login'));
    }

    public function test_admin_bisa_masuk_dengan_kredensial_benar(): void
    {
        $this->admin();

        $this->post(route('admin.login.proses'), [
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(Admin::first(), 'admin');
    }

    public function test_waktu_login_terakhir_tercatat(): void
    {
        $admin = $this->admin();
        $this->assertNull($admin->last_login_at);

        $this->post(route('admin.login.proses'), [
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);

        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_kredensial_salah_ditolak_tanpa_membocorkan_email(): void
    {
        $this->admin();

        $this->from(route('admin.login'))
            ->post(route('admin.login.proses'), [
                'email' => 'admin@desa.test',
                'password' => 'salah',
            ])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        // Email yang tidak terdaftar harus menghasilkan pesan yang sama persis.
        $this->from(route('admin.login'))
            ->post(route('admin.login.proses'), [
                'email' => 'bukan@siapa.test',
                'password' => 'salah',
            ])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('admin');
    }

    public function test_login_diblokir_setelah_lima_percobaan_gagal(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.proses'), [
                'email' => 'admin@desa.test',
                'password' => 'salah',
            ]);
        }

        // Percobaan ke-6 diblokir, bahkan dengan kata sandi yang benar.
        $response = $this->post(route('admin.login.proses'), [
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk',
            session('errors')->first('email'),
        );
        $this->assertGuest('admin');
    }

    public function test_login_berhasil_mengatur_ulang_hitungan_kegagalan(): void
    {
        $this->admin();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('admin.login.proses'), [
                'email' => 'admin@desa.test',
                'password' => 'salah',
            ]);
        }

        $this->post(route('admin.login.proses'), [
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);

        $this->assertSame(0, RateLimiter::attempts('login:admin@desa.test|127.0.0.1'));
    }

    public function test_admin_bisa_keluar(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_halaman_admin_tidak_boleh_di_cache_peramban(): void
    {
        $admin = $this->admin();

        // Symfony menormalkan dan mengurutkan ulang direktif Cache-Control,
        // jadi yang diperiksa adalah kehadiran tiap direktif, bukan urutannya.
        $header = $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->headers->get('Cache-Control');

        foreach (['no-cache', 'no-store', 'must-revalidate', 'max-age=0'] as $direktif) {
            $this->assertStringContainsString($direktif, $header);
        }
    }

    public function test_admin_yang_sudah_masuk_dialihkan_dari_halaman_login(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_kata_sandi_disimpan_sebagai_hash(): void
    {
        $admin = $this->admin();

        $this->assertNotSame('rahasia123', $admin->password);
        $this->assertTrue(Hash::check('rahasia123', $admin->password));
    }
}
