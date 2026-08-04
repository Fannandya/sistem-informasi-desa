<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\TeksTampilan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeksTampilanTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);
    }

    // ------------------------------------------------------------------
    // Model & cache
    // ------------------------------------------------------------------

    public function test_semua_mengembalikan_peta_kunci_ke_nilai(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo', 'grup' => 'beranda']);

        $this->assertSame(['beranda.hero.cta_profil' => 'Ayo'], TeksTampilan::semua());
    }

    public function test_cache_terhapus_otomatis_setelah_simpan_dan_hapus(): void
    {
        TeksTampilan::semua(); // isi cache dengan array kosong

        $baris = TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo', 'grup' => 'beranda']);
        $this->assertSame(['beranda.hero.cta_profil' => 'Ayo'], TeksTampilan::semua());

        $baris->delete();
        $this->assertSame([], TeksTampilan::semua());
    }

    // ------------------------------------------------------------------
    // Kelas pendukung Teks
    // ------------------------------------------------------------------

    public function test_teks_ambil_memakai_default_config_saat_db_kosong(): void
    {
        $this->assertSame('Kenali Desa Kami', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_teks_ambil_memakai_nilai_db_bila_terisi(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->assertSame('Ayo Kenalan', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_teks_ambil_mengganti_placeholder(): void
    {
        $hasil = \App\Support\Teks::ambil('beranda.kontak.kalimat', ['{nama}' => 'Sukamaju']);

        $this->assertSame('Kantor Sukamaju melayani warga pada hari kerja.', $hasil);
    }

    public function test_teks_angka_mengembalikan_bilangan_bulat_dan_ikut_cache(): void
    {
        $this->assertSame(6, \App\Support\Teks::angka('kuota.beranda.potensi'));

        TeksTampilan::create(['kunci' => 'kuota.beranda.potensi', 'nilai' => '3', 'grup' => 'kuota']);

        $this->assertSame(3, \App\Support\Teks::angka('kuota.beranda.potensi'));
    }

    // ------------------------------------------------------------------
    // Akses & penyimpanan halaman admin
    // ------------------------------------------------------------------

    public function test_tamu_tidak_bisa_membuka_teks_tampilan(): void
    {
        $this->get(route('admin.teks-tampilan.index'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.teks-tampilan.perbarui', 'beranda'), [])
            ->assertRedirect(route('admin.login'));
    }

    public function test_grup_yang_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'grup-ngawur'), [])
            ->assertNotFound();
    }

    public function test_admin_dapat_menyimpan_teks_beranda(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['beranda__hero__cta_profil' => 'Ayo Kenalan'],
            ])
            ->assertRedirect(route('admin.teks-tampilan.index', ['grup' => 'beranda']));

        $this->assertDatabaseHas('teks_tampilan', [
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => 'Ayo Kenalan',
            'grup' => 'beranda',
            'admin_id' => $this->admin->id,
        ]);
    }

    public function test_mengosongkan_field_mengembalikan_ke_default(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['beranda__hero__cta_profil' => ''],
            ]);

        $this->assertDatabaseHas('teks_tampilan', [
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => null,
        ]);
        $this->assertSame('Kenali Desa Kami', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_kunci_di_luar_grup_tidak_ikut_tersimpan(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['kuota__beranda__potensi' => '99'],
            ]);

        $this->assertDatabaseMissing('teks_tampilan', ['kunci' => 'kuota.beranda.potensi']);
    }

    public function test_angka_kuota_ditolak_di_luar_rentang(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'kuota'), [
                'nilai' => ['kuota__beranda__potensi' => '99'],
            ])
            ->assertSessionHasErrors('nilai.kuota__beranda__potensi');

        $this->assertDatabaseMissing('teks_tampilan', ['kunci' => 'kuota.beranda.potensi']);
    }

    // ------------------------------------------------------------------
    // Tampilan halaman admin
    // ------------------------------------------------------------------

    public function test_halaman_teks_tampilan_menampilkan_field_setiap_kunci(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.teks-tampilan.index'));

        $response->assertOk();
        $response->assertSee('name="nilai[beranda__hero__cta_profil]"', false);
        $response->assertSee('name="nilai[footer__pengelola]"', false);
        $response->assertSee('name="nilai[menu__beranda]"', false);
        $response->assertSee('name="nilai[kuota__beranda__potensi]"', false);
    }

    public function test_field_terisi_dengan_nilai_tersimpan(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.teks-tampilan.index'))
            ->assertSee('value="Ayo Kenalan"', false);
    }

    public function test_kesalahan_validasi_di_grup_lain_membuka_tab_yang_sesuai(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'kuota'), [
                'nilai' => ['kuota__beranda__potensi' => '99'],
            ])
            ->assertSessionHasErrors('nilai.kuota__beranda__potensi');

        // Redirect target-nya tidak menyertakan ?grup=kuota, jadi tab yang
        // terbuka harus ditentukan dari lokasi kesalahan validasi di session.
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.teks-tampilan.index'));

        $response->assertSee('x-data="{ grup: \'kuota\' }"', false);
        $response->assertDontSee('x-data="{ grup: \'beranda\' }"', false);
    }

    // ------------------------------------------------------------------
    // Integrasi publik — menu & footer
    // ------------------------------------------------------------------

    public function test_menu_dan_footer_memakai_teks_tersimpan(): void
    {
        // Pastikan desa punya kontak agar kolom kontak ditampilkan di footer
        \App\Models\PengaturanDesa::ambil()->update(['email' => 'desa@test.id']);

        TeksTampilan::create(['kunci' => 'menu.beranda', 'nilai' => 'Home', 'grup' => 'menu']);
        TeksTampilan::create(['kunci' => 'footer.pengelola', 'nilai' => 'Dikelola oleh Kelurahan.', 'grup' => 'footer']);
        TeksTampilan::create(['kunci' => 'footer.kolom_kontak', 'nilai' => 'Hubungi', 'grup' => 'footer']);

        $response = $this->get(route('beranda'));

        $response->assertSee('Home');
        $response->assertSee('Dikelola oleh Kelurahan.');
        $response->assertSee('Hubungi');
    }

    public function test_menu_dan_footer_memakai_default_saat_belum_diubah(): void
    {
        $response = $this->get(route('beranda'));

        $response->assertSee('Struktur Organisasi'); // menu.struktur default
        $response->assertSee('Dikelola oleh Pemerintah Desa.'); // footer.pengelola default
    }

    // ------------------------------------------------------------------
    // Integrasi publik — beranda
    // ------------------------------------------------------------------

    public function test_beranda_menampilkan_teks_default(): void
    {
        $this->get(route('beranda'))->assertSee('Kenali Desa Kami');
    }

    public function test_beranda_menampilkan_teks_hasil_ubahan_admin(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->get(route('beranda'))->assertSee('Ayo Kenalan');
    }

    public function test_nilai_html_dirender_ter_escape(): void
    {
        TeksTampilan::create([
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => '<script>alert(1)</script>',
            'grup' => 'beranda',
        ]);

        $html = $this->get(route('beranda'))->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_kuota_potensi_mengubah_jumlah_kartu_di_beranda(): void
    {
        foreach (range(1, 5) as $i) {
            \App\Models\PotensiDesa::create(['nama' => "Potensi {$i}", 'kategori' => 'umkm', 'tampil' => true]);
        }

        TeksTampilan::create(['kunci' => 'kuota.beranda.potensi', 'nilai' => '2', 'grup' => 'kuota']);

        $response = $this->get(route('beranda'))->assertOk();

        $this->assertCount(2, $response->viewData('potensi'));
    }

    public function test_kuota_default_potensi_tetap_enam(): void
    {
        foreach (range(1, 20) as $i) {
            \App\Models\PotensiDesa::create(['nama' => "Potensi {$i}", 'kategori' => 'umkm', 'tampil' => true]);
        }

        $response = $this->get(route('beranda'))->assertOk();

        $this->assertCount(6, $response->viewData('potensi'));
    }
}
