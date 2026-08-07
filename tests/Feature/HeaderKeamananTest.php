<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Header keamanan tingkat peramban.
 *
 * Aplikasi ini menyimpan data kependudukan satu desa, dan panelnya dibuka
 * dari jaringan apa adanya di kantor desa. Perlindungan yang bisa diminta
 * langsung ke peramban — jangan tebak tipe berkas, jangan biarkan halaman ini
 * dibingkai situs lain, jangan kirim alamat lengkap ke pihak ketiga — tidak
 * menuntut apa pun dari perangkat kerasnya.
 */
class HeaderKeamananTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_publik_mengirim_header_keamanan(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_halaman_admin_mengirim_header_keamanan(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_halaman_publik_mengirim_content_security_policy(): void
    {
        $csp = $this->cspDenganHot(null);

        $this->assertNotNull($csp);

        // Direktif yang benar-benar mengunci, terlepas dari longgarnya
        // script-src (lihat catatan di TambahHeaderKeamanan).
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
    }

    public function test_csp_masih_mengizinkan_peta_google_disematkan(): void
    {
        $csp = $this->cspDenganHot(null);

        $this->assertMatchesRegularExpression(
            '/frame-src[^;]*https:\/\/www\.google\.com/',
            $csp,
        );
    }

    public function test_csp_mengizinkan_gambar_dari_disk_unggahan(): void
    {
        config()->set('unggahan.disk', 'r2');
        config()->set('filesystems.disks.r2.url', 'https://berkas.contoh.test/desa');

        $csp = $this->cspDenganHot(null);

        $this->assertMatchesRegularExpression(
            '/img-src[^;]*https:\/\/berkas\.contoh\.test/',
            $csp,
        );
    }

    public function test_hsts_tidak_dikirim_lewat_http_biasa(): void
    {
        $this->get('http://localhost/')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_dikirim_lewat_https(): void
    {
        $this->get('https://localhost/')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    // ------------------------------------------------------------------
    // Server pengembangan Vite
    //
    // Vite bisa mengikat ke loopback IPv6, sehingga public/hot berisi
    // http://[::1]:5173. Tata bahasa CSP tidak mengenal literal IPv6 dalam
    // kurung siku: peramban membuang sumber itu sebagai tidak sah, lalu
    // memblokir CSS dan JS yang datang darinya — halaman tampil sebagai HTML
    // polos tanpa gaya sama sekali.
    // ------------------------------------------------------------------

    public function test_asal_vite_ipv6_tidak_pernah_masuk_ke_csp(): void
    {
        $csp = $this->cspDenganHot('http://[::1]:5173');

        if ($csp !== null) {
            $this->assertStringNotContainsString('[::1]', $csp);
        }

        $this->assertTrue(true);
    }

    public function test_halaman_tetap_bergaya_saat_vite_mengikat_ipv6(): void
    {
        // Bila asal dev tidak bisa dinyatakan dalam CSP, lebih baik tidak
        // mengirim CSP sama sekali daripada mengirim kebijakan yang memblokir
        // seluruh gaya halaman.
        $this->assertNull(
            $this->cspDenganHot('http://[::1]:5173'),
            'CSP yang tidak bisa mengizinkan asal dev seharusnya tidak dikirim.',
        );
    }

    public function test_asal_vite_ipv4_tetap_diizinkan(): void
    {
        $csp = $this->cspDenganHot('http://127.0.0.1:5173');

        $this->assertNotNull($csp);
        $this->assertStringContainsString('http://127.0.0.1:5173', $csp);
        $this->assertStringContainsString('ws://127.0.0.1:5173', $csp);
    }

    /**
     * Menjalankan satu permintaan pada keadaan Vite tertentu, lalu
     * mengembalikan header CSP-nya. `null` berarti `npm run dev` tidak
     * sedang berjalan — keadaan yang sama dengan produksi.
     *
     * Keadaan public/hot ditetapkan secara eksplisit, bukan diwarisi dari
     * mesin yang menjalankan tes: hasil tes tidak boleh berbeda hanya karena
     * seorang pengembang kebetulan sedang menyalakan server Vite. Isi
     * semulanya dipulihkan setelahnya, karena berkas itu milik Vite.
     */
    private function cspDenganHot(?string $asal): ?string
    {
        $berkas = public_path('hot');
        $adaSebelumnya = is_file($berkas);
        $isiSemula = $adaSebelumnya ? file_get_contents($berkas) : null;

        if ($asal === null) {
            @unlink($berkas);
        } else {
            file_put_contents($berkas, $asal);
        }

        try {
            return $this->get('/')->assertOk()->headers->get('Content-Security-Policy');
        } finally {
            if ($adaSebelumnya) {
                file_put_contents($berkas, $isiSemula);
            } else {
                @unlink($berkas);
            }
        }
    }

    // ------------------------------------------------------------------
    // Cookie sesi
    //
    // Tanpa flag Secure, cookie sesi admin ikut terkirim pada permintaan HTTP
    // biasa — termasuk saat seseorang mengetik alamat tanpa https di jaringan
    // kantor desa. Nilainya cukup untuk mengambil alih sesi yang sedang
    // berjalan, jadi bawaannya harus aman di produksi, bukan menunggu
    // seseorang ingat menyalakannya.
    // ------------------------------------------------------------------

    public function test_cookie_sesi_otomatis_secure_di_produksi(): void
    {
        $this->assertTrue($this->konfigurasiSesiDengan(['APP_ENV' => 'production'])['secure']);
    }

    public function test_cookie_sesi_tidak_secure_di_luar_produksi(): void
    {
        $this->assertFalse($this->konfigurasiSesiDengan(['APP_ENV' => 'local'])['secure']);
    }

    public function test_pengaturan_eksplisit_tetap_menang_atas_bawaan(): void
    {
        $konfigurasi = $this->konfigurasiSesiDengan([
            'APP_ENV' => 'production',
            'SESSION_SECURE_COOKIE' => 'false',
        ]);

        $this->assertFalse($konfigurasi['secure']);
    }

    /**
     * Membaca ulang config/session.php dengan variabel lingkungan tertentu,
     * lalu memulihkan keadaan semula.
     *
     * @param  array<string, string>  $lingkungan
     * @return array<string, mixed>
     */
    private function konfigurasiSesiDengan(array $lingkungan): array
    {
        $semula = [];

        foreach ($lingkungan as $kunci => $nilai) {
            $semula[$kunci] = [$_ENV[$kunci] ?? null, $_SERVER[$kunci] ?? null];
            $_ENV[$kunci] = $nilai;
            $_SERVER[$kunci] = $nilai;
        }

        try {
            return require base_path('config/session.php');
        } finally {
            foreach ($semula as $kunci => [$env, $server]) {
                if ($env === null) {
                    unset($_ENV[$kunci]);
                } else {
                    $_ENV[$kunci] = $env;
                }

                if ($server === null) {
                    unset($_SERVER[$kunci]);
                } else {
                    $_SERVER[$kunci] = $server;
                }
            }
        }
    }

    /**
     * Berkas unduhan tetap harus lewat tanpa rusak. PreventBackHistory pernah
     * memecahkan seluruh unduhan panel admin karena memakai withHeaders()
     * pada StreamedResponse; kekeliruan yang sama tidak boleh terulang di
     * middleware header keamanan.
     */
    public function test_unduhan_tidak_rusak_oleh_header_keamanan(): void
    {
        $admin = \App\Models\Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/penduduk/impor/contoh')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
