<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GantiKataSandiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);
    }

    public function test_admin_bisa_mengganti_kata_sandi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kata-sandi.perbarui'), [
                'kata_sandi_lama' => 'rahasia123',
                'kata_sandi_baru' => 'sandibaru456',
                'kata_sandi_baru_confirmation' => 'sandibaru456',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('sandibaru456', $admin->fresh()->password));
    }

    public function test_kata_sandi_lama_yang_salah_ditolak(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kata-sandi.perbarui'), [
                'kata_sandi_lama' => 'salah',
                'kata_sandi_baru' => 'sandibaru456',
                'kata_sandi_baru_confirmation' => 'sandibaru456',
            ])
            ->assertSessionHasErrors('kata_sandi_lama');

        $this->assertTrue(Hash::check('rahasia123', $admin->fresh()->password));
    }

    public function test_konfirmasi_yang_tidak_cocok_ditolak(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kata-sandi.perbarui'), [
                'kata_sandi_lama' => 'rahasia123',
                'kata_sandi_baru' => 'sandibaru456',
                'kata_sandi_baru_confirmation' => 'bedasekali789',
            ])
            ->assertSessionHasErrors('kata_sandi_baru');
    }

    public function test_kata_sandi_baru_harus_minimal_delapan_karakter(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kata-sandi.perbarui'), [
                'kata_sandi_lama' => 'rahasia123',
                'kata_sandi_baru' => 'pendek',
                'kata_sandi_baru_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrors('kata_sandi_baru');
    }

    public function test_kata_sandi_baru_tidak_boleh_sama_dengan_yang_lama(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kata-sandi.perbarui'), [
                'kata_sandi_lama' => 'rahasia123',
                'kata_sandi_baru' => 'rahasia123',
                'kata_sandi_baru_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors('kata_sandi_baru');
    }

    public function test_tamu_tidak_bisa_mengganti_kata_sandi(): void
    {
        $this->put(route('admin.kata-sandi.perbarui'), [
            'kata_sandi_lama' => 'rahasia123',
            'kata_sandi_baru' => 'sandibaru456',
            'kata_sandi_baru_confirmation' => 'sandibaru456',
        ])->assertRedirect(route('admin.login'));
    }
}
