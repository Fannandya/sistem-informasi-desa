<?php

namespace Tests\Feature\Penduduk;

use App\Enums\AlasanKeluar;
use App\Enums\JenisMasuk;
use App\Enums\KelompokUmur;
use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefAgama;
use App\Models\RefPekerjaan;
use App\Models\RekapKk;
use App\Models\Wilayah;
use App\Queries\StatistikPenduduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatistikTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Wilayah $krajan;

    private Wilayah $sukasari;

    private StatistikPenduduk $statistik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->krajan = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001', 'urutan' => 1]);
        $this->sukasari = Wilayah::create(['dusun' => 'Sukasari', 'rw' => '002', 'rt' => '001', 'urutan' => 2]);
        $this->statistik = new StatistikPenduduk;
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge([
            'nama_lengkap' => 'Warga '.fake()->unique()->numberBetween(1, 999999),
            'tanggal_lahir' => Carbon::today()->subYears(30),
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->krajan->id,
            'jenis_masuk' => JenisMasuk::PendataanAwal,
            'tanggal_masuk' => Carbon::today()->subYear(),
        ], $atribut));
    }

    // ------------------------------------------------------------------
    // Sebaran umur
    // ------------------------------------------------------------------

    public function test_sebaran_umur_mengelompokkan_sesuai_prd(): void
    {
        foreach ([3, 9, 15, 25, 45, 70] as $umur) {
            $this->warga(['tanggal_lahir' => Carbon::today()->subYears($umur)->subDays(5)]);
        }

        $hasil = collect($this->statistik->sebaranUmur())->keyBy('kunci');

        $this->assertSame(1, $hasil[KelompokUmur::BalitaAnak->value]['jumlah']);
        $this->assertSame(1, $hasil[KelompokUmur::Lansia->value]['jumlah']);
        $this->assertSame(6, collect($this->statistik->sebaranUmur())->sum('jumlah'));
    }

    public function test_kelompok_tidak_diketahui_disembunyikan_bila_kosong(): void
    {
        $this->warga();

        $kunci = collect($this->statistik->sebaranUmur())->pluck('kunci');

        $this->assertNotContains(KelompokUmur::TidakDiketahui->value, $kunci);
    }

    public function test_kelompok_tidak_diketahui_muncul_bila_ada_isinya(): void
    {
        $this->warga(['tanggal_lahir' => null]);

        $kunci = collect($this->statistik->sebaranUmur())->pluck('kunci');

        $this->assertContains(KelompokUmur::TidakDiketahui->value, $kunci);
    }

    /**
     * Batas kelompok dihitung terhadap hari ini, dan pergeseran satu hari
     * memindahkan seseorang ke kelompok lain. Inilah bagian yang paling mudah
     * meleset ketika perhitungannya disusun ulang menjadi satu kueri.
     */
    public function test_sebaran_umur_tepat_pada_batas_kelompok(): void
    {
        // Berulang tahun ke-19 tepat hari ini -> Dewasa Muda (19-35).
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(19)]);

        // Berulang tahun ke-19 besok, hari ini masih 18 -> Remaja (13-18).
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(19)->addDay()]);

        $hasil = collect($this->statistik->sebaranUmur())->keyBy('kunci');

        $this->assertSame(1, $hasil[KelompokUmur::DewasaMuda->value]['jumlah']);
        $this->assertSame(1, $hasil[KelompokUmur::Remaja->value]['jumlah']);
    }

    public function test_sebaran_umur_dihitung_dalam_satu_kueri(): void
    {
        foreach ([3, 9, 15, 25, 45, 70] as $umur) {
            $this->warga(['tanggal_lahir' => Carbon::today()->subYears($umur)->subDays(5)]);
        }
        $this->warga(['tanggal_lahir' => null]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->statistik->sebaranUmur();

        $jumlahKueri = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(
            1,
            $jumlahKueri,
            'Sebaran umur seharusnya satu kueri agregat, bukan satu COUNT per kelompok.',
        );
    }

    public function test_warga_terarsip_tidak_ikut_sebaran_umur(): void
    {
        $this->warga();
        $this->warga()->delete();

        $this->assertSame(1, collect($this->statistik->sebaranUmur())->sum('jumlah'));
    }

    // ------------------------------------------------------------------
    // Gender
    // ------------------------------------------------------------------

    public function test_rasio_gender_menghitung_jumlah_dan_persen(): void
    {
        foreach (range(1, 6) as $i) {
            $this->warga(['jenis_kelamin' => 'L']);
        }
        foreach (range(1, 4) as $i) {
            $this->warga(['jenis_kelamin' => 'P']);
        }

        $hasil = collect($this->statistik->rasioGender())->keyBy('kunci');

        $this->assertSame(6, $hasil['L']['jumlah']);
        $this->assertSame(60.0, $hasil['L']['persen']);
        $this->assertSame(40.0, $hasil['P']['persen']);
    }

    public function test_rasio_gender_tanpa_data_tidak_membagi_nol(): void
    {
        $hasil = collect($this->statistik->rasioGender());

        $this->assertSame(0.0, $hasil->sum('persen'));
        $this->assertSame(0, $hasil->sum('jumlah'));
    }

    // ------------------------------------------------------------------
    // Mata pencaharian
    // ------------------------------------------------------------------

    public function test_mata_pencaharian_diurutkan_dan_sisanya_digabung(): void
    {
        // 12 jenis pekerjaan; hanya 10 teratas ditampilkan, sisanya "Lainnya".
        foreach (range(1, 12) as $i) {
            $pekerjaan = RefPekerjaan::create(['nama' => "Pekerjaan {$i}"]);

            foreach (range(1, 13 - $i) as $j) {
                $this->warga(['pekerjaan_id' => $pekerjaan->id]);
            }
        }

        $hasil = collect($this->statistik->mataPencaharian());

        $this->assertSame('Pekerjaan 1', $hasil->first()['label']);
        $this->assertSame(12, $hasil->first()['jumlah']);
        $this->assertSame('Lainnya', $hasil->last()['label']);
        // Pekerjaan 11 (2 warga) + Pekerjaan 12 (1 warga)
        $this->assertSame(3, $hasil->last()['jumlah']);
    }

    public function test_warga_tanpa_pekerjaan_tampil_terpisah(): void
    {
        $petani = RefPekerjaan::create(['nama' => 'Petani']);
        $this->warga(['pekerjaan_id' => $petani->id]);
        $this->warga(['pekerjaan_id' => null]);

        $hasil = collect($this->statistik->mataPencaharian())->keyBy('label');

        // Kekosongan data harus terlihat, bukan tersembunyi.
        $this->assertSame(1, $hasil['Belum diisi']['jumlah']);
    }

    // ------------------------------------------------------------------
    // Piramida umur x gender
    // ------------------------------------------------------------------

    public function test_piramida_mengelompokkan_per_lima_tahun_dan_gender(): void
    {
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(3), 'jenis_kelamin' => 'L']);
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(3), 'jenis_kelamin' => 'P']);
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(3), 'jenis_kelamin' => 'P']);
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(101), 'jenis_kelamin' => 'L']);

        $hasil = collect($this->statistik->piramidaUmurGender())->keyBy('label');

        $this->assertSame(1, $hasil['0–4']['laki']);
        $this->assertSame(2, $hasil['0–4']['perempuan']);
        $this->assertSame(1, $hasil['100+']['laki']);
        $this->assertSame(0, $hasil['100+']['perempuan']);
    }

    /**
     * Batas pita dihitung terhadap hari ini, persis seperti sebaranUmur():
     * berulang tahun ke-5 tepat hari ini harus pindah ke pita "5–9", bukan
     * tertinggal di "0–4".
     */
    public function test_piramida_tepat_pada_batas_pita(): void
    {
        // Berulang tahun ke-5 tepat hari ini -> pita 5–9.
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(5), 'jenis_kelamin' => 'L']);

        // Berulang tahun ke-5 besok, hari ini masih 4 -> pita 0–4.
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(5)->addDay(), 'jenis_kelamin' => 'P']);

        $hasil = collect($this->statistik->piramidaUmurGender())->keyBy('label');

        $this->assertSame(1, $hasil['5–9']['laki']);
        $this->assertSame(0, $hasil['5–9']['perempuan']);
        $this->assertSame(0, $hasil['0–4']['laki']);
        $this->assertSame(1, $hasil['0–4']['perempuan']);
    }

    public function test_piramida_dihitung_dalam_satu_kueri(): void
    {
        $this->warga(['tanggal_lahir' => Carbon::today()->subYears(30)]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->statistik->piramidaUmurGender();

        $jumlahKueri = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(1, $jumlahKueri);
    }

    public function test_piramida_punya_21_kelompok(): void
    {
        $this->assertCount(21, $this->statistik->piramidaUmurGender());
    }

    public function test_piramida_mengabaikan_warga_tanpa_tanggal_lahir(): void
    {
        $this->warga(['tanggal_lahir' => null]);

        $total = collect($this->statistik->piramidaUmurGender())->sum(fn ($b) => $b['laki'] + $b['perempuan']);

        $this->assertSame(0, $total);
    }

    // ------------------------------------------------------------------
    // Agama
    // ------------------------------------------------------------------

    public function test_agama_mengelompokkan_dan_memisahkan_belum_diisi(): void
    {
        $islam = RefAgama::create(['nama' => 'Islam']);
        $kristen = RefAgama::create(['nama' => 'Kristen']);

        $this->warga(['agama_id' => $islam->id]);
        $this->warga(['agama_id' => $islam->id]);
        $this->warga(['agama_id' => $kristen->id]);
        $this->warga(['agama_id' => null]);

        $hasil = collect($this->statistik->agama())->keyBy('label');

        $this->assertSame(2, $hasil['Islam']['jumlah']);
        $this->assertSame(1, $hasil['Kristen']['jumlah']);
        $this->assertSame(1, $hasil['Belum diisi']['jumlah']);
    }

    public function test_tidak_ingin_menyebutkan_terpisah_dari_belum_diisi(): void
    {
        $rahasia = RefAgama::create(['nama' => 'Tidak Ingin Menyebutkan']);

        $this->warga(['agama_id' => $rahasia->id]);
        $this->warga(['agama_id' => null]);

        $hasil = collect($this->statistik->agama())->keyBy('label');

        $this->assertSame(1, $hasil['Tidak Ingin Menyebutkan']['jumlah']);
        $this->assertSame(1, $hasil['Belum diisi']['jumlah']);
    }

    // ------------------------------------------------------------------
    // Status perkawinan
    // ------------------------------------------------------------------

    public function test_status_perkawinan_mengelompokkan_enam_kategori(): void
    {
        $this->warga(['status_perkawinan' => 'belum_kawin']);
        $this->warga(['status_perkawinan' => 'kawin_tercatat']);
        $this->warga(['status_perkawinan' => 'kawin_tercatat']);
        $this->warga(['status_perkawinan' => null]);

        $hasil = collect($this->statistik->statusPerkawinan())->keyBy('label');

        $this->assertSame(1, $hasil['Belum Kawin']['jumlah']);
        $this->assertSame(2, $hasil['Kawin Tercatat']['jumlah']);
        $this->assertSame(1, $hasil['Belum diisi']['jumlah']);
    }

    // ------------------------------------------------------------------
    // Sebaran pekerjaan (lengkap, publik)
    // ------------------------------------------------------------------

    public function test_sebaran_pekerjaan_tidak_dibatasi_sepuluh(): void
    {
        foreach (range(1, 12) as $i) {
            $pekerjaan = RefPekerjaan::create(['nama' => "Pekerjaan {$i}"]);
            $this->warga(['pekerjaan_id' => $pekerjaan->id]);
        }

        $hasil = collect($this->statistik->sebaranPekerjaan());

        $this->assertCount(12, $hasil);
        $this->assertFalse($hasil->contains('label', 'Lainnya'));
    }

    public function test_sebaran_pekerjaan_diurutkan_terbanyak(): void
    {
        $sedikit = RefPekerjaan::create(['nama' => 'Sedikit']);
        $banyak = RefPekerjaan::create(['nama' => 'Banyak']);

        $this->warga(['pekerjaan_id' => $sedikit->id]);
        $this->warga(['pekerjaan_id' => $banyak->id]);
        $this->warga(['pekerjaan_id' => $banyak->id]);
        $this->warga(['pekerjaan_id' => $banyak->id]);

        $hasil = collect($this->statistik->sebaranPekerjaan());

        $this->assertSame('Banyak', $hasil->first()['label']);
    }

    // ------------------------------------------------------------------
    // Sebaran wilayah
    // ------------------------------------------------------------------

    public function test_sebaran_wilayah_menggabungkan_warga_dan_kk(): void
    {
        foreach (range(1, 8) as $i) {
            $this->warga(['wilayah_id' => $this->krajan->id]);
        }
        foreach (range(1, 4) as $i) {
            $this->warga(['wilayah_id' => $this->sukasari->id]);
        }

        RekapKk::create(['wilayah_id' => $this->krajan->id, 'tahun' => now()->year, 'jumlah_kk' => 2]);

        $hasil = collect($this->statistik->sebaranWilayah('dusun'))->keyBy('label');

        $this->assertSame(8, $hasil['Krajan']['jumlah_warga']);
        $this->assertSame(2, $hasil['Krajan']['jumlah_kk']);
        $this->assertSame(4.0, $hasil['Krajan']['jiwa_per_kk']);

        // Wilayah tanpa rekap KK tidak menampilkan rata-rata palsu.
        $this->assertSame(4, $hasil['Sukasari']['jumlah_warga']);
        $this->assertNull($hasil['Sukasari']['jiwa_per_kk']);
    }

    public function test_wilayah_tanpa_warga_tetap_tampil_dengan_nol(): void
    {
        $this->warga(['wilayah_id' => $this->krajan->id]);

        $hasil = collect($this->statistik->sebaranWilayah('dusun'))->keyBy('label');

        // RT kosong adalah informasi, bukan ketiadaan informasi.
        $this->assertArrayHasKey('Sukasari', $hasil);
        $this->assertSame(0, $hasil['Sukasari']['jumlah_warga']);
    }

    public function test_sebaran_wilayah_bisa_diurutkan_dua_arah(): void
    {
        foreach (range(1, 5) as $i) {
            $this->warga(['wilayah_id' => $this->sukasari->id]);
        }
        $this->warga(['wilayah_id' => $this->krajan->id]);

        $this->assertSame('Sukasari', $this->statistik->sebaranWilayah('dusun', 'desc')[0]['label']);
        $this->assertSame('Krajan', $this->statistik->sebaranWilayah('dusun', 'asc')[0]['label']);
    }

    public function test_sebaran_wilayah_bisa_dirinci_sampai_rt(): void
    {
        $this->warga(['wilayah_id' => $this->krajan->id]);

        $label = collect($this->statistik->sebaranWilayah('rt'))->pluck('label');

        $this->assertContains('Krajan — RT 001/RW 001', $label);
    }

    // ------------------------------------------------------------------
    // Tren mutasi
    // ------------------------------------------------------------------

    public function test_tren_menghitung_kelahiran_dan_kematian_per_tahun(): void
    {
        $tahunIni = now()->year;

        foreach (range(1, 3) as $i) {
            $this->warga([
                'tanggal_lahir' => Carbon::create($tahunIni, 3, 1),
                'jenis_masuk' => JenisMasuk::Lahir,
                'tanggal_masuk' => Carbon::create($tahunIni, 3, 1),
            ]);
        }

        $mati = $this->warga();
        $mati->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => Carbon::create($tahunIni, 6, 1)])->save();
        $mati->delete();

        $tren = collect($this->statistik->trenMutasi(5))->keyBy('tahun');

        $this->assertSame(3, $tren[$tahunIni]['kelahiran']);
        $this->assertSame(1, $tren[$tahunIni]['kematian']);
    }

    public function test_tahun_tanpa_kejadian_tetap_tampil_dengan_nol(): void
    {
        $tren = collect($this->statistik->trenMutasi(5));

        // Garis grafik yang melompati tahun terbaca seolah tidak ada data.
        $this->assertCount(5, $tren);
        $this->assertSame(now()->year - 4, $tren->first()['tahun']);
        $this->assertSame(now()->year, $tren->last()['tahun']);
        $this->assertSame(0, $tren->sum('kelahiran'));
    }

    public function test_arsip_duplikat_tidak_muncul_di_tren(): void
    {
        $duplikat = $this->warga();
        $duplikat->fill(['alasan_keluar' => AlasanKeluar::Duplikat, 'tanggal_keluar' => now()])->save();
        $duplikat->delete();

        $tren = collect($this->statistik->trenMutasi(5))->keyBy('tahun');

        // Pembersihan data ganda bukan peristiwa kependudukan.
        $this->assertSame(0, $tren[now()->year]['kematian']);
        $this->assertSame(0, $tren[now()->year]['pindah_keluar']);
    }

    public function test_pindah_masuk_dan_keluar_terpisah_dari_kelahiran_kematian(): void
    {
        $tahunIni = now()->year;

        $this->warga(['jenis_masuk' => JenisMasuk::PindahMasuk, 'tanggal_masuk' => Carbon::create($tahunIni, 2, 1)]);

        $pindah = $this->warga();
        $pindah->fill(['alasan_keluar' => AlasanKeluar::PindahKeluar, 'tanggal_keluar' => Carbon::create($tahunIni, 5, 1)])->save();
        $pindah->delete();

        $tren = collect($this->statistik->trenMutasi(5))->keyBy('tahun');

        $this->assertSame(1, $tren[$tahunIni]['pindah_masuk']);
        $this->assertSame(1, $tren[$tahunIni]['pindah_keluar']);
        $this->assertSame(0, $tren[$tahunIni]['kelahiran']);
        $this->assertSame(0, $tren[$tahunIni]['kematian']);
    }

    // ------------------------------------------------------------------
    // Endpoint
    // ------------------------------------------------------------------

    public function test_seluruh_endpoint_statistik_menolak_tamu(): void
    {
        foreach (['umur', 'gender', 'pekerjaan', 'pendidikan', 'wilayah', 'tren'] as $nama) {
            $this->get(route("admin.statistik.{$nama}"))->assertRedirect(route('admin.login'));
        }
    }

    public function test_endpoint_mengembalikan_json_yang_benar(): void
    {
        $this->warga();

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.statistik.umur'))
            ->assertOk()
            ->assertJsonStructure(['data' => [['kunci', 'label', 'rentang', 'jumlah', 'persen']], 'total']);

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.statistik.wilayah', ['tingkat' => 'rt']))
            ->assertOk()
            ->assertJsonPath('tingkat', 'rt');

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.statistik.tren', ['tahun' => 10]))
            ->assertOk()
            ->assertJsonPath('jumlah_tahun', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_parameter_ngawur_dikembalikan_ke_nilai_aman(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.statistik.wilayah', ['tingkat' => 'provinsi']))
            ->assertJsonPath('tingkat', 'dusun');

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.statistik.tren', ['tahun' => 9999]))
            ->assertJsonPath('jumlah_tahun', 15);
    }

    public function test_dashboard_memuat_seluruh_kanvas_grafik(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('grafikUmur')
            ->assertSee('grafikGender')
            ->assertSee('grafikPekerjaan')
            ->assertSee('grafikTren')
            ->assertSee('Sebaran Per Wilayah');
    }
}
