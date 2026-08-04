<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\PotensiDesa;
use App\Models\RefKategoriPotensi;
use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PotensiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        RefKategoriPotensi::create(['nama' => 'UMKM', 'urutan' => 1]);
        RefKategoriPotensi::create(['nama' => 'Wisata', 'urutan' => 2]);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function potensi(array $atribut = []): PotensiDesa
    {
        return PotensiDesa::create(array_merge([
            'nama' => 'Keripik Singkong Bu Sari',
            'kategori_potensi_id' => 1,
            'tampil' => true,
        ], $atribut));
    }

    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'nama' => 'Keripik Singkong Bu Sari',
            'kategori_potensi_id' => '1',
            'tampil' => '1',
        ], $ganti);
    }

    // ------------------------------------------------------------------
    // Slug — PRD 6.17
    // ------------------------------------------------------------------

    public function test_slug_dibuat_otomatis_dari_nama(): void
    {
        $this->sebagaiAdmin()->post(route('admin.potensi.store'), $this->dataValid());

        $this->assertSame('keripik-singkong-bu-sari', PotensiDesa::first()->slug);
    }

    public function test_slug_tidak_berubah_saat_nama_diubah(): void
    {
        // Tautan yang sudah tersebar di grup WhatsApp warga tidak boleh mati
        // hanya karena admin memperbaiki satu huruf pada nama.
        $potensi = $this->potensi();
        $slugAsli = $potensi->slug;

        $this->sebagaiAdmin()->put(
            route('admin.potensi.update', $potensi),
            $this->dataValid(['nama' => 'Keripik Singkong Bu Sari Rasa Balado']),
        );

        $potensi = $potensi->fresh();
        $this->assertSame($slugAsli, $potensi->slug);
        $this->assertSame('Keripik Singkong Bu Sari Rasa Balado', $potensi->nama);
    }

    public function test_slug_bisa_disunting_manual(): void
    {
        $potensi = $this->potensi();

        $this->sebagaiAdmin()->put(
            route('admin.potensi.update', $potensi),
            $this->dataValid(['slug' => 'keripik-bu-sari']),
        );

        $this->assertSame('keripik-bu-sari', $potensi->fresh()->slug);
    }

    public function test_slug_ganda_diberi_akhiran_angka(): void
    {
        $this->potensi(['nama' => 'Curug Sukamaju']);
        $this->potensi(['nama' => 'Curug Sukamaju']);

        $this->assertSame(
            ['curug-sukamaju', 'curug-sukamaju-2'],
            PotensiDesa::orderBy('id')->pluck('slug')->all(),
        );
    }

    public function test_slug_yang_bentrok_ditolak_saat_disunting_manual(): void
    {
        $this->potensi(['nama' => 'Curug Sukamaju']);
        $lain = $this->potensi(['nama' => 'Bukit Panorama']);

        $this->sebagaiAdmin()
            ->put(route('admin.potensi.update', $lain), $this->dataValid(['slug' => 'curug-sukamaju']))
            ->assertSessionHasErrors('slug');
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public function test_potensi_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.potensi.store'), $this->dataValid(['deskripsi_singkat' => 'Renyah dan gurih.']))
            ->assertRedirect(route('admin.potensi.index'));

        $this->assertDatabaseHas('potensi_desa', ['nama' => 'Keripik Singkong Bu Sari', 'tampil' => true]);
    }

    public function test_kolom_wajib_divalidasi(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.potensi.store'), [])
            ->assertSessionHasErrors(['nama', 'kategori_potensi_id']);
    }

    public function test_kategori_di_luar_daftar_ditolak(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.potensi.store'), $this->dataValid(['kategori_potensi_id' => 999]))
            ->assertSessionHasErrors('kategori_potensi_id');
    }

    public function test_menghapus_potensi_ikut_menghapus_fotonya(): void
    {
        $layanan = app(LayananUnggahGambar::class);
        $foto = $layanan->simpan(UploadedFile::fake()->image('f.jpg', 800, 600), 'potensi');
        $potensi = $this->potensi(['foto' => $foto]);

        $this->sebagaiAdmin()->delete(route('admin.potensi.destroy', $potensi));

        $this->assertDatabaseCount('potensi_desa', 0);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_status_tampil_bisa_diubah_dari_daftar(): void
    {
        $potensi = $this->potensi(['tampil' => true]);

        $this->sebagaiAdmin()->post(route('admin.potensi.tampil', $potensi));

        $this->assertFalse($potensi->fresh()->tampil);
    }

    // ------------------------------------------------------------------
    // Halaman publik
    // ------------------------------------------------------------------

    public function test_katalog_publik_menampilkan_potensi_yang_tampil(): void
    {
        $this->potensi(['nama' => 'Tampil Ini', 'tampil' => true]);
        $this->potensi(['nama' => 'Disembunyikan', 'tampil' => false]);

        $this->get(route('publik.potensi.index'))
            ->assertOk()
            ->assertSee('Tampil Ini')
            ->assertDontSee('Disembunyikan');
    }

    public function test_halaman_detail_dapat_dibuka(): void
    {
        $potensi = $this->potensi(['deskripsi' => 'Diproduksi rumahan sejak 2015.']);

        $this->get(route('publik.potensi.show', $potensi))
            ->assertOk()
            ->assertSee('Keripik Singkong Bu Sari')
            ->assertSee('Diproduksi rumahan sejak 2015.');
    }

    public function test_potensi_tersembunyi_tidak_bisa_dibuka_meski_alamatnya_ditebak(): void
    {
        $potensi = $this->potensi(['tampil' => false]);

        $this->get(route('publik.potensi.show', $potensi))->assertNotFound();
    }

    public function test_katalog_bisa_disaring_per_kategori(): void
    {
        $this->potensi(['nama' => 'Keripik UMKM', 'kategori_potensi_id' => 1]);
        $this->potensi(['nama' => 'Curug Wisata', 'kategori_potensi_id' => 2]);

        $this->get(route('publik.potensi.index', ['kategori' => 2]))
            ->assertOk()
            ->assertSee('Curug Wisata')
            ->assertDontSee('Keripik UMKM');
    }

    public function test_katalog_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.potensi.index'))
            ->assertOk()
            ->assertSee('Data potensi belum tersedia');
    }

    public function test_halaman_detail_punya_meta_description(): void
    {
        $potensi = $this->potensi(['deskripsi_singkat' => 'Keripik renyah dari singkong pilihan.']);

        $this->get(route('publik.potensi.show', $potensi))
            ->assertOk()
            ->assertSee('Keripik renyah dari singkong pilihan.', false);
    }

    public function test_katalog_publik_memakai_pagination_dua_belas(): void
    {
        foreach (range(1, 15) as $i) {
            $this->potensi(['nama' => "Potensi {$i}"]);
        }

        $response = $this->get(route('publik.potensi.index'))->assertOk();

        $this->assertCount(12, $response->viewData('daftar')->items());
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_ditolak_di_halaman_admin(): void
    {
        $potensi = $this->potensi();

        $this->get(route('admin.potensi.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.potensi.store'), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.potensi.destroy', $potensi))->assertRedirect(route('admin.login'));
    }
}
