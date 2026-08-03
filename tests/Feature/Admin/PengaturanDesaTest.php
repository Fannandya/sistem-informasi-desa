<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\PengaturanDesa;
use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PengaturanDesaTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        $this->admin = Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);
    }

    private function simpan(string $bagian, array $data)
    {
        return $this->actingAs($this->admin, 'admin')
            ->put(route('admin.pengaturan.perbarui', $bagian), $data);
    }

    // ------------------------------------------------------------------
    // Singleton
    // ------------------------------------------------------------------

    public function test_pengaturan_selalu_satu_baris_dengan_id_satu(): void
    {
        $pertama = PengaturanDesa::ambil();
        $kedua = PengaturanDesa::ambil();

        $this->assertSame(PengaturanDesa::ID, $pertama->id);
        $this->assertTrue($pertama->is($kedua));
        $this->assertDatabaseCount('pengaturan_desa', 1);
    }

    public function test_membuat_baris_kedua_ditolak(): void
    {
        PengaturanDesa::ambil();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('singleton');

        PengaturanDesa::create(['nama_desa' => 'Desa Kedua']);
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_tidak_bisa_membuka_pengaturan(): void
    {
        $this->get(route('admin.pengaturan'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.pengaturan.perbarui', 'identitas'), [])
            ->assertRedirect(route('admin.login'));
    }

    public function test_bagian_yang_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.pengaturan.perbarui', 'bagian-ngawur'), [])
            ->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Identitas
    // ------------------------------------------------------------------

    public function test_identitas_desa_tersimpan(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Sukamaju',
            'kecamatan' => 'Cibeureum',
            'kabupaten' => 'Bandung Barat',
            'provinsi' => 'Jawa Barat',
            'tahun_berdiri' => 1952,
            'luas_desa' => 428.75,
        ])->assertSessionHasNoErrors();

        $desa = PengaturanDesa::ambil();

        $this->assertSame('Desa Sukamaju', $desa->nama_desa);
        $this->assertSame('Cibeureum, Bandung Barat, Jawa Barat', $desa->wilayahLengkap());
        $this->assertSame(1952, $desa->tahun_berdiri);
    }

    public function test_nama_desa_wajib_diisi(): void
    {
        $this->simpan('identitas', ['nama_desa' => ''])
            ->assertSessionHasErrors('nama_desa');
    }

    public function test_tahun_berdiri_tidak_boleh_melebihi_tahun_ini(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'tahun_berdiri' => now()->year + 1,
        ])->assertSessionHasErrors('tahun_berdiri');
    }

    public function test_luas_desa_harus_angka(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'luas_desa' => 'seratus hektare',
        ])->assertSessionHasErrors('luas_desa');
    }

    public function test_logo_dan_sampul_tersimpan_ke_penyimpanan(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
            'foto_sampul' => UploadedFile::fake()->image('sampul.jpg', 1920, 1080),
        ])->assertSessionHasNoErrors();

        $desa = PengaturanDesa::ambil();

        $this->assertStringStartsWith('logo/', $desa->logo);
        $this->assertStringStartsWith('sampul/', $desa->foto_sampul);
        Storage::disk('public')->assertExists($desa->logo);
        Storage::disk('public')->assertExists($desa->foto_sampul);
    }

    public function test_mengganti_logo_menghapus_logo_lama(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
        ]);
        $lama = PengaturanDesa::ambil()->logo;

        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'logo' => UploadedFile::fake()->image('logo-baru.png', 512, 512),
        ]);
        $baru = PengaturanDesa::ambil()->logo;

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);
    }

    public function test_logo_bisa_dihapus_lewat_kotak_centang(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
        ]);
        $path = PengaturanDesa::ambil()->logo;

        $this->simpan('identitas', ['nama_desa' => 'Desa Uji', 'hapus_logo' => '1']);

        $this->assertNull(PengaturanDesa::ambil()->logo);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $this->simpan('identitas', [
            'nama_desa' => 'Desa Uji',
            'logo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->assertNull(PengaturanDesa::ambil()->logo);
    }

    // ------------------------------------------------------------------
    // Profil
    // ------------------------------------------------------------------

    public function test_visi_misi_dan_sejarah_tersimpan(): void
    {
        $this->simpan('profil', [
            'visi' => 'Desa mandiri dan sejahtera.',
            'misi' => "Meningkatkan pelayanan\nMengembangkan UMKM",
            'sejarah' => 'Berdiri sejak 1952.',
        ])->assertSessionHasNoErrors();

        $desa = PengaturanDesa::ambil();

        $this->assertSame('Desa mandiri dan sejahtera.', $desa->visi);
        $this->assertSame(['Meningkatkan pelayanan', 'Mengembangkan UMKM'], $desa->misiSebagaiDaftar());
    }

    public function test_misi_membuang_penomoran_manual(): void
    {
        // Admin sering menulis "1." atau "-" sendiri; kalau tidak dibuang,
        // halaman publik menampilkan "1. 1. Meningkatkan pelayanan".
        $this->simpan('profil', [
            'misi' => "1. Meningkatkan pelayanan\n2) Mengembangkan UMKM\n- Membangun infrastruktur\n\n",
        ]);

        $this->assertSame(
            ['Meningkatkan pelayanan', 'Mengembangkan UMKM', 'Membangun infrastruktur'],
            PengaturanDesa::ambil()->misiSebagaiDaftar(),
        );
    }

    public function test_misi_kosong_menghasilkan_daftar_kosong(): void
    {
        $this->assertSame([], PengaturanDesa::ambil()->misiSebagaiDaftar());
    }

    // ------------------------------------------------------------------
    // Peta
    // ------------------------------------------------------------------

    public function test_iframe_google_maps_disimpan_sebagai_url_saja(): void
    {
        $url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.6';
        $iframe = '<iframe src="'.$url.'" width="600" height="450" style="border:0;" '
            .'allowfullscreen="" loading="lazy"></iframe>';

        $this->simpan('geografis', ['peta_embed' => $iframe])->assertSessionHasNoErrors();

        // Yang tersimpan hanya URL — bukan HTML mentah dari admin.
        $tersimpan = PengaturanDesa::ambil()->peta_embed;
        $this->assertSame($url, $tersimpan);
        $this->assertStringNotContainsString('<iframe', $tersimpan);
    }

    public function test_url_peta_polos_juga_diterima(): void
    {
        $url = 'https://www.google.com/maps/embed?pb=!1m18';

        $this->simpan('geografis', ['peta_embed' => $url])->assertSessionHasNoErrors();

        $this->assertSame($url, PengaturanDesa::ambil()->peta_embed);
    }

    public static function petaTidakSahProvider(): array
    {
        return [
            'skrip disisipkan' => ['<script>alert(1)</script>'],
            'iframe situs lain' => ['<iframe src="https://jahat.test/x"></iframe>'],
            'host bukan google' => ['https://maps.jahat.test/maps/embed?pb=1'],
            'bukan https' => ['http://www.google.com/maps/embed?pb=1'],
            'google tapi bukan embed' => ['https://www.google.com/search?q=desa'],
            'teks biasa' => ['lokasi desa ada di sebelah balai warga'],
            'javascript uri' => ['<iframe src="javascript:alert(1)"></iframe>'],
        ];
    }

    #[DataProvider('petaTidakSahProvider')]
    public function test_kode_peta_tidak_sah_ditolak(string $masukan): void
    {
        $this->simpan('geografis', ['peta_embed' => $masukan])
            ->assertSessionHasErrors('peta_embed');

        $this->assertNull(PengaturanDesa::ambil()->peta_embed);
    }

    public function test_peta_boleh_dikosongkan(): void
    {
        $this->simpan('geografis', ['letak_geografis' => 'Di lereng gunung.', 'peta_embed' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull(PengaturanDesa::ambil()->peta_embed);
    }

    // ------------------------------------------------------------------
    // Kontak
    // ------------------------------------------------------------------

    public function test_kontak_dan_medsos_tersimpan(): void
    {
        $this->simpan('kontak', [
            'alamat_kantor' => 'Jalan Raya Sukamaju No. 12',
            'telepon_kantor' => '(022) 5550123',
            'email' => 'kontak@sukamaju.desa.id',
            'website' => 'https://sukamaju.desa.id',
            'medsos' => [
                'facebook' => 'https://facebook.com/desasukamaju',
                'instagram' => '',
                'youtube' => null,
            ],
        ])->assertSessionHasNoErrors();

        $desa = PengaturanDesa::ambil();

        $this->assertSame('kontak@sukamaju.desa.id', $desa->email);
        // Tautan kosong tidak ikut disimpan, agar tidak menampilkan ikon mati.
        $this->assertSame(['facebook' => 'https://facebook.com/desasukamaju'], $desa->medsosTerisi());
        $this->assertTrue($desa->punyaKontak());
    }

    public function test_email_dan_tautan_medsos_divalidasi(): void
    {
        $this->simpan('kontak', ['email' => 'bukan-email'])
            ->assertSessionHasErrors('email');

        $this->simpan('kontak', ['medsos' => ['facebook' => 'bukan-url']])
            ->assertSessionHasErrors('medsos.facebook');
    }

    public function test_telepon_menolak_huruf(): void
    {
        $this->simpan('kontak', ['telepon_kantor' => 'hubungi pak RT'])
            ->assertSessionHasErrors('telepon_kantor');
    }

    public function test_punya_kontak_false_saat_semua_kosong(): void
    {
        $this->assertFalse(PengaturanDesa::ambil()->punyaKontak());
    }

    // ------------------------------------------------------------------
    // SEO
    // ------------------------------------------------------------------

    public function test_meta_description_dipakai_bila_diisi(): void
    {
        $this->simpan('seo', ['meta_description' => 'Website resmi Desa Sukamaju.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Website resmi Desa Sukamaju.', PengaturanDesa::ambil()->deskripsiMeta());
    }

    public function test_meta_description_kosong_memakai_sejarah(): void
    {
        $this->simpan('profil', ['sejarah' => 'Desa ini berdiri pada tahun 1952 di lereng gunung.']);

        $this->assertStringContainsString('1952', PengaturanDesa::ambil()->deskripsiMeta());
    }

    public function test_meta_description_selalu_dipotong_di_bawah_batas_wajar(): void
    {
        $this->simpan('profil', ['sejarah' => str_repeat('sejarah panjang ', 60)]);

        $this->assertLessThanOrEqual(160, mb_strlen(PengaturanDesa::ambil()->deskripsiMeta()));
    }

    // ------------------------------------------------------------------
    // Cache & tampilan
    // ------------------------------------------------------------------

    public function test_cache_disegarkan_setelah_disimpan(): void
    {
        PengaturanDesa::ambil();

        $this->simpan('identitas', ['nama_desa' => 'Desa Berubah']);

        $this->assertSame('Desa Berubah', PengaturanDesa::ambil()->nama_desa);
    }

    public function test_nama_desa_muncul_di_judul_halaman_admin(): void
    {
        $this->simpan('identitas', ['nama_desa' => 'Desa Sukamaju']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<title>Dashboard — Desa Sukamaju</title>', false);
    }

    public function test_halaman_pengaturan_dapat_dibuka(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.pengaturan'))
            ->assertOk()
            ->assertSee('Identitas Desa')
            ->assertSee('Visi, Misi &amp; Sejarah', false)
            ->assertSee('Kontak &amp; Media Sosial', false);
    }

    public function test_url_logo_kembali_ke_placeholder_bila_kosong(): void
    {
        $this->assertStringContainsString('placeholder', PengaturanDesa::ambil()->urlLogo());
    }
}
