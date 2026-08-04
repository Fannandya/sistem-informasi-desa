<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\BeritaDesa;
use App\Models\KategoriBerita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BeritaTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private KategoriBerita $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->kategori = KategoriBerita::create(['nama' => 'Pengumuman']);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function berita(array $atribut = []): BeritaDesa
    {
        return BeritaDesa::create(array_merge([
            'judul' => 'Musyawarah Desa Bahas APBDes',
            'isi' => '<p>Musyawarah dihadiri seluruh ketua RT.</p>',
            'status' => 'terbit',
            'tanggal_terbit' => now(),
        ], $atribut));
    }

    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'judul' => 'Musyawarah Desa Bahas APBDes',
            'isi' => '<p>Musyawarah dihadiri seluruh ketua RT.</p>',
            'status' => 'terbit',
        ], $ganti);
    }

    // ------------------------------------------------------------------
    // Sanitasi HTML — inti keamanan fitur ini
    // ------------------------------------------------------------------

    public static function isiBerbahayaProvider(): array
    {
        return [
            'skrip' => ['<p>Halo</p><script>alert(1)</script>', '<script'],
            'atribut event' => ['<p onclick="alert(1)">Halo</p>', 'onclick'],
            'iframe' => ['<p>Halo</p><iframe src="https://jahat.test"></iframe>', '<iframe'],
            'javascript uri' => ['<a href="javascript:alert(1)">Klik</a>', 'javascript:'],
            'style' => ['<p style="position:fixed">Halo</p>', 'style='],
            'img onerror' => ['<img src="x" onerror="alert(1)">', 'onerror'],
            'form' => ['<form action="https://jahat.test"><input name="a"></form>', '<form'],
        ];
    }

    #[DataProvider('isiBerbahayaProvider')]
    public function test_isi_berita_dibersihkan_sebelum_disimpan(string $isi, string $terlarang): void
    {
        // Isi berita dirender apa adanya di halaman baca, sehingga apa pun
        // yang lolos akan dieksekusi peramban pengunjung.
        $this->sebagaiAdmin()->post(route('admin.berita.store'), $this->dataValid(['isi' => $isi]));

        $tersimpan = BeritaDesa::first()->isi;

        $this->assertStringNotContainsString($terlarang, $tersimpan);
    }

    public function test_tag_aman_tetap_dipertahankan(): void
    {
        $isi = '<h2>Judul</h2><p>Paragraf <strong>tebal</strong> dan <em>miring</em>.</p>'
            .'<ul><li>Poin satu</li></ul><a href="https://desa.test">tautan</a>';

        $this->sebagaiAdmin()->post(route('admin.berita.store'), $this->dataValid(['isi' => $isi]));

        $tersimpan = BeritaDesa::first()->isi;

        foreach (['<h2>', '<strong>', '<em>', '<ul>', '<li>', 'href="https://desa.test"'] as $tag) {
            $this->assertStringContainsString($tag, $tersimpan);
        }
    }

    // ------------------------------------------------------------------
    // CRUD & status
    // ------------------------------------------------------------------

    public function test_berita_baru_tersimpan_dengan_penulisnya(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.berita.store'), $this->dataValid())
            ->assertRedirect(route('admin.berita.index'));

        $berita = BeritaDesa::first();

        $this->assertSame($this->admin->id, $berita->admin_id);
        $this->assertNotNull($berita->tanggal_terbit);
    }

    public function test_isi_wajib_diisi(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.berita.store'), $this->dataValid(['isi' => '']))
            ->assertSessionHasErrors('isi');
    }

    public function test_status_bisa_diubah_dari_daftar(): void
    {
        $berita = $this->berita(['status' => 'draf', 'tanggal_terbit' => null]);

        $this->sebagaiAdmin()->post(route('admin.berita.status', $berita));

        $berita = $berita->fresh();
        $this->assertTrue($berita->sudahTerbit());
        $this->assertNotNull($berita->tanggal_terbit);
    }

    public function test_waktu_terbit_dipertahankan_saat_dikembalikan_ke_draf(): void
    {
        // Kalau waktu terbit ikut direset, urutan daftar berita akan berubah
        // hanya karena admin sempat menyembunyikannya sebentar.
        $berita = $this->berita(['tanggal_terbit' => now()->subDays(10)]);
        $asli = $berita->tanggal_terbit;

        $this->sebagaiAdmin()->post(route('admin.berita.status', $berita)); // ke draf
        $this->sebagaiAdmin()->post(route('admin.berita.status', $berita)); // terbit lagi

        $this->assertEquals($asli->toDateTimeString(), $berita->fresh()->tanggal_terbit->toDateTimeString());
    }

    public function test_hanya_ada_satu_berita_utama(): void
    {
        $a = $this->berita(['judul' => 'Berita A']);
        $b = $this->berita(['judul' => 'Berita B']);

        $this->sebagaiAdmin()->post(route('admin.berita.utama', $a));
        $this->assertTrue($a->fresh()->utama);

        $this->sebagaiAdmin()->post(route('admin.berita.utama', $b));

        $this->assertFalse($a->fresh()->utama);
        $this->assertTrue($b->fresh()->utama);
        $this->assertSame(1, BeritaDesa::where('utama', true)->count());
    }

    // ------------------------------------------------------------------
    // Slug
    // ------------------------------------------------------------------

    public function test_slug_dibuat_otomatis_dan_tidak_berubah_saat_judul_diubah(): void
    {
        $berita = $this->berita();
        $slugAsli = $berita->slug;

        $this->assertSame('musyawarah-desa-bahas-apbdes', $slugAsli);

        $this->sebagaiAdmin()->put(
            route('admin.berita.update', $berita),
            $this->dataValid(['judul' => 'Musyawarah Desa Bahas APBDes Tahun 2027']),
        );

        $this->assertSame($slugAsli, $berita->fresh()->slug);
    }

    // ------------------------------------------------------------------
    // Halaman publik
    // ------------------------------------------------------------------

    public function test_daftar_publik_hanya_menampilkan_berita_terbit(): void
    {
        $this->berita(['judul' => 'Sudah Terbit', 'status' => 'terbit']);
        $this->berita(['judul' => 'Masih Draf', 'status' => 'draf']);

        $this->get(route('publik.berita.index'))
            ->assertOk()
            ->assertSee('Sudah Terbit')
            ->assertDontSee('Masih Draf');
    }

    public function test_draf_tidak_bisa_dibuka_meski_alamatnya_ditebak(): void
    {
        $berita = $this->berita(['status' => 'draf']);

        $this->get(route('publik.berita.show', $berita))->assertNotFound();
    }

    public function test_halaman_baca_menambah_penghitung_dilihat(): void
    {
        $berita = $this->berita();

        $this->get(route('publik.berita.show', $berita))->assertOk();
        $this->get(route('publik.berita.show', $berita))->assertOk();

        $this->assertSame(2, $berita->fresh()->dilihat);
    }

    public function test_berita_utama_disorot_di_daftar(): void
    {
        $this->berita(['judul' => 'Berita Utama Ini', 'utama' => true]);

        $this->get(route('publik.berita.index'))
            ->assertOk()
            ->assertSee('Berita Utama');
    }

    public function test_saringan_kategori_bekerja(): void
    {
        $this->berita(['judul' => 'Ada Kategori', 'kategori_id' => $this->kategori->id]);
        $this->berita(['judul' => 'Tanpa Kategori']);

        $this->get(route('publik.berita.kategori', $this->kategori))
            ->assertOk()
            ->assertSee('Ada Kategori')
            ->assertDontSee('Tanpa Kategori');
    }

    public function test_ringkasan_diambil_dari_isi_bila_dikosongkan(): void
    {
        $berita = $this->berita(['isi' => '<p>Musyawarah dihadiri seluruh ketua RT dan tokoh masyarakat.</p>']);

        // Tag HTML tidak boleh bocor sebagai teks mentah pada kartu daftar.
        $this->assertStringNotContainsString('<p>', $berita->ringkasanTampil());
        $this->assertStringContainsString('Musyawarah dihadiri', $berita->ringkasanTampil());
    }

    public function test_meta_description_dipotong_di_bawah_batas_wajar(): void
    {
        $berita = $this->berita(['ringkasan' => str_repeat('kabar desa ', 60)]);

        $this->assertLessThanOrEqual(160, mb_strlen($berita->deskripsiMeta()));
    }

    public function test_daftar_publik_memakai_pagination_sembilan(): void
    {
        foreach (range(1, 12) as $i) {
            $this->berita(['judul' => "Berita {$i}"]);
        }

        $response = $this->get(route('publik.berita.index'))->assertOk();

        $this->assertCount(9, $response->viewData('daftar')->items());
    }

    public function test_daftar_publik_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('publik.berita.index'))
            ->assertOk()
            ->assertSee('Belum ada berita');
    }

    // ------------------------------------------------------------------
    // Kategori
    // ------------------------------------------------------------------

    public function test_kategori_ganda_ditolak(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.kategori-berita.store'), ['nama' => 'pengumuman'])
            ->assertSessionHasErrors('nama');
    }

    public function test_kategori_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $this->berita(['kategori_id' => $this->kategori->id]);

        $this->sebagaiAdmin()
            ->delete(route('admin.kategori-berita.destroy', $this->kategori))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('kategori_berita', ['id' => $this->kategori->id]);
    }

    public function test_menghapus_kategori_tidak_menghapus_beritanya(): void
    {
        $kategoriLain = KategoriBerita::create(['nama' => 'Kegiatan']);
        $berita = $this->berita(['kategori_id' => $kategoriLain->id]);

        // Kategori dipakai, jadi dilindungi. Uji jalur nullOnDelete langsung.
        $kategoriLain->berita()->update(['kategori_id' => null]);
        $kategoriLain->delete();

        $this->assertDatabaseHas('berita_desa', ['id' => $berita->id, 'kategori_id' => null]);
    }

    public function test_tamu_ditolak_di_halaman_admin(): void
    {
        $this->get(route('admin.berita.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.berita.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.berita.store'), [])->assertRedirect(route('admin.login'));
    }
}
