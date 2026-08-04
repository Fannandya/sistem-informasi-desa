<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\Pengurus;
use App\Models\RefOrganisasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman Kelola Organisasi.
 *
 * Organisasi adalah data referensi seperti Wilayah dan Pekerjaan: isinya
 * menentukan pilihan pada formulir pengurus, dan urutannya menentukan susunan
 * kelompok pada halaman Struktur Organisasi publik.
 */
class KelolaOrganisasiTest extends TestCase
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

    private function organisasi(string $nama, int $urutan = 0): RefOrganisasi
    {
        return RefOrganisasi::create(['nama' => $nama, 'urutan' => $urutan]);
    }

    private function pengurus(RefOrganisasi $organisasi): Pengurus
    {
        return Pengurus::create([
            'nama' => 'Sutrisno Hadi',
            'jabatan' => 'Kepala Desa',
            'organisasi_id' => $organisasi->id,
            'tampil' => true,
        ]);
    }

    // ------------------------------------------------------------------
    // Halaman
    // ------------------------------------------------------------------

    public function test_halaman_dapat_dibuka_dan_menampilkan_daftar(): void
    {
        $this->organisasi('Pemerintah Desa', 1);
        $this->organisasi('Karang Taruna', 2);

        $this->sebagaiAdmin()
            ->get(route('admin.organisasi.index'))
            ->assertOk()
            ->assertSee('Kelola Organisasi')
            ->assertSee('Pemerintah Desa')
            ->assertSee('Karang Taruna');
    }

    public function test_halaman_kosong_menampilkan_pesan_ramah(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.organisasi.index'))
            ->assertOk()
            ->assertSee('Belum ada organisasi');
    }

    public function test_jumlah_pengurus_ditampilkan_per_organisasi(): void
    {
        $pemdes = $this->organisasi('Pemerintah Desa');
        $this->pengurus($pemdes);
        $this->organisasi('PKK');

        $this->sebagaiAdmin()
            ->get(route('admin.organisasi.index'))
            ->assertOk()
            ->assertSee('1 pengurus')
            ->assertSee('belum ada');
    }

    public function test_menu_sidebar_menautkan_ke_halaman_ini(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.organisasi.index'), false);
    }

    // ------------------------------------------------------------------
    // Tambah
    // ------------------------------------------------------------------

    public function test_organisasi_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.store'), ['nama' => 'Karang Taruna', 'urutan' => 3])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ref_organisasi', ['nama' => 'Karang Taruna', 'urutan' => 3]);
    }

    public function test_nama_wajib_diisi(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.store'), ['nama' => ''])
            ->assertSessionHasErrors('nama');
    }

    public function test_organisasi_baru_diletakkan_di_urutan_terakhir(): void
    {
        // Kelompok baru tidak boleh diam-diam menyalip susunan yang sudah
        // ditata admin.
        $this->organisasi('Pemerintah Desa', 1);
        $this->organisasi('RT', 5);

        $this->sebagaiAdmin()->post(route('admin.organisasi.store'), ['nama' => 'PKK']);

        $this->assertSame(6, RefOrganisasi::where('nama', 'PKK')->value('urutan'));
    }

    public function test_nama_ganda_ditolak_tanpa_memandang_huruf(): void
    {
        $this->organisasi('Karang Taruna');

        // "karang taruna" akan menjadi kelompok terpisah di halaman publik
        // untuk organisasi yang sama.
        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.store'), ['nama' => 'karang taruna'])
            ->assertSessionHasErrors('nama');

        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.store'), ['nama' => '  KARANG TARUNA  '])
            ->assertSessionHasErrors('nama');

        $this->assertSame(1, RefOrganisasi::count());
    }

    // ------------------------------------------------------------------
    // Ubah
    // ------------------------------------------------------------------

    public function test_organisasi_bisa_diubah(): void
    {
        $organisasi = $this->organisasi('Karang Taruna', 4);

        $this->sebagaiAdmin()
            ->put(route('admin.organisasi.update', $organisasi), ['nama' => 'Karang Taruna Desa', 'urutan' => 2])
            ->assertSessionHas('status');

        $organisasi = $organisasi->fresh();
        $this->assertSame('Karang Taruna Desa', $organisasi->nama);
        $this->assertSame(2, $organisasi->urutan);
    }

    public function test_mengubah_nama_menjadi_milik_organisasi_lain_ditolak(): void
    {
        $this->organisasi('Pemerintah Desa');
        $lain = $this->organisasi('PKK');

        $this->sebagaiAdmin()
            ->put(route('admin.organisasi.update', $lain), ['nama' => 'Pemerintah Desa'])
            ->assertSessionHasErrors('nama');
    }

    public function test_menyimpan_nama_yang_sama_pada_dirinya_sendiri_diterima(): void
    {
        $organisasi = $this->organisasi('PKK', 3);

        $this->sebagaiAdmin()
            ->put(route('admin.organisasi.update', $organisasi), ['nama' => 'PKK', 'urutan' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $organisasi->fresh()->urutan);
    }

    public function test_mengubah_nama_ikut_terlihat_di_halaman_publik(): void
    {
        $organisasi = $this->organisasi('Karang Taruna');
        $this->pengurus($organisasi);

        $this->sebagaiAdmin()->put(route('admin.organisasi.update', $organisasi), ['nama' => 'Karang Taruna Bhakti']);

        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('Karang Taruna Bhakti')
            ->assertDontSee('>Karang Taruna<', false);
    }

    // ------------------------------------------------------------------
    // Hapus
    // ------------------------------------------------------------------

    public function test_organisasi_tanpa_pengurus_bisa_dihapus(): void
    {
        $organisasi = $this->organisasi('PKK');

        $this->sebagaiAdmin()
            ->delete(route('admin.organisasi.destroy', $organisasi))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('ref_organisasi', ['id' => $organisasi->id]);
    }

    public function test_organisasi_yang_dipakai_tidak_bisa_dihapus(): void
    {
        // Menghapusnya akan membuat pengurus kehilangan kelompok dan hilang
        // dari halaman publik tanpa jejak.
        $organisasi = $this->organisasi('Pemerintah Desa');
        $this->pengurus($organisasi);

        $this->sebagaiAdmin()
            ->delete(route('admin.organisasi.destroy', $organisasi))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('ref_organisasi', ['id' => $organisasi->id]);
        $this->assertSame(1, Pengurus::count());
    }

    public function test_pesan_penolakan_menyebut_jumlah_pengurusnya(): void
    {
        $organisasi = $this->organisasi('Pemerintah Desa');
        $this->pengurus($organisasi);
        $this->pengurus($organisasi);

        $this->sebagaiAdmin()->delete(route('admin.organisasi.destroy', $organisasi));

        $this->assertStringContainsString('2 pengurus', session('galat'));
    }

    public function test_organisasi_bisa_dihapus_setelah_pengurusnya_dipindah(): void
    {
        $lama = $this->organisasi('Karang Taruna');
        $baru = $this->organisasi('PKK');
        $pengurus = $this->pengurus($lama);

        $pengurus->update(['organisasi_id' => $baru->id]);

        $this->sebagaiAdmin()
            ->delete(route('admin.organisasi.destroy', $lama))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('ref_organisasi', ['id' => $lama->id]);
        $this->assertSame($baru->id, $pengurus->fresh()->organisasi_id);
    }

    // ------------------------------------------------------------------
    // Urutan
    // ------------------------------------------------------------------

    public function test_urutan_bisa_digeser_naik_turun(): void
    {
        $a = $this->organisasi('Pemerintah Desa', 1);
        $b = $this->organisasi('RT', 2);

        $this->sebagaiAdmin()->post(route('admin.organisasi.geser', $b), ['arah' => 'naik']);

        $this->assertSame(1, $b->fresh()->urutan);
        $this->assertSame(2, $a->fresh()->urutan);
    }

    public function test_geser_di_ujung_tidak_error(): void
    {
        $a = $this->organisasi('Pemerintah Desa', 1);

        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.geser', $a), ['arah' => 'naik'])
            ->assertRedirect();

        $this->assertSame(1, $a->fresh()->urutan);
    }

    public function test_urutan_menentukan_susunan_halaman_publik(): void
    {
        $pemdes = $this->organisasi('Pemerintah Desa', 1);
        $rt = $this->organisasi('RT', 2);
        $this->pengurus($pemdes);
        $this->pengurus($rt);

        // Balik urutannya lewat halaman kelola organisasi.
        $this->sebagaiAdmin()->post(route('admin.organisasi.geser', $rt), ['arah' => 'naik']);

        $html = $this->get(route('publik.struktur'))->assertOk()->getContent();

        $this->assertLessThan(
            mb_strpos($html, 'Pemerintah Desa'),
            mb_strpos($html, '>RT<'),
        );
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_ditolak(): void
    {
        $organisasi = $this->organisasi('PKK');

        $this->get(route('admin.organisasi.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.organisasi.store'), [])->assertRedirect(route('admin.login'));
        $this->put(route('admin.organisasi.update', $organisasi), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.organisasi.destroy', $organisasi))->assertRedirect(route('admin.login'));
        $this->post(route('admin.organisasi.geser', $organisasi))->assertRedirect(route('admin.login'));
    }
}
