<?php

namespace Tests\Feature\Penduduk;

use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use App\Exceptions\ImporDitolak;
use App\Imports\ImporPenduduk;
use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImporPendudukTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        Wilayah::create(['dusun' => 'Dusun Krajan', 'rw' => '001', 'rt' => '001']);
        Wilayah::create(['dusun' => 'Dusun Krajan', 'rw' => '001', 'rt' => '002']);
        RefPekerjaan::create(['nama' => 'Petani']);
    }

    /** @param array<int, array<int, string>> $baris */
    private function impor(array $baris, bool $lewatiDuplikat = true)
    {
        return (new ImporPenduduk($lewatiDuplikat))->jalankan($baris);
    }

    private function judul(): array
    {
        return ['Nama Lengkap', 'Tanggal Lahir', 'Jenis Kelamin', 'Dusun', 'RW', 'RT', 'Pekerjaan', 'Pendidikan'];
    }

    // ------------------------------------------------------------------
    // Jalur normal
    // ------------------------------------------------------------------

    public function test_baris_valid_masuk_ke_daftar_warga(): void
    {
        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', 'Petani', 'SMA'],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '1', '2', '', 'S1'],
        ]);

        $this->assertSame(2, $hasil->berhasil);
        $this->assertSame([], $hasil->gagal);
        $this->assertSame(2, Penduduk::count());

        $siti = Penduduk::where('nama_lengkap', 'Siti Nurhayati')->first();
        $this->assertSame(JenisKelamin::Perempuan, $siti->jenis_kelamin);
        $this->assertSame('1985-04-17', $siti->tanggal_lahir->toDateString());
        $this->assertSame(Pendidikan::SMA, $siti->pendidikan);
        $this->assertSame('Petani', $siti->pekerjaan->nama);
    }

    public function test_nomor_rt_rw_tanpa_nol_di_depan_tetap_cocok(): void
    {
        // Admin akan mengetik "1" di Excel, bukan "001".
        $hasil = $this->impor([
            $this->judul(),
            ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '1', '2', '', ''],
        ]);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertSame('002', Penduduk::first()->wilayah->rt);
    }

    public function test_hasil_impor_ditandai_pendataan_awal(): void
    {
        // Bukan "lahir" — kalau salah, seluruh berkas impor akan terhitung
        // sebagai ledakan kelahiran pada tahun berjalan.
        $this->impor([
            $this->judul(),
            ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(JenisMasuk::PendataanAwal, Penduduk::first()->jenis_masuk);
        $this->assertSame(0, Penduduk::query()->lahirPadaTahun(now()->year)->count());
    }

    public function test_judul_kolom_tidak_peka_huruf_besar_kecil_dan_spasi(): void
    {
        $hasil = $this->impor([
            ['  NAMA_LENGKAP ', 'tanggal lahir', 'Jenis  Kelamin', 'DUSUN', 'rw', 'Rt'],
            ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '001', '001'],
        ]);

        $this->assertSame(1, $hasil->berhasil);
    }

    public static function jenisKelaminProvider(): array
    {
        return [
            ['L', JenisKelamin::LakiLaki],
            ['laki-laki', JenisKelamin::LakiLaki],
            ['PRIA', JenisKelamin::LakiLaki],
            ['P', JenisKelamin::Perempuan],
            ['Perempuan', JenisKelamin::Perempuan],
            ['wanita', JenisKelamin::Perempuan],
        ];
    }

    #[DataProvider('jenisKelaminProvider')]
    public function test_penulisan_jenis_kelamin_yang_beragam_diterima(string $tulisan, JenisKelamin $harapan): void
    {
        $this->impor([
            $this->judul(),
            ['Warga', '1990-01-20', $tulisan, 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame($harapan, Penduduk::first()->jenis_kelamin);
    }

    public function test_format_tanggal_indonesia_diterima(): void
    {
        $hasil = $this->impor([
            $this->judul(),
            ['Budi', '17/04/1985', 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertSame('1985-04-17', Penduduk::first()->tanggal_lahir->toDateString());
    }

    public function test_baris_tanpa_tanggal_lahir_tetap_diterima(): void
    {
        // PRD 2.4: kolomnya nullable justru untuk menampung data impor lama
        // yang tidak lengkap.
        $hasil = $this->impor([
            $this->judul(),
            ['Warga Lama', '', 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertNull(Penduduk::first()->tanggal_lahir);
    }

    // ------------------------------------------------------------------
    // Penolakan seluruh berkas
    // ------------------------------------------------------------------

    public static function kolomTerlarangProvider(): array
    {
        return [['NIK'], ['No KK'], ['Nomor Telepon'], ['No HP'], ['Alamat'], ['Agama'], ['Golongan Darah']];
    }

    #[DataProvider('kolomTerlarangProvider')]
    public function test_berkas_dengan_kolom_terlarang_ditolak_seluruhnya(string $kolomTerlarang): void
    {
        $this->expectException(ImporDitolak::class);
        $this->expectExceptionMessage($kolomTerlarang);

        try {
            $this->impor([
                array_merge($this->judul(), [$kolomTerlarang]),
                ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', '', '3204010101900001'],
            ]);
        } finally {
            // Penolakan harus terjadi sebelum satu baris pun diproses.
            $this->assertSame(0, Penduduk::count());
        }
    }

    public function test_berkas_tanpa_kolom_wajib_ditolak(): void
    {
        $this->expectException(ImporDitolak::class);
        $this->expectExceptionMessage('Kolom wajib belum ada');

        $this->impor([
            ['Nama Lengkap', 'Tanggal Lahir'],
            ['Budi', '1990-01-20'],
        ]);
    }

    public function test_berkas_kosong_ditolak(): void
    {
        $this->expectException(ImporDitolak::class);

        $this->impor([$this->judul()]);
    }

    public function test_berkas_melebihi_batas_baris_ditolak(): void
    {
        config()->set('impor.maks_baris', 3);

        $this->expectException(ImporDitolak::class);
        $this->expectExceptionMessage('melebihi batas');

        $baris = [$this->judul()];
        for ($i = 0; $i < 5; $i++) {
            $baris[] = ["Warga {$i}", '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''];
        }

        try {
            $this->impor($baris);
        } finally {
            $this->assertSame(0, Penduduk::count());
        }
    }

    // ------------------------------------------------------------------
    // Baris gagal
    // ------------------------------------------------------------------

    public function test_baris_gagal_dilaporkan_dengan_nomor_dan_alasan(): void
    {
        $hasil = $this->impor([
            $this->judul(),
            ['Valid Satu', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
            ['', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
            ['Gender Ngawur', '1990-01-20', 'X', 'Dusun Krajan', '001', '001', '', ''],
            ['Wilayah Asing', '1990-01-20', 'L', 'Dusun Antah', '009', '009', '', ''],
            ['Kerja Asing', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', 'Astronot', ''],
            ['Lahir Besok', now()->addYear()->format('Y-m-d'), 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertCount(5, $hasil->gagal);

        // Nomor baris harus merujuk baris di berkas, termasuk baris judul.
        $this->assertSame(3, $hasil->gagal[0]['baris']);
        $this->assertStringContainsString('Nama lengkap kosong', $hasil->gagal[0]['alasan']);
        $this->assertStringContainsString('L atau P', $hasil->gagal[1]['alasan']);
        $this->assertStringContainsString('belum terdaftar', $hasil->gagal[2]['alasan']);
        $this->assertStringContainsString('Astronot', $hasil->gagal[3]['alasan']);
        $this->assertStringContainsString('melewati hari ini', $hasil->gagal[4]['alasan']);
    }

    public function test_wilayah_asing_tidak_dibuat_otomatis(): void
    {
        // Salah ketik dusun akan diam-diam menambah wilayah palsu dan
        // memecah statistik sebaran kalau dibuat otomatis.
        $this->impor([
            $this->judul(),
            ['Budi', '1990-01-20', 'L', 'Dusun Krajaan', '001', '001', '', ''],
        ]);

        $this->assertSame(2, Wilayah::count());
    }

    // ------------------------------------------------------------------
    // Duplikat
    // ------------------------------------------------------------------

    public function test_duplikat_terhadap_data_yang_sudah_ada_dilewati(): void
    {
        Penduduk::create([
            'nama_lengkap' => 'Siti Nurhayati',
            'tanggal_lahir' => '1985-04-17',
            'jenis_kelamin' => 'P',
            'wilayah_id' => Wilayah::first()->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now(),
        ]);

        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(0, $hasil->berhasil);
        $this->assertCount(1, $hasil->duplikat);
        $this->assertSame(1, Penduduk::count());
    }

    public function test_duplikat_di_dalam_berkas_yang_sama_juga_dilewati(): void
    {
        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['siti   nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertCount(1, $hasil->duplikat);
    }

    public function test_nama_sama_tapi_wilayah_berbeda_bukan_duplikat(): void
    {
        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '002', '', ''],
        ]);

        $this->assertSame(2, $hasil->berhasil);
        $this->assertSame([], $hasil->duplikat);
    }

    public function test_duplikat_bisa_dipaksa_masuk(): void
    {
        // Dua warga berbeda bisa saja punya nama, tanggal lahir, dan wilayah
        // yang sama persis. Admin yang memutuskan.
        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ], lewatiDuplikat: false);

        $this->assertSame(2, $hasil->berhasil);
        $this->assertSame(2, Penduduk::count());
    }

    public function test_duplikat_terhadap_warga_terarsip_juga_terdeteksi(): void
    {
        $warga = Penduduk::create([
            'nama_lengkap' => 'Siti Nurhayati',
            'tanggal_lahir' => '1985-04-17',
            'jenis_kelamin' => 'P',
            'wilayah_id' => Wilayah::first()->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now(),
        ]);
        $warga->delete();

        $hasil = $this->impor([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertCount(1, $hasil->duplikat);
    }

    // ------------------------------------------------------------------
    // Antarmuka
    // ------------------------------------------------------------------

    public function test_halaman_impor_dapat_dibuka(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.impor'))
            ->assertOk()
            ->assertSee('Susunan Kolom')
            ->assertSee('Unduh Berkas Contoh');
    }

    public function test_berkas_contoh_dapat_diunduh(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.impor.contoh'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $isi = $response->streamedContent();

        $this->assertStringContainsString('Nama Lengkap', $isi);
        $this->assertStringContainsString('Jenis Kelamin', $isi);
        // BOM UTF-8 agar Excel menampilkan huruf beraksen dengan benar.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $isi);
    }

    public function test_unggah_csv_lewat_antarmuka_berhasil(): void
    {
        $csv = "Nama Lengkap,Tanggal Lahir,Jenis Kelamin,Dusun,RW,RT\n"
            ."Siti Nurhayati,1985-04-17,P,Dusun Krajan,001,001\n"
            ."Budi Santoso,1990-01-20,L,Dusun Krajan,001,002\n";

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.impor.proses'), [
                'berkas' => UploadedFile::fake()->createWithContent('warga.csv', $csv),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertSame(2, Penduduk::count());
    }

    public function test_unggah_berkas_dengan_kolom_terlarang_ditolak_di_antarmuka(): void
    {
        $csv = "Nama Lengkap,NIK,Jenis Kelamin,Dusun,RW,RT\n"
            ."Budi,3204010101900001,L,Dusun Krajan,001,001\n";

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.impor.proses'), [
                'berkas' => UploadedFile::fake()->createWithContent('warga.csv', $csv),
            ])
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, Penduduk::count());
    }

    public function test_berkas_bukan_spreadsheet_ditolak(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.impor.proses'), [
                'berkas' => UploadedFile::fake()->create('gambar.jpg', 10, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('berkas');
    }

    public function test_tamu_ditolak(): void
    {
        $this->get(route('admin.penduduk.impor'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.penduduk.impor.contoh'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.penduduk.impor.proses'), [])->assertRedirect(route('admin.login'));
    }
}
