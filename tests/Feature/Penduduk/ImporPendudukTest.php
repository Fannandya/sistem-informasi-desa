<?php

namespace Tests\Feature\Penduduk;

use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use App\Exceptions\ImporDitolak;
use App\Imports\AnalisisImpor;
use App\Imports\ImporPenduduk;
use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImporPendudukTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Berkas pratinjau ditulis ke disk lokal. Tanpa fake, sisa berkas dari
        // satu tes akan terlihat oleh tes berikutnya.
        Storage::fake('local');

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        Wilayah::create(['dusun' => 'Dusun Krajan', 'rw' => '001', 'rt' => '001']);
        Wilayah::create(['dusun' => 'Dusun Krajan', 'rw' => '001', 'rt' => '002']);
        RefPekerjaan::create(['nama' => 'Petani']);
    }

    private function judul(): array
    {
        return ['Nama Lengkap', 'Tanggal Lahir', 'Jenis Kelamin', 'Dusun', 'RW', 'RT', 'Pekerjaan', 'Pendidikan'];
    }

    /** @param array<int, array<int, string>> $baris */
    private function analisis(array $baris)
    {
        return (new AnalisisImpor())->jalankan($baris);
    }

    /** Membuat berkas CSV dari baris, untuk pengujian lewat antarmuka. */
    private function berkas(array $baris): UploadedFile
    {
        $isi = collect($baris)->map(fn ($b) => implode(',', $b))->implode("\n")."\n";

        return UploadedFile::fake()->createWithContent('warga.csv', $isi);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function warga(string $nama, string $tanggalLahir, ?int $wilayahId = null): Penduduk
    {
        return Penduduk::create([
            'nama_lengkap' => $nama,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => 'P',
            'wilayah_id' => $wilayahId ?? Wilayah::first()->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now(),
        ]);
    }

    // ==================================================================
    // Analisis — tidak boleh menyimpan apa pun
    // ==================================================================

    public function test_analisis_tidak_menyimpan_satu_baris_pun(): void
    {
        $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', 'Petani', 'SMA'],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '1', '2', '', 'S1'],
        ]);

        $this->assertSame(0, Penduduk::count());
    }

    public function test_baris_valid_masuk_daftar_siap(): void
    {
        $hasil = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', 'Petani', 'SMA'],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '1', '2', '', 'S1'],
        ]);

        $this->assertCount(2, $hasil->siap);
        $this->assertSame([], $hasil->gagal);
        $this->assertFalse($hasil->adaGanda());

        $siti = $hasil->siap[0];
        $this->assertSame('Siti Nurhayati', $siti->nama);
        $this->assertSame('1985-04-17', $siti->tanggalLahir);
        $this->assertSame('Perempuan', $siti->jenisKelamin);
        $this->assertSame('Dusun Krajan — RT 001/RW 001', $siti->wilayah);
        $this->assertSame('Petani', $siti->pekerjaan);
    }

    // ==================================================================
    // Deteksi ganda — harus menunjukkan data pembandingnya
    // ==================================================================

    public function test_ganda_terhadap_warga_yang_sudah_ada_menyertakan_data_pembanding(): void
    {
        $lama = $this->warga('Siti Nurhayati', '1985-04-17');

        $hasil = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertCount(1, $hasil->ganda);
        $this->assertSame([], $hasil->siap);

        $baris = $hasil->ganda[0];

        // Admin harus bisa melihat data mana yang dianggap sama.
        $this->assertNotNull($baris->miripDengan);
        $this->assertTrue($baris->miripDengan->is($lama));
        $this->assertStringContainsString('daftar warga aktif', $baris->alasanGanda());
    }

    public function test_ganda_terhadap_warga_terarsip_ditandai_berbeda(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17')->delete();

        $hasil = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertStringContainsString('arsip', $hasil->ganda[0]->alasanGanda());
        $this->assertTrue($hasil->ganda[0]->miripDengan->trashed());
    }

    public function test_ganda_di_dalam_berkas_menyebut_nomor_barisnya(): void
    {
        $hasil = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['siti   nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertCount(1, $hasil->siap);
        $this->assertCount(1, $hasil->ganda);
        $this->assertSame(2, $hasil->ganda[0]->gandaDenganBaris);
        $this->assertStringContainsString('baris 2', $hasil->ganda[0]->alasanGanda());
    }

    public function test_nama_sama_wilayah_berbeda_bukan_ganda(): void
    {
        $hasil = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '002', '', ''],
        ]);

        $this->assertCount(2, $hasil->siap);
        $this->assertFalse($hasil->adaGanda());
    }

    // ==================================================================
    // Penyimpanan — mengikuti keputusan admin
    // ==================================================================

    public function test_tanpa_persetujuan_data_ganda_tidak_ikut_tersimpan(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');

        $analisis = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
        ]);

        $hasil = (new ImporPenduduk())->simpan($analisis, barisGandaDisetujui: []);

        $this->assertSame(1, $hasil->berhasil);
        $this->assertSame(1, $hasil->gandaDilewati);
        $this->assertSame(2, Penduduk::count()); // 1 lama + 1 baru
    }

    public function test_data_ganda_yang_disetujui_admin_tetap_tersimpan(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');

        $analisis = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $nomorBaris = $analisis->ganda[0]->nomorBaris;
        $hasil = (new ImporPenduduk())->simpan($analisis, [$nomorBaris]);

        // Dua warga berbeda bisa saja punya nama, tanggal lahir, dan wilayah
        // yang sama persis. Admin yang memutuskan, bukan sistem.
        $this->assertSame(1, $hasil->berhasil);
        $this->assertSame(1, $hasil->gandaDiterima);
        $this->assertSame(2, Penduduk::where('nama_lengkap', 'Siti Nurhayati')->count());
    }

    public function test_hanya_baris_ganda_yang_dicentang_yang_masuk(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');
        $this->warga('Budi Santoso', '1990-01-20');

        $analisis = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);

        $this->assertCount(2, $analisis->ganda);

        // Hanya baris 2 yang disetujui.
        (new ImporPenduduk())->simpan($analisis, [2]);

        $this->assertSame(2, Penduduk::where('nama_lengkap', 'Siti Nurhayati')->count());
        $this->assertSame(1, Penduduk::where('nama_lengkap', 'Budi Santoso')->count());
    }

    public function test_hasil_impor_ditandai_pendataan_awal(): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
        ]);
        (new ImporPenduduk())->simpan($analisis);

        // Bukan "lahir": satu berkas impor tidak boleh terbaca sebagai
        // ledakan kelahiran pada tahun berjalan.
        $this->assertSame(JenisMasuk::PendataanAwal, Penduduk::first()->jenis_masuk);
        $this->assertSame(0, Penduduk::query()->lahirPadaTahun(now()->year)->count());
    }

    public function test_ringkasan_menyebut_keputusan_admin_bukan_keputusan_sistem(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');

        $analisis = $this->analisis([
            $this->judul(),
            ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
            ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
        ]);

        $ringkasan = (new ImporPenduduk())->simpan($analisis, $analisis->nomorBarisGanda())->ringkasan();

        $this->assertStringContainsString('Anda setujui', $ringkasan);
        $this->assertStringNotContainsString('dilewati karena terdeteksi ganda', $ringkasan);
    }

    // ==================================================================
    // Baris bermasalah
    // ==================================================================

    public function test_baris_gagal_dilaporkan_dan_tidak_menghalangi_baris_lain(): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['Valid Satu', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
            ['', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', ''],
            ['Gender Ngawur', '1990-01-20', 'X', 'Dusun Krajan', '001', '001', '', ''],
            ['Wilayah Asing', '1990-01-20', 'L', 'Dusun Antah', '009', '009', '', ''],
            ['Kerja Asing', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', 'Astronot', ''],
        ]);

        $this->assertCount(1, $analisis->siap);
        $this->assertCount(4, $analisis->gagal);
        $this->assertSame(3, $analisis->gagal[0]['baris']);
        $this->assertStringContainsString('Nama lengkap kosong', $analisis->gagal[0]['alasan']);

        (new ImporPenduduk())->simpan($analisis);

        $this->assertSame(1, Penduduk::count());
    }

    public function test_wilayah_asing_tidak_dibuat_otomatis(): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['Budi', '1990-01-20', 'L', 'Dusun Krajaan', '001', '001', '', ''],
        ]);
        (new ImporPenduduk())->simpan($analisis);

        $this->assertSame(2, Wilayah::count());
    }

    // ==================================================================
    // Penolakan seluruh berkas
    // ==================================================================

    public static function kolomTerlarangProvider(): array
    {
        return [['NIK'], ['No KK'], ['Nomor Telepon'], ['Alamat'], ['Agama']];
    }

    #[DataProvider('kolomTerlarangProvider')]
    public function test_berkas_dengan_kolom_terlarang_ditolak(string $kolom): void
    {
        $this->expectException(ImporDitolak::class);
        $this->expectExceptionMessage($kolom);

        $this->analisis([
            array_merge($this->judul(), [$kolom]),
            ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', '', '3204010101900001'],
        ]);
    }

    public function test_berkas_tanpa_kolom_wajib_ditolak(): void
    {
        $this->expectException(ImporDitolak::class);
        $this->expectExceptionMessage('Kolom wajib belum ada');

        $this->analisis([['Nama Lengkap', 'Tanggal Lahir'], ['Budi', '1990-01-20']]);
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

        $this->analisis($baris);
    }

    // ==================================================================
    // Alur antarmuka: unggah → pratinjau → lanjut / batal
    // ==================================================================

    public function test_unggah_menampilkan_pratinjau_tanpa_menyimpan(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');

        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.pratinjau'), [
                'berkas' => $this->berkas([
                    $this->judul(),
                    ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
                    ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
                ]),
            ])
            ->assertOk()
            ->assertSee('Belum ada data yang tersimpan')
            ->assertSee('terdeteksi sama dengan warga yang sudah ada')
            ->assertSee('Lanjut Impor')
            ->assertSee('Batal');

        // Satu warga lama saja; belum ada yang ditambahkan.
        $this->assertSame(1, Penduduk::count());
    }

    public function test_pratinjau_menampilkan_daftar_yang_akan_ditambahkan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.pratinjau'), [
                'berkas' => $this->berkas([
                    $this->judul(),
                    ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', 'Petani', 'S1'],
                ]),
            ])
            ->assertOk()
            ->assertSee('warga yang akan ditambahkan')
            ->assertSee('Budi Santoso')
            ->assertSee('Dusun Krajan — RT 002/RW 001', false)
            ->assertSee('Petani');
    }

    public function test_lanjut_impor_menyimpan_sesuai_centang_admin(): void
    {
        $this->warga('Siti Nurhayati', '1985-04-17');

        $response = $this->sebagaiAdmin()->post(route('admin.penduduk.impor.pratinjau'), [
            'berkas' => $this->berkas([
                $this->judul(),
                ['Siti Nurhayati', '1985-04-17', 'P', 'Dusun Krajan', '001', '001', '', ''],
                ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
            ]),
        ])->assertOk();

        $token = $response->viewData('token');

        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.proses'), ['token' => $token, 'ganda' => [2]])
            ->assertRedirect(route('admin.penduduk.index'))
            ->assertSessionHas('status');

        // 1 lama + Budi + Siti kedua yang disetujui admin.
        $this->assertSame(3, Penduduk::count());
    }

    public function test_batal_membuang_berkas_tanpa_menyimpan(): void
    {
        $response = $this->sebagaiAdmin()->post(route('admin.penduduk.impor.pratinjau'), [
            'berkas' => $this->berkas([
                $this->judul(),
                ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
            ]),
        ])->assertOk();

        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.batal'))
            ->assertRedirect(route('admin.penduduk.impor'))
            ->assertSessionHas('status');

        $this->assertSame(0, Penduduk::count());

        // Berkas sementara ikut dibuang.
        $this->assertEmpty(Storage::files('impor-sementara'));

        // Token lama tidak bisa dipakai lagi setelah dibatalkan.
        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.proses'), ['token' => $response->viewData('token')])
            ->assertRedirect(route('admin.penduduk.impor'))
            ->assertSessionHasErrors('berkas');
    }

    public function test_token_milik_sesi_lain_ditolak(): void
    {
        $this->sebagaiAdmin()->post(route('admin.penduduk.impor.pratinjau'), [
            'berkas' => $this->berkas([
                $this->judul(),
                ['Budi Santoso', '1990-01-20', 'L', 'Dusun Krajan', '001', '002', '', ''],
            ]),
        ])->assertOk();

        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.proses'), ['token' => 'token-tebakan-orang-lain'])
            ->assertRedirect(route('admin.penduduk.impor'))
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, Penduduk::count());
    }

    public function test_berkas_dengan_kolom_terlarang_ditolak_di_antarmuka(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.pratinjau'), [
                'berkas' => $this->berkas([
                    ['Nama Lengkap', 'NIK', 'Jenis Kelamin', 'Dusun', 'RW', 'RT'],
                    ['Budi', '3204010101900001', 'L', 'Dusun Krajan', '001', '001'],
                ]),
            ])
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, Penduduk::count());
        $this->assertEmpty(Storage::files('impor-sementara'));
    }

    public function test_berkas_bukan_spreadsheet_ditolak(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.penduduk.impor.pratinjau'), [
                'berkas' => UploadedFile::fake()->create('gambar.jpg', 10, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('berkas');
    }

    public function test_halaman_impor_dan_berkas_contoh_tersedia(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.impor'))
            ->assertOk()
            ->assertSee('Susunan Kolom')
            ->assertSee('Periksa Berkas')
            ->assertSee('tidak langsung disimpan');

        $isi = $this->sebagaiAdmin()
            ->get(route('admin.penduduk.impor.contoh'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $isi);
        $this->assertStringContainsString('Nama Lengkap', $isi);
    }

    public function test_tamu_ditolak(): void
    {
        $this->get(route('admin.penduduk.impor'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.penduduk.impor.pratinjau'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.penduduk.impor.proses'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.penduduk.impor.batal'))->assertRedirect(route('admin.login'));
    }

    // ==================================================================
    // Toleransi penulisan
    // ==================================================================

    public static function jenisKelaminProvider(): array
    {
        return [
            ['L', JenisKelamin::LakiLaki], ['laki-laki', JenisKelamin::LakiLaki], ['PRIA', JenisKelamin::LakiLaki],
            ['P', JenisKelamin::Perempuan], ['Perempuan', JenisKelamin::Perempuan], ['wanita', JenisKelamin::Perempuan],
        ];
    }

    #[DataProvider('jenisKelaminProvider')]
    public function test_penulisan_jenis_kelamin_beragam_diterima(string $tulisan, JenisKelamin $harapan): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['Warga', '1990-01-20', $tulisan, 'Dusun Krajan', '001', '001', '', ''],
        ]);
        (new ImporPenduduk())->simpan($analisis);

        $this->assertSame($harapan, Penduduk::first()->jenis_kelamin);
    }

    public function test_format_tanggal_indonesia_dan_baris_tanpa_tanggal_diterima(): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['Budi', '17/04/1985', 'L', 'Dusun Krajan', '001', '001', '', ''],
            ['Warga Lama', '', 'L', 'Dusun Krajan', '001', '002', '', ''],
        ]);
        (new ImporPenduduk())->simpan($analisis);

        $this->assertSame('1985-04-17', Penduduk::where('nama_lengkap', 'Budi')->first()->tanggal_lahir->toDateString());
        $this->assertNull(Penduduk::where('nama_lengkap', 'Warga Lama')->first()->tanggal_lahir);
    }

    public function test_judul_kolom_tidak_peka_huruf_besar_kecil(): void
    {
        $hasil = $this->analisis([
            ['  NAMA_LENGKAP ', 'tanggal lahir', 'Jenis  Kelamin', 'DUSUN', 'rw', 'Rt'],
            ['Budi', '1990-01-20', 'L', 'Dusun Krajan', '001', '001'],
        ]);

        $this->assertCount(1, $hasil->siap);
    }

    public function test_pendidikan_diterjemahkan_dari_berbagai_penulisan(): void
    {
        $analisis = $this->analisis([
            $this->judul(),
            ['A', '1990-01-20', 'L', 'Dusun Krajan', '001', '001', '', 'SMA'],
            ['B', '1991-01-20', 'L', 'Dusun Krajan', '001', '002', '', 'Sarjana'],
        ]);
        (new ImporPenduduk())->simpan($analisis);

        $this->assertSame(Pendidikan::SMA, Penduduk::where('nama_lengkap', 'A')->first()->pendidikan);
        $this->assertSame(Pendidikan::S1, Penduduk::where('nama_lengkap', 'B')->first()->pendidikan);
    }
}
