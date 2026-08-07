<?php

namespace Tests\Feature\Penduduk;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefAgama;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Database\Seeders\RefAgamaSeeder;
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
            ->assertSessionHasErrors(['nama']);

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

    // ------------------------------------------------------------------
    // Agama
    // ------------------------------------------------------------------

    public function test_agama_baru_tersimpan(): void
    {
        $agama = RefAgama::create(['nama' => 'Islam', 'is_template' => false]);

        $this->assertDatabaseHas('ref_agama', ['id' => $agama->id, 'nama' => 'Islam']);
    }

    public function test_agama_dipakai_terdeteksi(): void
    {
        $agama = RefAgama::create(['nama' => 'Kristen']);
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);

        Penduduk::create([
            'nama_lengkap' => 'Warga Uji', 'tanggal_lahir' => now()->subYears(30),
            'jenis_kelamin' => 'L', 'wilayah_id' => $wilayah->id, 'agama_id' => $agama->id,
            'jenis_masuk' => 'pendataan_awal', 'tanggal_masuk' => now(),
        ]);

        $this->assertTrue($agama->fresh()->sedangDipakai());
        $this->assertSame(1, $agama->fresh()->jumlahPemakai());
    }

    public function test_seeder_agama_membuat_tujuh_entri_bawaan(): void
    {
        (new RefAgamaSeeder)->run();

        $this->assertSame(7, RefAgama::template()->count());
        $this->assertDatabaseHas('ref_agama', ['nama' => 'Islam', 'is_template' => true]);
        $this->assertDatabaseHas('ref_agama', ['nama' => 'Tidak Ingin Menyebutkan', 'is_template' => true]);
    }

    public function test_agama_baru_tersimpan_lewat_halaman(): void
    {
        $this->sebagaiAdmin()->post(route('admin.agama.store'), ['nama' => 'Buddha'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ref_agama', ['nama' => 'Buddha']);
    }

    public function test_agama_template_tidak_bisa_dihapus(): void
    {
        $template = RefAgama::create(['nama' => 'Islam', 'is_template' => true]);

        $this->sebagaiAdmin()->delete(route('admin.agama.destroy', $template))
            ->assertSessionHasErrors(['nama']);

        $this->assertDatabaseHas('ref_agama', ['id' => $template->id]);
    }

    public function test_agama_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $agama = RefAgama::create(['nama' => 'Konghucu', 'is_template' => false]);
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);

        Penduduk::create([
            'nama_lengkap' => 'Warga Uji', 'tanggal_lahir' => now()->subYears(30),
            'jenis_kelamin' => 'L', 'wilayah_id' => $wilayah->id, 'agama_id' => $agama->id,
            'jenis_masuk' => 'pendataan_awal', 'tanggal_masuk' => now(),
        ]);

        $this->sebagaiAdmin()->delete(route('admin.agama.destroy', $agama))
            ->assertSessionHasErrors(['nama']);

        $this->assertDatabaseHas('ref_agama', ['id' => $agama->id]);
    }

    public function test_agama_custom_bisa_dihapus(): void
    {
        $agama = RefAgama::create(['nama' => 'Lainnya', 'is_template' => false]);

        $this->sebagaiAdmin()->delete(route('admin.agama.destroy', $agama))
            ->assertRedirectToRoute('admin.agama.index');

        $this->assertDatabaseMissing('ref_agama', ['id' => $agama->id]);
    }

    public function test_halaman_agama_menampilkan_daftar(): void
    {
        RefAgama::create(['nama' => 'Islam', 'is_template' => true]);

        $this->sebagaiAdmin()->get(route('admin.agama.index'))
            ->assertOk()
            ->assertSee('Islam');
    }

    public function test_tamu_ditolak_di_halaman_agama(): void
    {
        $this->get(route('admin.agama.index'))->assertRedirect(route('admin.login'));
    }

    public function test_nama_agama_mirip_tidak_langsung_ditolak(): void
    {
        RefAgama::create(['nama' => 'Islam']);

        $respons = $this->sebagaiAdmin()->post(route('admin.agama.store'), ['nama' => 'islam']);

        // Bukan galat validasi — sesi membawa data untuk dialog konfirmasi.
        $respons->assertSessionHasNoErrors();
        $respons->assertSessionHas('agama_duplikat');
        $this->assertSame(1, RefAgama::count());
    }

    public function test_nama_agama_mirip_tersimpan_bila_dipaksa(): void
    {
        RefAgama::create(['nama' => 'Islam']);

        $this->sebagaiAdmin()->post(route('admin.agama.store'), ['nama' => 'islam', 'paksa' => '1'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('agama_duplikat');

        $this->assertSame(2, RefAgama::count());
        $this->assertDatabaseHas('ref_agama', ['nama' => 'islam']);
    }

    public function test_nama_agama_beda_tersimpan_tanpa_konfirmasi(): void
    {
        RefAgama::create(['nama' => 'Islam']);

        $this->sebagaiAdmin()->post(route('admin.agama.store'), ['nama' => 'Kristen'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('agama_duplikat');

        $this->assertSame(2, RefAgama::count());
    }

    public function test_ubah_nama_agama_jadi_mirip_entri_lain_butuh_konfirmasi(): void
    {
        RefAgama::create(['nama' => 'Islam']);
        $kedua = RefAgama::create(['nama' => 'Kristen']);

        $respons = $this->sebagaiAdmin()->put(route('admin.agama.update', $kedua), ['nama' => 'ISLAM']);

        $respons->assertSessionHas('agama_duplikat');
        $this->assertSame('Kristen', $kedua->fresh()->nama);
    }
}
