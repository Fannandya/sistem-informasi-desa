<?php

namespace Tests\Feature\Publik;

use App\Enums\JenisMasuk;
use App\Models\Penduduk;
use App\Models\RekapKk;
use App\Models\Wilayah;
use App\Queries\StatistikPublik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Statistik publik dihitung ulang pada setiap kunjungan — termasuk di
 * beranda, halaman yang paling sering dibuka. Angkanya berasal dari agregat
 * atas seluruh tabel penduduk, sementara datanya hanya berubah ketika aparat
 * desa menyunting sesuatu.
 *
 * Cache di sini tidak boleh "selamanya": sebaran umur dihitung terhadap
 * tanggal hari ini, jadi hasil kemarin sudah salah begitu hari berganti.
 */
class StatistikPublikCacheTest extends TestCase
{
    use RefreshDatabase;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001', 'urutan' => 1]);
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge([
            'nama_lengkap' => 'Warga '.fake()->unique()->numberBetween(1, 999999),
            'tanggal_lahir' => Carbon::today()->subYears(30),
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'jenis_masuk' => JenisMasuk::PendataanAwal,
            'tanggal_masuk' => Carbon::today()->subYear(),
        ], $atribut));
    }

    /** Jumlah kueri yang dijalankan oleh sebuah tindakan. */
    private function jumlahKueri(callable $tindakan): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $tindakan();

        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $jumlah;
    }

    public function test_ringkasan_kedua_tidak_menyentuh_basis_data_lagi(): void
    {
        $this->warga();

        $pertama = $this->jumlahKueri(fn () => (new StatistikPublik())->ringkasan());
        $kedua = $this->jumlahKueri(fn () => (new StatistikPublik())->ringkasan());

        $this->assertGreaterThan(0, $pertama);
        $this->assertSame(0, $kedua, 'Ringkasan kedua seharusnya dilayani dari cache.');
    }

    public function test_sebaran_umur_kedua_tidak_menyentuh_basis_data_lagi(): void
    {
        $this->warga();

        $this->jumlahKueri(fn () => (new StatistikPublik())->sebaranUmur());
        $kedua = $this->jumlahKueri(fn () => (new StatistikPublik())->sebaranUmur());

        $this->assertSame(0, $kedua);
    }

    public function test_menambah_warga_menyegarkan_statistik(): void
    {
        $this->warga();
        $sebelum = (new StatistikPublik())->ringkasan()['jumlah_penduduk'];

        $this->warga();
        $sesudah = (new StatistikPublik())->ringkasan()['jumlah_penduduk'];

        $this->assertSame($sebelum + 1, $sesudah);
    }

    public function test_mengarsipkan_warga_menyegarkan_statistik(): void
    {
        $warga = $this->warga();
        $sebelum = (new StatistikPublik())->ringkasan()['jumlah_penduduk'];

        $warga->delete();

        $this->assertSame($sebelum - 1, (new StatistikPublik())->ringkasan()['jumlah_penduduk']);
    }

    public function test_mengubah_rekap_kk_menyegarkan_statistik(): void
    {
        $this->warga();
        $this->assertNull((new StatistikPublik())->ringkasan()['jumlah_kk']);

        RekapKk::create([
            'wilayah_id' => $this->wilayah->id,
            'tahun' => now()->year,
            'jumlah_kk' => 40,
        ]);

        $this->assertSame(40, (new StatistikPublik())->ringkasan()['jumlah_kk']);
    }

    public function test_menambah_wilayah_menyegarkan_jumlah_dusun(): void
    {
        $this->warga();
        $sebelum = (new StatistikPublik())->ringkasan()['jumlah_dusun'];

        Wilayah::create(['dusun' => 'Sukasari', 'rw' => '002', 'rt' => '001', 'urutan' => 2]);

        $this->assertSame($sebelum + 1, (new StatistikPublik())->ringkasan()['jumlah_dusun']);
    }

    /**
     * Sebaran umur dihitung terhadap hari ini. Cache yang tidak pernah
     * kedaluwarsa akan menampilkan komposisi umur kemarin selamanya.
     */
    public function test_statistik_dihitung_ulang_setelah_hari_berganti(): void
    {
        // Lima warga yang besok berulang tahun ke-19: hari ini mereka masih
        // Remaja (13–18), lusa sudah Dewasa Muda (19–35). Lima orang dipakai
        // agar angkanya melewati ambang k-anonimitas dan benar-benar tampil.
        for ($i = 0; $i < 5; $i++) {
            $this->warga(['tanggal_lahir' => Carbon::today()->subYears(19)->addDay()]);
        }

        $remaja = fn () => collect((new StatistikPublik())->sebaranUmur())
            ->firstWhere('label', 'Remaja')['jumlah'];

        $this->assertSame(5, $remaja());

        $this->travel(2)->days();

        $this->assertNull($remaja(), 'Sebaran umur seharusnya dihitung ulang setelah tanggal berganti.');
    }

    /**
     * Impor warga menyimpan sampai 2.000 baris dalam satu transaksi. Bila
     * setiap baris benar-benar menghapus tujuh kunci cache, satu impor
     * berubah menjadi belasan ribu kueri DELETE pada penyimpanan cache
     * database — memperbaiki beban baca dengan menciptakan beban tulis.
     */
    public function test_penyimpanan_massal_tidak_membanjiri_cache(): void
    {
        config()->set('cache.default', 'database');

        // Keadaan yang ditiru: situs sudah melayani pengunjung sehingga
        // cachenya hangat, lalu sebuah impor masuk.
        (new StatistikPublik())->ringkasan();

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Cache dikosongkan sekali oleh warga pertama; sesudah itu ia memang
        // sudah kosong, jadi tidak ada yang perlu dihapus lagi.
        for ($i = 0; $i < 20; $i++) {
            $this->warga();
        }

        $penghapusanCache = collect(DB::getQueryLog())
            ->filter(fn (array $k) => str_contains(strtolower($k['query']), 'delete from "cache"'))
            ->count();

        DB::disableQueryLog();

        $this->assertGreaterThan(0, $penghapusanCache, 'Cache tetap harus dikosongkan sekali.');

        $this->assertLessThanOrEqual(
            StatistikPublik::jumlahKunciCache(),
            $penghapusanCache,
            'Penyimpanan massal seharusnya mengosongkan cache sekali saja, bukan per baris.',
        );
    }

    // ------------------------------------------------------------------
    // Pembatasan laju
    //
    // Endpoint grafik terbuka tanpa autentikasi. Cache membuat tiap
    // permintaan murah, tetapi tidak gratis — dan tanpa batas, satu skrip
    // sederhana cukup untuk menyibukkan hosting bersama milik desa.
    // ------------------------------------------------------------------

    public function test_endpoint_data_statistik_dibatasi_lajunya(): void
    {
        $this->warga();

        $batas = 60;

        for ($i = 0; $i < $batas; $i++) {
            $this->get('/statistik/data/umur')->assertOk();
        }

        $this->get('/statistik/data/umur')->assertStatus(429);
    }

    public function test_halaman_statistik_biasa_tidak_ikut_dibatasi_seketat_itu(): void
    {
        $this->warga();

        for ($i = 0; $i < 61; $i++) {
            $this->get('/statistik/data/umur');
        }

        // Halaman HTML-nya memakai jatah terpisah dan tetap bisa dibuka.
        $this->get('/statistik')->assertOk();
    }

    public function test_piramida_menyensor_sel_di_bawah_ambang(): void
    {
        foreach (range(1, 3) as $i) {
            $this->warga(['tanggal_lahir' => Carbon::today()->subYears(3), 'jenis_kelamin' => 'L']);
        }
        foreach (range(1, 6) as $i) {
            $this->warga(['tanggal_lahir' => Carbon::today()->subYears(3), 'jenis_kelamin' => 'P']);
        }

        $hasil = collect((new StatistikPublik())->piramidaUmurGender())->keyBy('label');

        $this->assertNull($hasil['0–4']['laki']);
        $this->assertSame('<5', $hasil['0–4']['laki_tampilan']);
        $this->assertSame(6, $hasil['0–4']['perempuan']);
    }

    public function test_agama_publik_menyensor_kelompok_kecil(): void
    {
        $agama = \App\Models\RefAgama::create(['nama' => 'Konghucu']);
        foreach (range(1, 3) as $i) {
            $this->warga(['agama_id' => $agama->id]);
        }

        $hasil = collect((new StatistikPublik())->agama())->keyBy('label');

        $this->assertNull($hasil['Konghucu']['jumlah']);
        $this->assertSame('<5', $hasil['Konghucu']['tampilan']);
    }

    public function test_untuk_grafik_piramida_mengembalikan_data_piramida(): void
    {
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(30)]);

        $data = (new StatistikPublik())->untukGrafik('piramida');

        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('laki', $data->first());
    }

    /**
     * assertIsArray() pada tipe kembalian yang sudah dideklarasikan `array`
     * selalu benar — tidak membuktikan apa pun soal cache. Tes ini
     * membandingkan angka sungguhan sebelum/sesudah, dengan kelompok uji
     * yang sudah di atas ambang sedari awal (lihat komentar di bawah)
     * supaya sensor privasi tidak menutupi selisihnya.
     */
    public function test_cache_piramida_agama_perkawinan_pekerjaan_dikosongkan_bersama(): void
    {
        $agama = \App\Models\RefAgama::create(['nama' => 'Cache Uji']);
        $pekerjaan = \App\Models\RefPekerjaan::create(['nama' => 'Cache Uji']);

        $atributUji = [
            'tanggal_lahir' => Carbon::today()->subYears(30),
            'jenis_kelamin' => 'L',
            'agama_id' => $agama->id,
            'status_perkawinan' => 'kawin_tercatat',
            'pekerjaan_id' => $pekerjaan->id,
        ];

        // Kelompok uji diisi sampai tepat di ambang lebih dulu. Kalau
        // dimulai dari nol, penambahan satu warga di bawah akan tetap
        // tersensor jadi "<5" di kedua sisi — perbandingannya tidak akan
        // pernah bisa membuktikan bahwa cache benar-benar disegarkan.
        foreach (range(1, StatistikPublik::AMBANG) as $i) {
            $this->warga($atributUji);
        }

        $ambilAngka = fn () => [
            'piramida' => collect((new StatistikPublik())->piramidaUmurGender())->firstWhere('label', '30–34')['laki'],
            'agama' => collect((new StatistikPublik())->agama())->firstWhere('label', 'Cache Uji')['jumlah'],
            'perkawinan' => collect((new StatistikPublik())->statusPerkawinan())
                ->firstWhere('label', \App\Enums\StatusPerkawinan::KawinTercatat->label())['jumlah'],
            'pekerjaan' => collect((new StatistikPublik())->sebaranPekerjaan())->firstWhere('label', 'Cache Uji')['jumlah'],
        ];

        // Cache terisi dengan kondisi awal (tepat di ambang, jadi sudah
        // tampil sebagai angka pasti untuk keempat statistik).
        $sebelum = $ambilAngka();

        $this->warga($atributUji);

        StatistikPublik::lupakanCache();

        $sesudah = $ambilAngka();

        // Setiap statistik harus naik satu — kalau salah satu kunci cache
        // lupa didaftarkan di KUNCI_CACHE, nilainya akan tetap sama dengan
        // $sebelum karena masih dilayani dari cache lama.
        $this->assertSame($sebelum['piramida'] + 1, $sesudah['piramida']);
        $this->assertSame($sebelum['agama'] + 1, $sesudah['agama']);
        $this->assertSame($sebelum['perkawinan'] + 1, $sesudah['perkawinan']);
        $this->assertSame($sebelum['pekerjaan'] + 1, $sesudah['pekerjaan']);
    }

    public function test_halaman_statistik_mengirim_data_piramida_pekerjaan_agama_perkawinan(): void
    {
        $this->warga();

        $respons = $this->get(route('publik.statistik'));

        $respons->assertOk();
        $respons->assertViewHas('piramida');
        $respons->assertViewHas('sebaranPekerjaan');
        $respons->assertViewHas('agama');
        $respons->assertViewHas('statusPerkawinan');
    }

    public function test_endpoint_data_piramida_mengembalikan_json(): void
    {
        $this->warga();

        $this->getJson(route('publik.statistik.data', 'piramida'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_endpoint_data_jenis_ngawur_ditolak(): void
    {
        $this->getJson(route('publik.statistik.data', 'entah-apa'))
            ->assertNotFound();
    }
}
