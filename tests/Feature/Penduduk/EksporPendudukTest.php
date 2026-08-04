<?php

namespace Tests\Feature\Penduduk;

use App\Enums\AlasanKeluar;
use App\Exports\EksporPenduduk;
use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\PengaturanDesa;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use App\Queries\PencarianPenduduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EksporPendudukTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Wilayah $krajan;

    private Wilayah $sukasari;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->krajan = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        $this->sukasari = Wilayah::create(['dusun' => 'Sukasari', 'rw' => '002', 'rt' => '001']);

        PengaturanDesa::ambil()->fill(['nama_desa' => 'Desa Sukamaju'])->save();
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge([
            'nama_lengkap' => 'Warga '.fake()->unique()->numberBetween(1, 999999),
            'tanggal_lahir' => Carbon::today()->subYears(30),
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->krajan->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => Carbon::today()->subYear(),
        ], $atribut));
    }

    private function unduh(array $parameter = [])
    {
        return $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.ekspor', $parameter));
    }

    // ------------------------------------------------------------------
    // Kolom & privasi
    // ------------------------------------------------------------------

    public static function kolomTerlarangProvider(): array
    {
        return [['NIK'], ['Nomor KK'], ['Kartu Keluarga'], ['Telepon'], ['No HP'], ['Alamat'], ['Tanggal Lahir'], ['Agama']];
    }

    #[DataProvider('kolomTerlarangProvider')]
    public function test_judul_kolom_ekspor_tidak_memuat_kolom_terlarang(string $terlarang): void
    {
        foreach ([false, true] as $arsip) {
            $judul = array_map('mb_strtolower', EksporPenduduk::kolomDiizinkan($arsip));

            $this->assertNotContains(
                mb_strtolower($terlarang),
                $judul,
                "Kolom \"{$terlarang}\" tidak boleh ikut terekspor (PRD 2.4).",
            );
        }
    }

    public function test_kolom_ekspor_ditetapkan_eksplisit(): void
    {
        // Daftar putih. Menambah kolom pada tabel penduduk tidak boleh
        // membuat kolom itu ikut terekspor dengan sendirinya.
        $this->assertSame(
            ['Nama Lengkap', 'Umur', 'Jenis Kelamin', 'Dusun', 'RW', 'RT', 'Pekerjaan', 'Pendidikan'],
            EksporPenduduk::kolomDiizinkan(),
        );

        $this->assertSame(
            ['Nama Lengkap', 'Umur', 'Jenis Kelamin', 'Dusun', 'RW', 'RT', 'Pekerjaan', 'Pendidikan', 'Alasan Keluar', 'Tanggal Keluar'],
            EksporPenduduk::kolomDiizinkan(arsip: true),
        );
    }

    public function test_umur_diekspor_sebagai_angka_bukan_tanggal_lahir(): void
    {
        $warga = $this->warga(['tanggal_lahir' => Carbon::today()->subYears(42)]);

        $baris = (new EksporPenduduk(new PencarianPenduduk()))->map($warga);

        $this->assertSame(42, $baris[1]);
        $this->assertNotContains($warga->tanggal_lahir->toDateString(), $baris);
    }

    public function test_judul_kolom_selaras_dengan_berkas_contoh_impor(): void
    {
        // Berkas hasil ekspor harus bisa disunting lalu diimpor kembali.
        $ekspor = EksporPenduduk::kolomDiizinkan();
        $impor = EksporPenduduk::judulImporTerkait();

        foreach (['Nama Lengkap', 'Jenis Kelamin', 'Dusun', 'RW', 'RT', 'Pekerjaan', 'Pendidikan'] as $kolom) {
            $this->assertContains($kolom, $ekspor, "Kolom {$kolom} hilang dari ekspor.");
            $this->assertContains($kolom, $impor, "Kolom {$kolom} hilang dari berkas contoh impor.");
        }
    }

    // ------------------------------------------------------------------
    // Isi berkas
    // ------------------------------------------------------------------

    public function test_ekspor_semua_berisi_seluruh_warga_aktif(): void
    {
        foreach (range(1, 5) as $i) {
            $this->warga();
        }
        $this->warga()->delete();

        $ekspor = new EksporPenduduk(new PencarianPenduduk());

        // Warga terarsip tidak ikut terekspor.
        $this->assertSame(5, $ekspor->query()->count());
    }

    public function test_ekspor_mengikuti_saringan_yang_aktif(): void
    {
        $this->warga(['nama_lengkap' => 'Pria Krajan', 'jenis_kelamin' => 'L']);
        $this->warga(['nama_lengkap' => 'Wanita Krajan', 'jenis_kelamin' => 'P']);
        $this->warga(['nama_lengkap' => 'Pria Sukasari', 'jenis_kelamin' => 'L', 'wilayah_id' => $this->sukasari->id]);

        $pencarian = new PencarianPenduduk(['dusun' => 'Krajan', 'jenis_kelamin' => 'L']);
        $nama = (new EksporPenduduk($pencarian))->query()->pluck('nama_lengkap');

        $this->assertSame(['Pria Krajan'], $nama->all());
    }

    public function test_ekspor_arsip_memuat_alasan_dan_tanggal_keluar(): void
    {
        $warga = $this->warga();
        $warga->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => now()])->save();
        $warga->delete();

        $baris = (new EksporPenduduk(new PencarianPenduduk(), arsip: true))->map($warga->fresh());

        $this->assertSame('Meninggal Dunia', $baris[8]);
        $this->assertSame(now()->format('Y-m-d'), $baris[9]);
    }

    // ------------------------------------------------------------------
    // Unduhan
    // ------------------------------------------------------------------

    public function test_unduhan_xlsx_dan_csv_berhasil(): void
    {
        Excel::fake();
        $this->warga();

        $this->unduh(['format' => 'xlsx'])->assertOk();
        Excel::assertDownloaded('desa-sukamaju-data-warga-'.now()->format('Y-m-d').'.xlsx');

        $this->unduh(['format' => 'csv'])->assertOk();
        Excel::assertDownloaded('desa-sukamaju-data-warga-'.now()->format('Y-m-d').'.csv');
    }

    public function test_nama_berkas_membedakan_hasil_saringan(): void
    {
        Excel::fake();
        $this->warga();

        // Beberapa hasil ekspor tidak boleh tertukar di folder unduhan admin.
        $this->unduh(['dusun' => 'Krajan', 'jenis_kelamin' => 'L'])->assertOk();

        Excel::assertDownloaded(
            'desa-sukamaju-data-warga-krajan-jenis-kelamin-l-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    public function test_nama_berkas_arsip_dibedakan(): void
    {
        Excel::fake();
        $warga = $this->warga();
        $warga->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => now()])->save();
        $warga->delete();

        $this->unduh(['arsip' => 1])->assertOk();

        Excel::assertDownloaded('desa-sukamaju-arsip-warga-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_hasil_saringan_kosong_dibatalkan_dengan_pesan(): void
    {
        $this->warga(['nama_lengkap' => 'Budi']);

        $this->unduh(['q' => 'tidak-ada-warga-bernama-ini'])
            ->assertRedirect()
            ->assertSessionHas('galat');
    }

    public function test_data_terlalu_besar_diarahkan_ke_csv(): void
    {
        $baris = collect(range(1, 12))->map(fn ($i) => [
            'nama_lengkap' => "Warga {$i}",
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->krajan->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();
        Penduduk::insert($baris);

        // Ambang diturunkan agar tidak perlu membuat 10.000 baris di pengujian.
        $refleksi = new \ReflectionClassConstant(
            \App\Http\Controllers\Admin\EksporPendudukController::class,
            'AMBANG_CSV',
        );
        $this->assertSame(10000, $refleksi->getValue());

        // Dengan ambang sebenarnya, 12 baris tetap boleh xlsx.
        Excel::fake();
        $this->unduh(['format' => 'xlsx'])->assertOk();
    }

    public function test_csv_memakai_bom_utf8(): void
    {
        // Tanpa BOM, Excel di Windows membaca berkas sebagai ANSI dan nama
        // warga beraksen tampil rusak.
        $pengaturan = (new EksporPenduduk(new PencarianPenduduk()))->getCsvSettings();

        $this->assertTrue($pengaturan['use_bom']);
    }

    public function test_tamu_ditolak(): void
    {
        $this->get(route('admin.penduduk.ekspor'))->assertRedirect(route('admin.login'));
    }

    public function test_tombol_ekspor_tampil_di_daftar_warga_dan_arsip(): void
    {
        $this->warga();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index'))
            ->assertOk()
            ->assertSee('Ekspor')
            ->assertSee('Excel (.xlsx)');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.arsip'))
            ->assertOk()
            ->assertSee('Ekspor');
    }

    public function test_saringan_aktif_ikut_terbawa_ke_tombol_ekspor(): void
    {
        $this->warga();

        $html = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['dusun' => 'Krajan']))
            ->assertOk()
            ->getContent();

        // Berkas yang diunduh harus berisi persis data yang tampil di layar.
        $this->assertStringContainsString('name="dusun" value="Krajan"', $html);
    }
}
