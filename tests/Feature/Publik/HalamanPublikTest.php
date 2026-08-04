<?php

namespace Tests\Feature\Publik;

use App\Models\AcaraDesa;
use App\Models\Admin;
use App\Models\BeritaDesa;
use App\Models\Penduduk;
use App\Models\PengaturanDesa;
use App\Models\PotensiDesa;
use App\Models\RekapKk;
use App\Models\Wilayah;
use App\Queries\StatistikPublik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HalamanPublikTest extends TestCase
{
    use RefreshDatabase;

    private Wilayah $krajan;

    protected function setUp(): void
    {
        parent::setUp();

        PengaturanDesa::ambil()->fill([
            'nama_desa' => 'Desa Sukamaju',
            'kecamatan' => 'Cibeureum',
            'visi' => 'Desa mandiri dan sejahtera.',
            'misi' => "Meningkatkan pelayanan\nMengembangkan UMKM",
        ])->save();

        $this->krajan = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
    }

    private function warga(int $jumlah, array $atribut = []): void
    {
        foreach (range(1, $jumlah) as $i) {
            Penduduk::create(array_merge([
                'nama_lengkap' => 'Warga '.fake()->unique()->numberBetween(1, 999999),
                'tanggal_lahir' => Carbon::today()->subYears(30),
                'jenis_kelamin' => 'L',
                'wilayah_id' => $this->krajan->id,
                'jenis_masuk' => 'pendataan_awal',
                'tanggal_masuk' => now()->subYear(),
            ], $atribut));
        }
    }

    // ------------------------------------------------------------------
    // Akses & kerangka
    // ------------------------------------------------------------------

    public function test_seluruh_halaman_publik_dapat_dibuka_tanpa_login(): void
    {
        foreach ([
            'beranda', 'publik.profil', 'publik.statistik', 'publik.potensi.index',
            'publik.fasilitas', 'publik.prestasi', 'publik.berita.index',
            'publik.galeri.index', 'publik.struktur',
        ] as $rute) {
            $this->get(route($rute))->assertOk();
        }
    }

    public function test_identitas_desa_muncul_di_kop_dan_footer(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Desa Sukamaju')
            ->assertSee('Cibeureum');
    }

    public function test_setiap_halaman_punya_judul_dan_meta_description(): void
    {
        foreach (['beranda', 'publik.profil', 'publik.berita.index'] as $rute) {
            $html = $this->get(route($rute))->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/<title>.{5,}<\/title>/', $html);
            $this->assertMatchesRegularExpression('/<meta name="description" content=".{10,}"/', $html);
            $this->assertStringContainsString('og:title', $html);
        }
    }

    public function test_halaman_404_memakai_kerangka_publik(): void
    {
        $this->get('/alamat-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke Beranda');
    }

    // ------------------------------------------------------------------
    // Landing
    // ------------------------------------------------------------------

    public function test_bagian_tanpa_data_tidak_muncul_sebagai_judul_kosong(): void
    {
        // Tanpa konten apa pun, landing tidak boleh menampilkan judul bagian
        // yang menggantung.
        $this->get(route('beranda'))
            ->assertOk()
            ->assertDontSee('Potensi Unggulan')
            ->assertDontSee('Berita Terbaru')
            ->assertDontSee('Galeri Kegiatan');
    }

    public function test_bagian_muncul_setelah_datanya_ada(): void
    {
        PotensiDesa::create(['nama' => 'Keripik Bu Sari', 'kategori' => 'umkm', 'tampil' => true]);
        BeritaDesa::create(['judul' => 'Musyawarah Desa', 'isi' => '<p>Isi</p>', 'status' => 'terbit', 'tanggal_terbit' => now()]);
        AcaraDesa::create(['judul' => 'Kerja Bakti', 'tanggal_acara' => now(), 'status' => 'terbit']);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Potensi Unggulan')
            ->assertSee('Keripik Bu Sari')
            ->assertSee('Berita Terbaru')
            ->assertSee('Galeri Kegiatan');
    }

    public function test_landing_memuat_tombol_ke_setiap_halaman_khusus(): void
    {
        PotensiDesa::create(['nama' => 'Keripik', 'kategori' => 'umkm', 'tampil' => true]);
        BeritaDesa::create(['judul' => 'Berita', 'isi' => '<p>x</p>', 'status' => 'terbit', 'tanggal_terbit' => now()]);

        $html = $this->get(route('beranda'))->assertOk()->getContent();

        $this->assertStringContainsString('Lihat Berita Lebih Lengkap', $html);
        $this->assertStringContainsString('Lihat Semua Potensi', $html);
        $this->assertStringContainsString('Lihat Statistik Lengkap', $html);
        $this->assertStringContainsString(route('publik.berita.index'), $html);
    }

    public function test_landing_hanya_memuat_cuplikan_bukan_seluruh_data(): void
    {
        foreach (range(1, 20) as $i) {
            PotensiDesa::create(['nama' => "Potensi {$i}", 'kategori' => 'umkm', 'tampil' => true]);
        }

        $response = $this->get(route('beranda'))->assertOk();

        $this->assertCount(6, $response->viewData('potensi'));
    }

    // ------------------------------------------------------------------
    // Statistik publik — PRD 2.6
    // ------------------------------------------------------------------

    public function test_kelompok_kecil_tidak_pernah_jadi_angka_pasti(): void
    {
        // Di RT kecil, "1 orang lulusan S3" praktis menunjuk satu orang.
        $this->warga(20, ['pendidikan' => 'sma']);
        $this->warga(2, ['pendidikan' => 's3']);

        $pendidikan = collect((new StatistikPublik())->pendidikan())->keyBy('label');

        $this->assertSame(20, $pendidikan['SMA / SMK / Sederajat']['jumlah']);
        $this->assertNull($pendidikan['Doktor (S3)']['jumlah']);
        $this->assertSame('<5', $pendidikan['Doktor (S3)']['tampilan']);
    }

    public function test_angka_kelompok_kecil_tidak_ikut_terkirim_ke_peramban(): void
    {
        $this->warga(20, ['pendidikan' => 'sma']);
        $this->warga(2, ['pendidikan' => 's3']);

        $json = $this->getJson(route('publik.statistik.data', 'pendidikan'))
            ->assertOk()
            ->json('data');

        $s3 = collect($json)->firstWhere('label', 'Doktor (S3)');

        // Menyembunyikan di tampilan saja tidak cukup: angkanya tetap terbaca
        // lewat "lihat sumber halaman".
        $this->assertNull($s3['jumlah']);

        // Tidak boleh ada satu pun angka di bawah ambang yang lolos ke respons.
        foreach ($json as $baris) {
            $this->assertTrue(
                $baris['jumlah'] === null || $baris['jumlah'] >= StatistikPublik::AMBANG,
                "Kelompok \"{$baris['label']}\" mengirim angka {$baris['jumlah']} yang di bawah ambang.",
            );
        }
    }

    public function test_statistik_publik_tidak_pernah_memuat_data_individu(): void
    {
        $this->warga(10, ['nama_lengkap' => 'Budi Santoso Rahasia']);

        foreach (['umur', 'gender', 'pendidikan'] as $jenis) {
            $isi = $this->getJson(route('publik.statistik.data', $jenis))->assertOk()->content();

            $this->assertStringNotContainsString('Budi Santoso Rahasia', $isi);
            $this->assertStringNotContainsString('tanggal_lahir', $isi);
            $this->assertStringNotContainsString('nama_lengkap', $isi);
        }
    }

    public function test_halaman_statistik_tidak_merinci_sampai_rt(): void
    {
        Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '002']);
        $this->warga(10);

        $html = $this->get(route('publik.statistik'))->assertOk()->getContent();

        // Sebaran publik berhenti di tingkat dusun.
        $this->assertStringNotContainsString('RT 001/RW 001', $html);
        $this->assertStringContainsString('Krajan', $html);
    }

    public function test_endpoint_statistik_publik_menolak_jenis_tak_dikenal(): void
    {
        $this->getJson(route('publik.statistik.data', 'per-rt'))->assertNotFound();
        $this->getJson(route('publik.statistik.data', 'nama-warga'))->assertNotFound();
    }

    public function test_jumlah_kk_disembunyikan_bila_belum_ada_rekap(): void
    {
        $this->warga(10);

        $this->assertNull((new StatistikPublik())->ringkasan()['jumlah_kk']);

        RekapKk::create(['wilayah_id' => $this->krajan->id, 'tahun' => now()->year, 'jumlah_kk' => 3]);

        $this->assertSame(3, (new StatistikPublik())->ringkasan()['jumlah_kk']);
    }

    public function test_angka_publik_sama_dengan_dashboard_admin(): void
    {
        // PRD 2.9: kedua halaman wajib menampilkan Jumlah KK yang identik.
        $this->warga(10);
        RekapKk::create(['wilayah_id' => $this->krajan->id, 'tahun' => now()->year, 'jumlah_kk' => 3]);

        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $publik = (new StatistikPublik())->ringkasan();
        $dashboard = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('kk');

        $this->assertSame($dashboard->total(), $publik['jumlah_kk']);
        $this->assertSame($dashboard->tahunAcuan(), $publik['tahun_kk']);
    }

    public function test_grafik_punya_padanan_tabel_teks(): void
    {
        $this->warga(10);

        // Grafik saja tidak terbaca pembaca layar.
        $this->get(route('publik.statistik'))
            ->assertOk()
            ->assertSee('Lihat dalam bentuk tabel');
    }

    // ------------------------------------------------------------------
    // SEO
    // ------------------------------------------------------------------

    public function test_sitemap_memuat_halaman_utama_dan_detail(): void
    {
        $berita = BeritaDesa::create(['judul' => 'Berita Satu', 'isi' => '<p>x</p>', 'status' => 'terbit', 'tanggal_terbit' => now()]);
        $potensi = PotensiDesa::create(['nama' => 'Keripik', 'kategori' => 'umkm', 'tampil' => true]);

        $isi = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->getContent();

        $this->assertStringContainsString(route('beranda'), $isi);
        $this->assertStringContainsString(route('publik.berita.show', $berita), $isi);
        $this->assertStringContainsString(route('publik.potensi.show', $potensi), $isi);
    }

    public function test_sitemap_tidak_memuat_draf_atau_halaman_admin(): void
    {
        $draf = BeritaDesa::create(['judul' => 'Draf Rahasia', 'isi' => '<p>x</p>', 'status' => 'draf']);

        $isi = $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringNotContainsString($draf->slug, $isi);
        $this->assertStringNotContainsString('/admin', $isi);
    }

    public function test_robots_melarang_pengindeksan_admin(): void
    {
        $isi = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /admin', $isi);
        $this->assertStringContainsString('Sitemap:', $isi);
    }

    public function test_halaman_admin_ditandai_noindex(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('noindex', false);
    }
}
