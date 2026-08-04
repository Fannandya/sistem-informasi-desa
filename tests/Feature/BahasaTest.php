<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Berkas bahasa Indonesia.
 *
 * Laravel 12 tidak menyertakan berkas bahasa apa pun. Karena APP_LOCALE dan
 * APP_FALLBACK_LOCALE keduanya "id", kunci yang tidak ditemukan tampil apa
 * adanya — aparat desa sempat melihat "validation.required" dan
 * "pagination.previous" alih-alih kalimat yang bisa dipahami.
 */
class BahasaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesan_validasi_bawaan_diterjemahkan(): void
    {
        foreach ([
            'validation.required',
            'validation.email',
            'validation.integer',
            'validation.date',
            'validation.exists',
            'validation.unique',
            'validation.image',
            'validation.mimes',
            'validation.max.string',
            'validation.max.file',
            'validation.min.numeric',
            'validation.confirmed',
            'validation.before_or_equal',
            'validation.after_or_equal',
            'validation.regex',
            'validation.current_password',
        ] as $kunci) {
            $this->assertNotSame(
                $kunci,
                __($kunci),
                "Kunci bahasa \"{$kunci}\" belum diterjemahkan dan akan tampil mentah ke pengguna.",
            );
        }
    }

    public function test_label_paginasi_diterjemahkan(): void
    {
        $this->assertNotSame('pagination.previous', __('pagination.previous'));
        $this->assertNotSame('pagination.next', __('pagination.next'));
    }

    public function test_pesan_validasi_tampil_terbaca_di_formulir(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.penduduk.create'))
            ->post(route('admin.penduduk.store'), [])
            ->assertSessionHasErrors('nama_lengkap');

        $pesan = session('errors')->first('nama_lengkap');

        $this->assertStringNotContainsString('validation.', $pesan);
        $this->assertStringContainsString('wajib diisi', $pesan);
    }

    public function test_halaman_dengan_paginasi_tidak_membocorkan_kunci_bahasa(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);

        Penduduk::insert(collect(range(1, 60))->map(fn ($i) => [
            'nama_lengkap' => "Warga {$i}",
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $html = $this->actingAs($admin, 'admin')
            ->get(route('admin.penduduk.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('pagination.previous', $html);
        $this->assertStringNotContainsString('pagination.next', $html);
        $this->assertStringContainsString('Berikutnya', $html);
    }
}
