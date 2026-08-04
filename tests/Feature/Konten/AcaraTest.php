<?php

namespace Tests\Feature\Konten;

use App\Models\AcaraDesa;
use App\Models\Admin;
use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcaraTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function acara(array $atribut = []): AcaraDesa
    {
        return AcaraDesa::create(array_merge([
            'judul' => 'Kerja Bakti Bersihkan Saluran Air',
            'tanggal_acara' => now()->subWeek(),
            'status' => 'terbit',
        ], $atribut));
    }

    private function unggah(AcaraDesa $acara, int $jumlah = 1)
    {
        return $this->sebagaiAdmin()->post(route('admin.acara.foto.unggah', $acara), [
            'foto' => collect(range(1, $jumlah))
                ->map(fn ($i) => UploadedFile::fake()->image("f{$i}.jpg", 800, 600))
                ->all(),
        ]);
    }

    // ------------------------------------------------------------------
    // Foto
    // ------------------------------------------------------------------

    public function test_beberapa_foto_bisa_diunggah_sekaligus(): void
    {
        $acara = $this->acara();

        $this->unggah($acara, 3)->assertSessionHasNoErrors();

        $this->assertSame(3, $acara->foto()->count());
        foreach ($acara->foto as $foto) {
            Storage::disk('public')->assertExists($foto->path);
        }
    }

    public function test_foto_pertama_otomatis_menjadi_sampul(): void
    {
        $acara = $this->acara();
        $this->unggah($acara, 3);

        $this->assertSame(1, $acara->foto()->where('sampul', true)->count());
        $this->assertTrue($acara->foto()->first()->sampul);
    }

    public function test_batas_jumlah_foto_per_unggahan_ditegakkan(): void
    {
        config()->set('unggahan.maks_berkas_per_kiriman', 3);
        $acara = $this->acara();

        $this->unggah($acara, 5)->assertSessionHasErrors('foto');

        $this->assertSame(0, $acara->foto()->count());
    }

    public function test_sampul_bisa_dipindah_ke_foto_lain(): void
    {
        $acara = $this->acara();
        $this->unggah($acara, 3);
        $target = $acara->foto()->skip(1)->first();

        $this->sebagaiAdmin()->put(route('admin.acara.foto.perbarui', $acara), [
            'sampul' => $target->id,
            'keterangan' => [$target->id => 'Warga bergotong royong'],
        ]);

        $this->assertTrue($target->fresh()->sampul);
        $this->assertSame(1, $acara->foto()->where('sampul', true)->count());
        $this->assertSame('Warga bergotong royong', $target->fresh()->keterangan);
    }

    public function test_menghapus_foto_ikut_menghapus_berkasnya(): void
    {
        $acara = $this->acara();
        $this->unggah($acara, 2);
        $foto = $acara->foto()->first();
        $path = $foto->path;

        $this->sebagaiAdmin()->delete(route('admin.acara.foto.hapus', [$acara, $foto]));

        Storage::disk('public')->assertMissing($path);
        $this->assertSame(1, $acara->foto()->count());
    }

    public function test_menghapus_sampul_menaikkan_foto_berikutnya(): void
    {
        // Tanpa ini, kartu galeri mendadak kehilangan gambarnya.
        $acara = $this->acara();
        $this->unggah($acara, 3);
        $sampul = $acara->foto()->where('sampul', true)->first();

        $this->sebagaiAdmin()->delete(route('admin.acara.foto.hapus', [$acara, $sampul]));

        $this->assertSame(1, $acara->foto()->where('sampul', true)->count());
        $this->assertNotNull($acara->fresh()->fotoSampul());
    }

    public function test_foto_acara_lain_tidak_bisa_dihapus_lewat_acara_ini(): void
    {
        $acaraA = $this->acara(['judul' => 'Acara A']);
        $acaraB = $this->acara(['judul' => 'Acara B']);
        $this->unggah($acaraB, 1);

        $this->sebagaiAdmin()
            ->delete(route('admin.acara.foto.hapus', [$acaraA, $acaraB->foto()->first()]))
            ->assertNotFound();

        $this->assertSame(1, $acaraB->foto()->count());
    }

    public function test_menghapus_acara_ikut_menghapus_seluruh_fotonya(): void
    {
        $acara = $this->acara();
        $this->unggah($acara, 3);
        $path = $acara->foto->pluck('path');

        $this->sebagaiAdmin()->delete(route('admin.acara.destroy', $acara));

        $this->assertDatabaseCount('acara_desa', 0);
        $this->assertDatabaseCount('foto_acara', 0);
        foreach ($path as $p) {
            Storage::disk('public')->assertMissing($p);
        }
    }

    // ------------------------------------------------------------------
    // CRUD & status
    // ------------------------------------------------------------------

    public function test_acara_baru_mengarah_ke_halaman_unggah_foto(): void
    {
        // Dokumentasi tanpa foto tidak ada gunanya, jadi admin langsung
        // diarahkan ke langkah berikutnya.
        $this->sebagaiAdmin()
            ->post(route('admin.acara.store'), [
                'judul' => 'Musyawarah Desa',
                'tanggal_acara' => now()->subDay()->toDateString(),
                'status' => 'draf',
            ])
            ->assertRedirect(route('admin.acara.edit', AcaraDesa::first()));
    }

    public function test_kolom_wajib_divalidasi(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.acara.store'), [])
            ->assertSessionHasErrors(['judul', 'tanggal_acara', 'status']);
    }

    public function test_status_bisa_diubah_dari_daftar(): void
    {
        $acara = $this->acara(['status' => 'draf']);

        $this->sebagaiAdmin()->post(route('admin.acara.status', $acara));

        $this->assertTrue($acara->fresh()->sudahTerbit());
    }

    public function test_slug_tidak_berubah_saat_judul_diubah(): void
    {
        $acara = $this->acara();
        $slugAsli = $acara->slug;

        $this->sebagaiAdmin()->put(route('admin.acara.update', $acara), [
            'judul' => 'Kerja Bakti Bersihkan Saluran Air dan Selokan',
            'tanggal_acara' => $acara->tanggal_acara->toDateString(),
            'status' => 'terbit',
        ]);

        $this->assertSame($slugAsli, $acara->fresh()->slug);
    }

    // ------------------------------------------------------------------
    // Halaman publik
    // ------------------------------------------------------------------

    public function test_galeri_publik_hanya_menampilkan_acara_terbit(): void
    {
        $this->acara(['judul' => 'Sudah Terbit', 'status' => 'terbit']);
        $this->acara(['judul' => 'Masih Draf', 'status' => 'draf']);

        $this->get(route('publik.galeri.index'))
            ->assertOk()
            ->assertSee('Sudah Terbit')
            ->assertDontSee('Masih Draf');
    }

    public function test_draf_tidak_bisa_dibuka_meski_alamatnya_ditebak(): void
    {
        $acara = $this->acara(['status' => 'draf']);

        $this->get(route('publik.galeri.show', $acara))->assertNotFound();
    }

    public function test_halaman_detail_menampilkan_seluruh_foto(): void
    {
        $acara = $this->acara();
        $this->unggah($acara, 4);

        $this->get(route('publik.galeri.show', $acara))
            ->assertOk()
            ->assertSee('4 foto')
            ->assertSee('Kerja Bakti');
    }

    public function test_acara_tanpa_foto_tetap_bisa_dibuka(): void
    {
        $acara = $this->acara();

        $this->get(route('publik.galeri.show', $acara))
            ->assertOk()
            ->assertSee('belum tersedia');
    }

    public function test_galeri_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.galeri.index'))
            ->assertOk()
            ->assertSee('Belum ada dokumentasi kegiatan');
    }

    public function test_tamu_ditolak_di_halaman_admin(): void
    {
        $acara = $this->acara();

        $this->get(route('admin.acara.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.acara.foto.unggah', $acara), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.acara.destroy', $acara))->assertRedirect(route('admin.login'));
    }
}
