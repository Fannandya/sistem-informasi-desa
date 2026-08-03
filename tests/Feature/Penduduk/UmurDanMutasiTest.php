<?php

namespace Tests\Feature\Penduduk;

use App\Enums\AlasanKeluar;
use App\Enums\JenisMasuk;
use App\Enums\KelompokUmur;
use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Inti kebenaran statistik desa: umur yang dihitung, bukan disimpan; dan
 * kelahiran/kematian yang berasal dari kolom mutasi, bukan dari penambahan
 * atau penghapusan baris (PRD 2.7 & 2.8).
 */
class UmurDanMutasiTest extends TestCase
{
    use RefreshDatabase;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge([
            'nama_lengkap' => 'Warga Uji',
            'tanggal_lahir' => Carbon::today()->subYears(30),
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'jenis_masuk' => JenisMasuk::PendataanAwal,
            'tanggal_masuk' => Carbon::today()->subYear(),
        ], $atribut));
    }

    // ------------------------------------------------------------------
    // Umur
    // ------------------------------------------------------------------

    public function test_umur_dihitung_dari_tanggal_lahir(): void
    {
        $warga = $this->warga(['tanggal_lahir' => Carbon::today()->subYears(42)->subDays(3)]);

        $this->assertSame(42, $warga->umur());
    }

    public function test_umur_belum_bertambah_sehari_sebelum_ulang_tahun(): void
    {
        $warga = $this->warga(['tanggal_lahir' => Carbon::today()->subYears(30)->addDay()]);

        $this->assertSame(29, $warga->umur());
    }

    public function test_umur_ikut_berubah_seiring_waktu(): void
    {
        // Inilah alasan umur tidak disimpan: angka yang disimpan akan basi
        // tanpa ada yang menyadarinya.
        $warga = $this->warga(['tanggal_lahir' => Carbon::today()->subYears(30)]);
        $this->assertSame(30, $warga->umur());

        Carbon::setTestNow(Carbon::today()->addYears(5));
        $this->assertSame(35, $warga->fresh()->umur());

        Carbon::setTestNow();
    }

    public function test_warga_tanpa_tanggal_lahir_tidak_punya_umur(): void
    {
        $warga = $this->warga(['tanggal_lahir' => null]);

        $this->assertNull($warga->umur());
        $this->assertSame('Tidak diketahui', $warga->umurTerbaca());
        $this->assertSame(KelompokUmur::TidakDiketahui, $warga->kelompokUmur());
    }

    // ------------------------------------------------------------------
    // Kelompok umur
    // ------------------------------------------------------------------

    public static function kelompokProvider(): array
    {
        return [
            [0, KelompokUmur::BalitaAnak],
            [5, KelompokUmur::BalitaAnak],
            [6, KelompokUmur::AnakSekolah],
            [12, KelompokUmur::AnakSekolah],
            [13, KelompokUmur::Remaja],
            [18, KelompokUmur::Remaja],
            [19, KelompokUmur::DewasaMuda],
            [35, KelompokUmur::DewasaMuda],
            [36, KelompokUmur::Dewasa],
            [60, KelompokUmur::Dewasa],
            [61, KelompokUmur::Lansia],
            [102, KelompokUmur::Lansia],
        ];
    }

    #[DataProvider('kelompokProvider')]
    public function test_batas_kelompok_umur_tepat(int $umur, KelompokUmur $harapan): void
    {
        $this->assertSame($harapan, KelompokUmur::dariUmur($umur));
    }

    public function test_saringan_kelompok_umur_konsisten_dengan_perhitungan(): void
    {
        // Setiap kelompok harus menangkap warganya sendiri dan tidak lebih.
        foreach ([3, 9, 15, 25, 45, 70] as $umur) {
            $this->warga([
                'nama_lengkap' => "Warga {$umur}",
                'tanggal_lahir' => Carbon::today()->subYears($umur)->subDays(10),
            ]);
        }
        $this->warga(['nama_lengkap' => 'Tanpa Tanggal', 'tanggal_lahir' => null]);

        foreach (KelompokUmur::cases() as $kelompok) {
            $lewatQuery = Penduduk::query()->diKelompokUmur($kelompok)->pluck('nama_lengkap')->sort()->values();

            $lewatPhp = Penduduk::all()
                ->filter(fn (Penduduk $p) => $p->kelompokUmur() === $kelompok)
                ->pluck('nama_lengkap')->sort()->values();

            $this->assertEquals(
                $lewatPhp->all(),
                $lewatQuery->all(),
                "Saringan SQL dan perhitungan PHP berbeda untuk kelompok {$kelompok->value}.",
            );
        }
    }

    // ------------------------------------------------------------------
    // Kelahiran & kematian
    // ------------------------------------------------------------------

    public function test_pendatang_tidak_dihitung_sebagai_kelahiran(): void
    {
        $tahun = now()->year;

        $this->warga(['jenis_masuk' => JenisMasuk::Lahir, 'tanggal_masuk' => now(), 'tanggal_lahir' => now()]);
        $this->warga(['jenis_masuk' => JenisMasuk::PindahMasuk, 'tanggal_masuk' => now()]);
        $this->warga(['jenis_masuk' => JenisMasuk::PendataanAwal, 'tanggal_masuk' => now()]);

        // Tiga baris ditambahkan, tapi hanya satu yang benar-benar kelahiran.
        $this->assertSame(3, Penduduk::count());
        $this->assertSame(1, Penduduk::query()->lahirPadaTahun($tahun)->count());
    }

    public function test_hanya_alasan_meninggal_yang_dihitung_sebagai_kematian(): void
    {
        $tahun = now()->year;

        foreach (AlasanKeluar::cases() as $alasan) {
            $warga = $this->warga(['nama_lengkap' => $alasan->value]);
            $warga->fill(['alasan_keluar' => $alasan, 'tanggal_keluar' => now()])->save();
            $warga->delete();
        }

        // Tiga baris diarsipkan, tapi hanya satu yang kematian.
        $this->assertSame(3, Penduduk::onlyTrashed()->count());
        $this->assertSame(1, Penduduk::query()->meninggalPadaTahun($tahun)->count());
    }

    public function test_bayi_yang_lahir_lalu_meninggal_tetap_terhitung_kelahiran(): void
    {
        $tahun = now()->year;

        $bayi = $this->warga([
            'tanggal_lahir' => now()->subMonths(2),
            'jenis_masuk' => JenisMasuk::Lahir,
            'tanggal_masuk' => now()->subMonths(2),
        ]);
        $bayi->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => now()])->save();
        $bayi->delete();

        $this->assertSame(1, Penduduk::query()->lahirPadaTahun($tahun)->count());
        $this->assertSame(1, Penduduk::query()->meninggalPadaTahun($tahun)->count());
    }

    public function test_kelahiran_dihitung_per_tahun_kejadian(): void
    {
        foreach ([now()->year - 2, now()->year - 1, now()->year] as $tahun) {
            $this->warga([
                'tanggal_lahir' => Carbon::create($tahun, 3, 10),
                'jenis_masuk' => JenisMasuk::Lahir,
                'tanggal_masuk' => Carbon::create($tahun, 3, 10),
            ]);
        }

        $this->assertSame(1, Penduduk::query()->lahirPadaTahun(now()->year)->count());
        $this->assertSame(1, Penduduk::query()->lahirPadaTahun(now()->year - 1)->count());
        $this->assertSame(0, Penduduk::query()->lahirPadaTahun(now()->year - 5)->count());
    }

    public function test_warga_terarsip_tidak_terhitung_sebagai_warga_aktif(): void
    {
        $this->warga();
        $terarsip = $this->warga(['nama_lengkap' => 'Sudah Keluar']);
        $terarsip->delete();

        $this->assertSame(1, Penduduk::query()->aktif()->count());
        $this->assertSame(1, Penduduk::query()->terarsip()->count());
        $this->assertSame(2, Penduduk::withTrashed()->count());
    }
}
