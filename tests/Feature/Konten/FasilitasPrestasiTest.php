<?php

namespace Tests\Feature\Konten;

use App\Enums\TingkatPrestasi;
use App\Models\Admin;
use App\Models\FasilitasUmum;
use App\Models\PrestasiDesa;
use App\Models\RefKategoriFasilitas;
use App\Models\Wilayah;
use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FasilitasPrestasiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        RefKategoriFasilitas::create(['nama' => 'Pendidikan', 'urutan' => 1]);
        RefKategoriFasilitas::create(['nama' => 'Kesehatan', 'urutan' => 2]);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function fasilitas(array $atribut = []): FasilitasUmum
    {
        return FasilitasUmum::create(array_merge([
            'nama' => 'SDN Sukamaju 01',
            'kategori_fasilitas_id' => 1,
            'tampil' => true,
        ], $atribut));
    }

    private function prestasi(array $atribut = []): PrestasiDesa
    {
        return PrestasiDesa::create(array_merge([
            'judul' => 'Juara 1 Lomba Desa Bersih',
            'tahun' => now()->year,
            'tingkat' => TingkatPrestasi::Kabupaten,
            'tampil' => true,
        ], $atribut));
    }

    // ==================================================================
    // Fasilitas
    // ==================================================================

    public function test_fasilitas_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.fasilitas.store'), [
                'nama' => 'Puskesmas Pembantu',
                'kategori_fasilitas_id' => 2,
                'tampil' => '1',
            ])
            ->assertRedirect(route('admin.fasilitas.index'));

        $this->assertDatabaseHas('fasilitas_umum', ['nama' => 'Puskesmas Pembantu']);
    }

    public function test_kategori_fasilitas_di_luar_daftar_ditolak(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.fasilitas.store'), ['nama' => 'X', 'kategori_fasilitas_id' => 999])
            ->assertSessionHasErrors('kategori_fasilitas_id');
    }

    public function test_menghapus_fasilitas_ikut_menghapus_fotonya(): void
    {
        $layanan = app(LayananUnggahGambar::class);
        $foto = $layanan->simpan(UploadedFile::fake()->image('f.jpg', 800, 600), 'fasilitas');
        $fasilitas = $this->fasilitas(['foto' => $foto]);

        $this->sebagaiAdmin()->delete(route('admin.fasilitas.destroy', $fasilitas));

        $this->assertDatabaseCount('fasilitas_umum', 0);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_halaman_publik_fasilitas_dikelompokkan_per_kategori(): void
    {
        $this->fasilitas(['nama' => 'SDN Satu', 'kategori_fasilitas_id' => 1]);
        RefKategoriFasilitas::create(['nama' => 'Ibadah', 'urutan' => 3]);
        $this->fasilitas(['nama' => 'Masjid Al-Ikhlas', 'kategori_fasilitas_id' => 3]);

        $this->get(route('publik.fasilitas'))
            ->assertOk()
            ->assertSee('Pendidikan')
            ->assertSee('SDN Satu')
            ->assertSee('Masjid Al-Ikhlas');
    }

    public function test_kategori_tanpa_isi_tidak_muncul_sebagai_judul_kosong(): void
    {
        $this->fasilitas(['kategori_fasilitas_id' => 1]);

        $this->get(route('publik.fasilitas'))
            ->assertOk()
            ->assertDontSee('Ibadah');
    }

    public function test_fasilitas_tersembunyi_tidak_muncul_di_publik(): void
    {
        $this->fasilitas(['nama' => 'Terlihat', 'tampil' => true]);
        $this->fasilitas(['nama' => 'Tersembunyi', 'tampil' => false]);

        $this->get(route('publik.fasilitas'))
            ->assertOk()
            ->assertSee('Terlihat')
            ->assertDontSee('Tersembunyi');
    }

    public function test_lokasi_fasilitas_menggabungkan_alamat_dan_wilayah(): void
    {
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '002']);
        $fasilitas = $this->fasilitas(['alamat' => 'Jalan Raya No. 5', 'wilayah_id' => $wilayah->id]);

        $this->assertSame('Jalan Raya No. 5 · RT 002/RW 001', $fasilitas->lokasiTerbaca());
        $this->assertNull($this->fasilitas(['nama' => 'Tanpa lokasi'])->lokasiTerbaca());
    }

    public function test_fasilitas_publik_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.fasilitas'))
            ->assertOk()
            ->assertSee('Data fasilitas belum tersedia');
    }

    // ==================================================================
    // Prestasi
    // ==================================================================

    public function test_prestasi_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.prestasi.store'), [
                'judul' => 'Desa Sadar Hukum',
                'tahun' => now()->year,
                'tingkat' => 'provinsi',
                'tampil' => '1',
            ])
            ->assertRedirect(route('admin.prestasi.index'));

        $this->assertDatabaseHas('prestasi_desa', ['judul' => 'Desa Sadar Hukum']);
    }

    public function test_tahun_prestasi_di_masa_depan_ditolak(): void
    {
        // Prestasi yang belum diraih tidak masuk akal untuk dicatat.
        $this->sebagaiAdmin()
            ->post(route('admin.prestasi.store'), [
                'judul' => 'Juara Masa Depan',
                'tahun' => now()->year + 1,
            ])
            ->assertSessionHasErrors('tahun');
    }

    public function test_prestasi_diurutkan_dari_tahun_terbaru(): void
    {
        $this->prestasi(['judul' => 'Lama', 'tahun' => now()->year - 5]);
        $this->prestasi(['judul' => 'Baru', 'tahun' => now()->year]);

        $html = $this->get(route('publik.prestasi'))->assertOk()->getContent();

        $this->assertLessThan(mb_strpos($html, 'Lama'), mb_strpos($html, 'Baru'));
    }

    public function test_prestasi_bisa_disaring_per_tingkat(): void
    {
        $this->prestasi(['judul' => 'Tingkat Nasional Ini', 'tingkat' => TingkatPrestasi::Nasional]);
        $this->prestasi(['judul' => 'Tingkat Desa Ini', 'tingkat' => TingkatPrestasi::Desa]);

        $this->get(route('publik.prestasi', ['tingkat' => 'nasional']))
            ->assertOk()
            ->assertSee('Tingkat Nasional Ini')
            ->assertDontSee('Tingkat Desa Ini');
    }

    public function test_prestasi_tersembunyi_tidak_muncul_di_publik(): void
    {
        $this->prestasi(['judul' => 'Terlihat', 'tampil' => true]);
        $this->prestasi(['judul' => 'Tersembunyi', 'tampil' => false]);

        $this->get(route('publik.prestasi'))
            ->assertOk()
            ->assertSee('Terlihat')
            ->assertDontSee('Tersembunyi');
    }

    public function test_prestasi_tanpa_tingkat_tetap_tampil(): void
    {
        $this->prestasi(['judul' => 'Tanpa Tingkat', 'tingkat' => null]);

        $this->get(route('publik.prestasi'))
            ->assertOk()
            ->assertSee('Tanpa Tingkat');
    }

    public function test_prestasi_publik_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.prestasi'))
            ->assertOk()
            ->assertSee('Data prestasi belum tersedia');
    }

    public function test_status_tampil_bisa_diubah_dari_daftar(): void
    {
        $fasilitas = $this->fasilitas(['tampil' => true]);
        $prestasi = $this->prestasi(['tampil' => true]);

        $this->sebagaiAdmin()->post(route('admin.fasilitas.tampil', $fasilitas));
        $this->sebagaiAdmin()->post(route('admin.prestasi.tampil', $prestasi));

        $this->assertFalse($fasilitas->fresh()->tampil);
        $this->assertFalse($prestasi->fresh()->tampil);
    }

    public function test_tamu_ditolak_di_halaman_admin(): void
    {
        $this->get(route('admin.fasilitas.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.prestasi.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.fasilitas.store'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.prestasi.store'), [])->assertRedirect(route('admin.login'));
    }

    public function test_halaman_publik_dapat_dibuka_tanpa_login(): void
    {
        $this->get(route('publik.fasilitas'))->assertOk();
        $this->get(route('publik.prestasi'))->assertOk();
    }
}
