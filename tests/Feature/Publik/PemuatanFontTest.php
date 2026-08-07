<?php

namespace Tests\Feature\Publik;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Setiap <link rel="stylesheet"> ke Google Fonts menahan render halaman
 * sampai balasannya tiba. Halaman publik meminta tiga berkas terpisah dari
 * host yang sama, padahal Google Fonts bisa melayani semuanya sekaligus.
 * Di koneksi seluler desa, dua permintaan yang dihemat itu terasa.
 */
class PemuatanFontTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string> */
    private function tautanFont(string $html): array
    {
        preg_match_all(
            '/<link[^>]+href="(https:\/\/fonts\.googleapis\.com\/css2[^"]*)"/',
            $html,
            $cocok,
        );

        return $cocok[1];
    }

    public function test_halaman_publik_meminta_font_dalam_satu_permintaan(): void
    {
        $tautan = $this->tautanFont($this->get('/')->assertOk()->getContent());

        $this->assertCount(1, $tautan, 'Font halaman publik seharusnya digabung menjadi satu permintaan.');
    }

    public function test_satu_permintaan_itu_tetap_memuat_seluruh_font_publik(): void
    {
        $tautan = $this->tautanFont($this->get('/')->getContent())[0] ?? '';
        $alamat = html_entity_decode($tautan);

        foreach (['Literata', 'Nunito+Sans', 'Material+Symbols+Outlined'] as $keluarga) {
            $this->assertStringContainsString($keluarga, $alamat);
        }

        $this->assertStringContainsString('display=swap', $alamat);
    }

    public function test_halaman_admin_meminta_font_dalam_satu_permintaan(): void
    {
        $admin = Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);

        $html = $this->actingAs($admin, 'admin')->get('/admin')->assertOk()->getContent();
        $tautan = $this->tautanFont($html);

        $this->assertCount(1, $tautan);

        $alamat = html_entity_decode($tautan[0]);
        $this->assertStringContainsString('IBM+Plex+Serif', $alamat);
        $this->assertStringContainsString('Material+Symbols+Outlined', $alamat);
    }

    /**
     * preconnect ke kedua host Google Fonts harus tetap ada: penggabungan
     * memangkas jumlah permintaan, bukan kebutuhan menyiapkan koneksinya
     * lebih awal.
     */
    public function test_preconnect_google_fonts_tetap_ada(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('rel="preconnect" href="https://fonts.googleapis.com"', $html);
        $this->assertStringContainsString('rel="preconnect" href="https://fonts.gstatic.com"', $html);
    }
}
