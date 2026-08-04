<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\Pengurus;
use App\Models\RefOrganisasi;
use App\Models\Wilayah;
use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PengurusTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private RefOrganisasi $pemdes;

    private RefOrganisasi $rt;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->pemdes = RefOrganisasi::create(['nama' => 'Pemerintah Desa', 'urutan' => 1]);
        $this->rt = RefOrganisasi::create(['nama' => 'RT', 'urutan' => 4]);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function pengurus(array $atribut = []): Pengurus
    {
        return Pengurus::create(array_merge([
            'nama' => 'Sutrisno Hadi',
            'jabatan' => 'Kepala Desa',
            'organisasi_id' => $this->pemdes->id,
            'tampil' => true,
            'urutan' => 0,
        ], $atribut));
    }

    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'nama' => 'Sutrisno Hadi',
            'jabatan' => 'Kepala Desa',
            'organisasi_id' => $this->pemdes->id,
            'tampil' => '1',
        ], $ganti);
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public function test_pengurus_baru_tersimpan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.pengurus.store'), $this->dataValid(['nomor_hp' => '0812-3456-7890']))
            ->assertRedirect(route('admin.pengurus.index'));

        $this->assertDatabaseHas('pengurus_desa', ['nama' => 'Sutrisno Hadi', 'tampil' => true]);
    }

    public function test_kolom_wajib_divalidasi(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.pengurus.store'), [])
            ->assertSessionHasErrors(['nama', 'jabatan', 'organisasi_id']);
    }

    public function test_nomor_hp_menolak_huruf(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.pengurus.store'), $this->dataValid(['nomor_hp' => 'hubungi pak RT']))
            ->assertSessionHasErrors('nomor_hp');
    }

    public function test_pengurus_bisa_diubah(): void
    {
        $pengurus = $this->pengurus();

        $this->sebagaiAdmin()
            ->put(route('admin.pengurus.update', $pengurus), $this->dataValid(['jabatan' => 'Sekretaris Desa']))
            ->assertRedirect(route('admin.pengurus.index'));

        $this->assertSame('Sekretaris Desa', $pengurus->fresh()->jabatan);
    }

    public function test_menghapus_pengurus_ikut_menghapus_fotonya(): void
    {
        // PRD 6.16: pengurus memakai hard delete, sehingga fotonya boleh
        // ikut dihapus dari penyimpanan.
        $layanan = app(LayananUnggahGambar::class);
        $foto = $layanan->simpan(UploadedFile::fake()->image('f.jpg', 600, 600), 'pengurus');
        $pengurus = $this->pengurus(['foto' => $foto]);

        $this->sebagaiAdmin()->delete(route('admin.pengurus.destroy', $pengurus));

        $this->assertDatabaseCount('pengurus_desa', 0);
        Storage::disk('public')->assertMissing($foto);
        Storage::disk('public')->assertMissing($layanan->pathThumbnail($foto));
    }

    public function test_foto_tersimpan_dan_diganti_dengan_benar(): void
    {
        $this->sebagaiAdmin()->post(route('admin.pengurus.store'), $this->dataValid([
            'foto' => UploadedFile::fake()->image('lama.jpg', 600, 600),
        ]));

        $lama = Pengurus::first()->foto;
        Storage::disk('public')->assertExists($lama);

        $this->sebagaiAdmin()->put(route('admin.pengurus.update', Pengurus::first()), $this->dataValid([
            'foto' => UploadedFile::fake()->image('baru.jpg', 600, 600),
        ]));

        $baru = Pengurus::first()->foto;

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);
    }

    // ------------------------------------------------------------------
    // Status tampil & urutan
    // ------------------------------------------------------------------

    public function test_status_tampil_bisa_diubah_dari_daftar(): void
    {
        $pengurus = $this->pengurus(['tampil' => true]);

        $this->sebagaiAdmin()->post(route('admin.pengurus.tampil', $pengurus));
        $this->assertFalse($pengurus->fresh()->tampil);

        $this->sebagaiAdmin()->post(route('admin.pengurus.tampil', $pengurus));
        $this->assertTrue($pengurus->fresh()->tampil);
    }

    public function test_urutan_bisa_digeser_naik_turun(): void
    {
        $a = $this->pengurus(['nama' => 'A', 'urutan' => 0]);
        $b = $this->pengurus(['nama' => 'B', 'urutan' => 1]);

        $this->sebagaiAdmin()->post(route('admin.pengurus.geser', $b), ['arah' => 'naik']);

        $this->assertSame(0, $b->fresh()->urutan);
        $this->assertSame(1, $a->fresh()->urutan);
    }

    public function test_geser_di_ujung_tidak_error(): void
    {
        $a = $this->pengurus(['urutan' => 0]);

        $this->sebagaiAdmin()->post(route('admin.pengurus.geser', $a), ['arah' => 'naik'])->assertRedirect();

        $this->assertSame(0, $a->fresh()->urutan);
    }

    public function test_geser_tidak_melintas_organisasi_lain(): void
    {
        $pemdes = $this->pengurus(['nama' => 'Kades', 'organisasi_id' => $this->pemdes->id, 'urutan' => 5]);
        $ketuaRt = $this->pengurus(['nama' => 'Ketua RT', 'organisasi_id' => $this->rt->id, 'urutan' => 1]);

        $this->sebagaiAdmin()->post(route('admin.pengurus.geser', $pemdes), ['arah' => 'naik']);

        // Tidak ada tetangga di organisasinya sendiri, jadi tidak berubah.
        $this->assertSame(5, $pemdes->fresh()->urutan);
        $this->assertSame(1, $ketuaRt->fresh()->urutan);
    }

    // ------------------------------------------------------------------
    // Organisasi sebagai data referensi
    // ------------------------------------------------------------------

    public function test_organisasi_ganda_ditolak_tanpa_memandang_huruf(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.organisasi.store'), ['nama' => 'pemerintah desa'])
            ->assertSessionHasErrors('nama');

        $this->assertSame(2, RefOrganisasi::count());
    }

    public function test_organisasi_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $this->pengurus();

        $this->sebagaiAdmin()
            ->delete(route('admin.organisasi.destroy', $this->pemdes))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('ref_organisasi', ['id' => $this->pemdes->id]);
    }

    public function test_organisasi_kosong_bisa_dihapus(): void
    {
        $this->sebagaiAdmin()
            ->delete(route('admin.organisasi.destroy', $this->rt))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('ref_organisasi', ['id' => $this->rt->id]);
    }

    // ------------------------------------------------------------------
    // Nomor WhatsApp
    // ------------------------------------------------------------------

    public static function nomorProvider(): array
    {
        return [
            ['081234567890', '6281234567890'],
            ['0812-3456-7890', '6281234567890'],
            ['+62 812 3456 7890', '6281234567890'],
            ['(0812) 3456 7890', '6281234567890'],
            ['812345678', '62812345678'],
        ];
    }

    #[DataProvider('nomorProvider')]
    public function test_nomor_whatsapp_dinormalkan(string $masukan, string $harapan): void
    {
        $this->assertSame($harapan, $this->pengurus(['nomor_hp' => $masukan])->nomorWhatsapp());
    }

    public function test_pengurus_tanpa_nomor_tidak_punya_tautan_whatsapp(): void
    {
        $this->assertNull($this->pengurus(['nomor_hp' => null])->nomorWhatsapp());
    }

    // ------------------------------------------------------------------
    // Halaman publik
    // ------------------------------------------------------------------

    public function test_halaman_publik_dapat_dibuka_tanpa_login(): void
    {
        $this->pengurus();

        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertSee('Sutrisno Hadi');
    }

    public function test_pengurus_tersembunyi_tidak_muncul_di_publik(): void
    {
        $this->pengurus(['nama' => 'Tampil', 'tampil' => true]);
        $this->pengurus(['nama' => 'Disembunyikan', 'tampil' => false]);

        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('Tampil')
            ->assertDontSee('Disembunyikan');
    }

    public function test_kelompok_diurutkan_sesuai_urutan_organisasi(): void
    {
        $this->pengurus(['nama' => 'Ketua RT Satu', 'organisasi_id' => $this->rt->id]);
        $this->pengurus(['nama' => 'Kepala Desa Satu', 'organisasi_id' => $this->pemdes->id]);

        $html = $this->get(route('publik.struktur'))->assertOk()->getContent();

        // Pemerintah Desa (urutan 1) harus muncul sebelum RT (urutan 4).
        $this->assertLessThan(
            mb_strpos($html, '>RT<'),
            mb_strpos($html, 'Pemerintah Desa'),
        );
    }

    public function test_halaman_publik_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('Data pengurus belum tersedia');
    }

    public function test_nomor_hp_dan_tautan_whatsapp_tampil_di_publik(): void
    {
        $this->pengurus(['nomor_hp' => '081234567890']);

        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('081234567890')
            ->assertSee('https://wa.me/6281234567890');
    }

    public function test_wilayah_pengurus_rt_tampil(): void
    {
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '002']);
        $this->pengurus(['organisasi_id' => $this->rt->id, 'wilayah_id' => $wilayah->id]);

        $this->get(route('publik.struktur'))
            ->assertOk()
            ->assertSee('RT 002/RW 001');
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_ditolak_di_halaman_admin(): void
    {
        $pengurus = $this->pengurus();

        $this->get(route('admin.pengurus.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.pengurus.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.pengurus.store'), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.pengurus.destroy', $pengurus))->assertRedirect(route('admin.login'));
    }
}
