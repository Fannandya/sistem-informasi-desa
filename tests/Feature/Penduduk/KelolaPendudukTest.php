<?php

namespace Tests\Feature\Penduduk;

use App\Enums\AlasanKeluar;
use App\Enums\JenisMasuk;
use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KelolaPendudukTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Wilayah $wilayah;

    private RefPekerjaan $pekerjaan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        $this->pekerjaan = RefPekerjaan::create(['nama' => 'Petani']);
    }

    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1990-05-17',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'pekerjaan_id' => $this->pekerjaan->id,
            'pendidikan' => 'sma',
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
        ], $ganti);
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge($this->dataValid(), $atribut));
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_ditolak_di_seluruh_halaman_penduduk(): void
    {
        $warga = $this->warga();

        $this->get(route('admin.penduduk.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.penduduk.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.penduduk.edit', $warga))->assertRedirect(route('admin.login'));
        $this->get(route('admin.penduduk.arsip'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.penduduk.store'), [])->assertRedirect(route('admin.login'));
    }

    // ------------------------------------------------------------------
    // Tambah & ubah
    // ------------------------------------------------------------------

    public function test_warga_baru_tersimpan(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid())
            ->assertRedirect(route('admin.penduduk.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('penduduk', [
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'pendidikan' => 'sma',
        ]);
    }

    public function test_kolom_wajib_divalidasi(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), [])
            ->assertSessionHasErrors([
                'nama_lengkap', 'tanggal_lahir', 'jenis_kelamin',
                'wilayah_id', 'jenis_masuk', 'tanggal_masuk',
            ]);

        $this->assertDatabaseCount('penduduk', 0);
    }

    public function test_tanggal_lahir_di_masa_depan_ditolak(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid([
                'tanggal_lahir' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('tanggal_lahir');
    }

    public function test_tanggal_masuk_sebelum_tanggal_lahir_ditolak(): void
    {
        // Seseorang tidak bisa tercatat masuk sebelum ia lahir.
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid([
                'tanggal_lahir' => '2020-01-01',
                'tanggal_masuk' => '2019-01-01',
            ]))
            ->assertSessionHasErrors('tanggal_masuk');
    }

    public function test_wilayah_tidak_terdaftar_ditolak(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid(['wilayah_id' => 9999]))
            ->assertSessionHasErrors('wilayah_id');
    }

    public function test_data_warga_bisa_diubah(): void
    {
        $warga = $this->warga();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.penduduk.update', $warga), $this->dataValid([
                'nama_lengkap' => 'Budi Santoso Wijaya',
                'pendidikan' => 's1',
            ]))
            ->assertRedirect(route('admin.penduduk.index'));

        $this->assertSame('Budi Santoso Wijaya', $warga->fresh()->nama_lengkap);
    }

    public function test_data_terarsip_tidak_bisa_diubah(): void
    {
        $warga = $this->warga();
        $warga->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => now()])->save();
        $warga->delete();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.edit', $warga->id))
            ->assertForbidden();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.penduduk.update', $warga->id), $this->dataValid(['nama_lengkap' => 'Diubah']))
            ->assertForbidden();

        $this->assertSame('Budi Santoso', $warga->fresh()->nama_lengkap);
    }

    // ------------------------------------------------------------------
    // Arsip & pemulihan
    // ------------------------------------------------------------------

    public function test_mengarsipkan_warga_mencatat_alasan_dan_tanggal(): void
    {
        $warga = $this->warga();

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.penduduk.arsipkan', $warga), [
                'alasan_keluar' => 'meninggal',
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.penduduk.index'));

        $warga = $warga->fresh();

        $this->assertTrue($warga->trashed());
        $this->assertSame(AlasanKeluar::Meninggal, $warga->alasan_keluar);
        $this->assertNotNull($warga->tanggal_keluar);
    }

    public function test_arsip_tanpa_alasan_ditolak(): void
    {
        $warga = $this->warga();

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.penduduk.arsipkan', $warga), [
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('alasan_keluar');

        $this->assertFalse($warga->fresh()->trashed());
    }

    public function test_tanggal_keluar_sebelum_tanggal_masuk_ditolak(): void
    {
        $warga = $this->warga(['tanggal_masuk' => now()->subYear()->toDateString()]);

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.penduduk.arsipkan', $warga), [
                'alasan_keluar' => 'pindah_keluar',
                'tanggal_keluar' => now()->subYears(3)->toDateString(),
            ])
            ->assertSessionHasErrors('tanggal_keluar');

        $this->assertFalse($warga->fresh()->trashed());
    }

    public function test_warga_terarsip_hilang_dari_daftar_aktif(): void
    {
        $warga = $this->warga(['nama_lengkap' => 'Sudah Keluar']);
        $warga->fill(['alasan_keluar' => AlasanKeluar::PindahKeluar, 'tanggal_keluar' => now()])->save();
        $warga->delete();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index'))
            ->assertOk()
            ->assertDontSee('Sudah Keluar');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.arsip'))
            ->assertOk()
            ->assertSee('Sudah Keluar');
    }

    public function test_pemulihan_mengembalikan_warga_dan_menghapus_data_keluar(): void
    {
        $warga = $this->warga();
        $warga->fill(['alasan_keluar' => AlasanKeluar::Meninggal, 'tanggal_keluar' => now()])->save();
        $warga->delete();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.pulihkan', $warga->id))
            ->assertRedirect(route('admin.penduduk.arsip'));

        $warga = $warga->fresh();

        $this->assertFalse($warga->trashed());
        // Kalau data keluar tidak dibersihkan, warga yang dipulihkan tetap
        // terhitung pada statistik kematian tahun berjalan.
        $this->assertNull($warga->alasan_keluar);
        $this->assertNull($warga->tanggal_keluar);
        $this->assertSame(0, Penduduk::query()->meninggalPadaTahun(now()->year)->count());
    }

    // ------------------------------------------------------------------
    // Pencarian & saringan
    // ------------------------------------------------------------------

    public function test_pencarian_nama_mencocokkan_sebagian_kata(): void
    {
        $this->warga(['nama_lengkap' => 'Siti Nurhayati']);
        $this->warga(['nama_lengkap' => 'Budi Santoso']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['q' => 'nurhay']))
            ->assertOk()
            ->assertSee('Siti Nurhayati')
            ->assertDontSee('Budi Santoso');
    }

    public function test_saringan_wilayah_dan_gender_bisa_digabung(): void
    {
        $lain = Wilayah::create(['dusun' => 'Sukasari', 'rw' => '002', 'rt' => '001']);

        $this->warga(['nama_lengkap' => 'Pria Krajan', 'jenis_kelamin' => 'L']);
        $this->warga(['nama_lengkap' => 'Wanita Krajan', 'jenis_kelamin' => 'P']);
        $this->warga(['nama_lengkap' => 'Pria Sukasari', 'jenis_kelamin' => 'L', 'wilayah_id' => $lain->id]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['dusun' => 'Krajan', 'jenis_kelamin' => 'L']))
            ->assertOk()
            ->assertSee('Pria Krajan')
            ->assertDontSee('Wanita Krajan')
            ->assertDontSee('Pria Sukasari');
    }

    public function test_saringan_kelompok_umur_bekerja(): void
    {
        $this->warga(['nama_lengkap' => 'Balita Satu', 'tanggal_lahir' => Carbon::today()->subYears(3)]);
        $this->warga(['nama_lengkap' => 'Lansia Satu', 'tanggal_lahir' => Carbon::today()->subYears(70)]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['kelompok_umur' => 'lansia']))
            ->assertOk()
            ->assertSee('Lansia Satu')
            ->assertDontSee('Balita Satu');
    }

    public function test_saringan_rt_menerima_nomor_tanpa_nol_di_depan(): void
    {
        // Admin akan mengetik "1", bukan "001".
        $this->warga(['nama_lengkap' => 'Warga RT Satu']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['rt' => '1']))
            ->assertOk()
            ->assertSee('Warga RT Satu');
    }

    public function test_saringan_bertahan_di_url_saat_pindah_halaman(): void
    {
        Penduduk::insert(collect(range(1, 60))->map(fn ($i) => [
            'nama_lengkap' => "Warga Ke-{$i}",
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $html = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index', ['jenis_kelamin' => 'L']))
            ->assertOk()
            ->getContent();

        // Tautan pagination wajib membawa serta saringan yang aktif.
        $this->assertStringContainsString('jenis_kelamin=L', $html);
        $this->assertStringContainsString('page=2', $html);
    }

    public function test_daftar_dibatasi_lima_puluh_baris_per_halaman(): void
    {
        Penduduk::insert(collect(range(1, 55))->map(fn ($i) => [
            'nama_lengkap' => sprintf('Warga %03d', $i),
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.index'))
            ->assertOk();

        $this->assertCount(50, $response->viewData('daftar')->items());
        $this->assertSame(55, $response->viewData('daftar')->total());
    }

    public function test_agama_dan_status_perkawinan_tersimpan(): void
    {
        $agama = \App\Models\RefAgama::create(['nama' => 'Hindu']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid([
                'agama_id' => $agama->id,
                'status_perkawinan' => 'kawin_tercatat',
            ]))
            ->assertSessionHasNoErrors();

        $warga = Penduduk::where('nama_lengkap', 'Budi Santoso')->first();

        $this->assertSame($agama->id, $warga->agama_id);
        $this->assertSame(\App\Enums\StatusPerkawinan::KawinTercatat, $warga->status_perkawinan);
        $this->assertSame('Hindu', $warga->agama->nama);
    }

    public function test_agama_dan_status_perkawinan_opsional(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.penduduk.store'), $this->dataValid([
                'agama_id' => null,
                'status_perkawinan' => null,
            ]))
            ->assertSessionHasNoErrors();
    }
}
