<?php

namespace Tests\Feature\Unggahan;

use App\Exceptions\UnggahanGagal;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class LayananUnggahGambarTest extends TestCase
{
    private LayananUnggahGambar $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->layanan = app(LayananUnggahGambar::class);
    }

    private function gambar(int $lebar = 2400, int $tinggi = 1600, string $ekstensi = 'jpg'): UploadedFile
    {
        return UploadedFile::fake()->image("foto.{$ekstensi}", $lebar, $tinggi);
    }

    private function bacaLebar(string $path): int
    {
        $isi = Storage::disk('public')->get($path);

        return (new ImageManager(new Driver()))->read($isi)->width();
    }

    public function test_gambar_tersimpan_beserta_thumbnailnya(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'potensi');

        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($this->layanan->pathThumbnail($path));
    }

    public function test_gambar_disimpan_di_folder_sesuai_jenisnya(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'pengurus');

        $this->assertStringStartsWith('pengurus/', $path);
        $this->assertSame('pengurus/thumb/'.basename($path), $this->layanan->pathThumbnail($path));
    }

    public function test_gambar_dikonversi_ke_webp(): void
    {
        $path = $this->layanan->simpan($this->gambar(ekstensi: 'png'), 'potensi');

        $this->assertStringEndsWith('.webp', $path);
    }

    public function test_nama_berkas_asli_tidak_bocor_ke_path(): void
    {
        $berkas = UploadedFile::fake()->image('KTP Pak Lurah rahasia.jpg', 800, 600);

        $path = $this->layanan->simpan($berkas, 'potensi');

        $this->assertStringNotContainsString('KTP', $path);
        $this->assertStringNotContainsString('rahasia', $path);
    }

    public function test_gambar_besar_dikecilkan_ke_lebar_maksimum(): void
    {
        $path = $this->layanan->simpan($this->gambar(2400, 1600), 'potensi');

        $this->assertSame(config('unggahan.lebar_maks'), $this->bacaLebar($path));
        $this->assertSame(config('unggahan.lebar_thumbnail'), $this->bacaLebar($this->layanan->pathThumbnail($path)));
    }

    public function test_gambar_kecil_tidak_diperbesar(): void
    {
        $path = $this->layanan->simpan($this->gambar(300, 200), 'potensi');

        $this->assertSame(300, $this->bacaLebar($path));
        $this->assertSame(300, $this->bacaLebar($this->layanan->pathThumbnail($path)));
    }

    public function test_format_tidak_didukung_ditolak(): void
    {
        $this->expectException(UnggahanGagal::class);
        $this->expectExceptionMessage('tidak didukung');

        $this->layanan->simpan(UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'), 'potensi');
    }

    public function test_berkas_melebihi_batas_ukuran_ditolak(): void
    {
        config()->set('unggahan.maks_kb', 100);

        $this->expectException(UnggahanGagal::class);
        $this->expectExceptionMessage('melebihi batas');

        // UploadedFile::fake()->image() tidak bisa dipaksa besar, jadi dipakai
        // create() dengan mime gambar yang sah.
        $this->layanan->simpan(
            UploadedFile::fake()->create('besar.jpg', 500, 'image/jpeg'),
            'potensi',
        );
    }

    public function test_jenis_unggahan_tidak_terdaftar_ditolak(): void
    {
        $this->expectException(UnggahanGagal::class);
        $this->expectExceptionMessage('tidak terdaftar');

        $this->layanan->simpan($this->gambar(), 'jenis-ngawur');
    }

    public function test_hapus_menghilangkan_gambar_dan_thumbnail(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'potensi');
        $thumbnail = $this->layanan->pathThumbnail($path);

        $this->layanan->hapus($path);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumbnail);
    }

    public function test_hapus_dengan_path_kosong_tidak_error(): void
    {
        $this->layanan->hapus(null);
        $this->layanan->hapus('');

        $this->assertTrue(true);
    }

    public function test_ganti_menyimpan_yang_baru_dan_menghapus_yang_lama(): void
    {
        $lama = $this->layanan->simpan($this->gambar(), 'potensi');

        $baru = $this->layanan->ganti($lama, $this->gambar(), 'potensi');

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertExists($baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertMissing($this->layanan->pathThumbnail($lama));
    }

    public function test_ganti_yang_gagal_tidak_menghapus_gambar_lama(): void
    {
        $lama = $this->layanan->simpan($this->gambar(), 'potensi');

        try {
            $this->layanan->ganti($lama, UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'), 'potensi');
            $this->fail('Seharusnya melempar UnggahanGagal.');
        } catch (UnggahanGagal) {
            // Gambar lama wajib tetap utuh — inilah alasan urutannya
            // simpan-baru-dulu, baru hapus-lama.
        }

        Storage::disk('public')->assertExists($lama);
    }

    public function test_url_mengembalikan_placeholder_bila_belum_ada_gambar(): void
    {
        $this->assertStringContainsString(
            'placeholder/pengurus.svg',
            $this->layanan->url(null, 'pengurus'),
        );

        $this->assertStringContainsString(
            'placeholder/umum.svg',
            $this->layanan->url(null, 'jenis-yang-tak-punya-placeholder'),
        );
    }

    public function test_url_mengembalikan_alamat_gambar_bila_ada(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'potensi');

        $this->assertStringContainsString($path, $this->layanan->url($path, 'potensi'));
    }

    public function test_url_thumbnail_mengembalikan_placeholder_bila_belum_ada_gambar(): void
    {
        $this->assertStringContainsString(
            'placeholder/potensi.svg',
            $this->layanan->urlThumbnail(null, 'potensi'),
        );
    }

    // ------------------------------------------------------------------
    // Pembentukan URL tidak boleh menyentuh penyimpanan
    //
    // Menanyakan keberadaan berkas pada setiap gambar berarti satu operasi
    // stat per gambar di disk lokal — dan satu permintaan HTTP per gambar
    // begitu unggahan pindah ke Cloudflare R2. Satu halaman publik memuat
    // belasan gambar, jadi ongkosnya dibayar setiap kunjungan.
    //
    // Kelengkapan berkas dijaga di tempat lain: simpan() menulis gambar
    // utama dan thumbnail sekaligus lalu membatalkan keduanya bila salah
    // satu gagal, hapus() membuang keduanya, dan berkas yatim ditemukan
    // lewat `php artisan unggahan:berkas-yatim`.
    // ------------------------------------------------------------------

    public function test_url_dibentuk_tanpa_memeriksa_keberadaan_berkas(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'potensi');
        Storage::disk('public')->delete($path);

        $this->assertStringContainsString($path, $this->layanan->url($path, 'potensi'));
    }

    public function test_url_thumbnail_dibentuk_tanpa_memeriksa_keberadaan_berkas(): void
    {
        $path = $this->layanan->simpan($this->gambar(), 'potensi');
        $thumbnail = $this->layanan->pathThumbnail($path);
        Storage::disk('public')->delete($thumbnail);

        $this->assertStringContainsString($thumbnail, $this->layanan->urlThumbnail($path, 'potensi'));
    }

    public function test_aturan_validasi_memuat_batas_dari_konfigurasi(): void
    {
        $aturan = LayananUnggahGambar::aturanValidasi(wajib: true);

        $this->assertContains('required', $aturan);
        $this->assertContains('image', $aturan);
        $this->assertContains('mimes:jpg,jpeg,png,webp', $aturan);
        $this->assertContains('max:'.config('unggahan.maks_kb'), $aturan);

        $this->assertContains('nullable', LayananUnggahGambar::aturanValidasi());
    }
}
