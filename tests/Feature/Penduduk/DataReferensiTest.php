<?php

namespace Tests\Feature\Penduduk;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Wilayah dan pekerjaan sebagai data referensi.
 *
 * Tujuannya bukan sekadar kerapian: kalau "Krajan" dan "krajan" bisa masuk
 * sebagai dua baris, sebaran wilayah pada dashboard akan memecah satu dusun
 * menjadi dua, dan angkanya tidak bisa dipakai untuk laporan.
 */
class DataReferensiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    // ------------------------------------------------------------------
    // Wilayah
    // ------------------------------------------------------------------

    public function test_wilayah_baru_tersimpan_dengan_nomor_tiga_digit(): void
    {
        $this->sebagaiAdmin()->post(route('admin.wilayah.store'), [
            'dusun' => 'Dusun Krajan',
            'rw' => '1',
            'rt' => '2',
        ])->assertSessionHasNoErrors();

        // "1" dan "01" tidak boleh menjadi wilayah berbeda.
        $this->assertDatabaseHas('wilayah', ['dusun' => 'Dusun Krajan', 'rw' => '001', 'rt' => '002']);
    }

    public function test_kombinasi_wilayah_ganda_ditolak(): void
    {
        Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);

        $this->sebagaiAdmin()->post(route('admin.wilayah.store'), [
            'dusun' => 'Krajan',
            'rw' => '1',
            'rt' => '1',
        ])->assertSessionHasErrors('rt');

        $this->assertSame(1, Wilayah::count());
    }

    public function test_nomor_rt_tanpa_angka_ditolak(): void
    {
        $this->sebagaiAdmin()->post(route('admin.wilayah.store'), [
            'dusun' => 'Krajan',
            'rw' => 'satu',
            'rt' => 'dua',
        ])->assertSessionHasErrors('rt');
    }

    public function test_wilayah_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        Penduduk::create([
            'nama_lengkap' => 'Warga', 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id, 'jenis_masuk' => 'pendataan_awal', 'tanggal_masuk' => now(),
        ]);

        $this->sebagaiAdmin()
            ->delete(route('admin.wilayah.destroy', $wilayah))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('wilayah', ['id' => $wilayah->id]);
    }

    public function test_wilayah_yang_dipakai_warga_terarsip_juga_dilindungi(): void
    {
        // Warga terarsip tetap merujuk wilayahnya; menghapus wilayah akan
        // membuat data arsip tidak bisa dipulihkan dengan benar.
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        $warga = Penduduk::create([
            'nama_lengkap' => 'Warga', 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id, 'jenis_masuk' => 'pendataan_awal', 'tanggal_masuk' => now(),
        ]);
        $warga->delete();

        $this->sebagaiAdmin()
            ->delete(route('admin.wilayah.destroy', $wilayah))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('wilayah', ['id' => $wilayah->id]);
    }

    public function test_wilayah_tanpa_warga_bisa_dihapus(): void
    {
        $wilayah = Wilayah::create(['dusun' => 'Kosong', 'rw' => '009', 'rt' => '009']);

        $this->sebagaiAdmin()
            ->delete(route('admin.wilayah.destroy', $wilayah))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('wilayah', ['id' => $wilayah->id]);
    }

    // ------------------------------------------------------------------
    // Pekerjaan
    // ------------------------------------------------------------------

    public function test_pekerjaan_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()->post(route('admin.pekerjaan.store'), ['nama' => 'Petani'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ref_pekerjaan', ['nama' => 'Petani']);
    }

    public function test_pekerjaan_ganda_ditolak_tanpa_memandang_huruf_besar_kecil(): void
    {
        RefPekerjaan::create(['nama' => 'Petani']);

        // "petani" akan memecah grafik mata pencaharian jadi dua batang.
        $this->sebagaiAdmin()->post(route('admin.pekerjaan.store'), ['nama' => 'petani'])
            ->assertSessionHasErrors('nama');

        $this->sebagaiAdmin()->post(route('admin.pekerjaan.store'), ['nama' => '  PETANI  '])
            ->assertSessionHasErrors('nama');

        $this->assertSame(1, RefPekerjaan::count());
    }

    public function test_pekerjaan_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        $pekerjaan = RefPekerjaan::create(['nama' => 'Petani']);

        Penduduk::create([
            'nama_lengkap' => 'Warga', 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id, 'pekerjaan_id' => $pekerjaan->id,
            'jenis_masuk' => 'pendataan_awal', 'tanggal_masuk' => now(),
        ]);

        $this->sebagaiAdmin()
            ->delete(route('admin.pekerjaan.destroy', $pekerjaan))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('ref_pekerjaan', ['id' => $pekerjaan->id]);
    }

    public function test_halaman_referensi_dapat_dibuka(): void
    {
        $this->sebagaiAdmin()->get(route('admin.wilayah.index'))->assertOk();
        $this->sebagaiAdmin()->get(route('admin.pekerjaan.index'))->assertOk();
    }

    public function test_tamu_ditolak(): void
    {
        $this->get(route('admin.wilayah.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.pekerjaan.index'))->assertRedirect(route('admin.login'));
    }
}
