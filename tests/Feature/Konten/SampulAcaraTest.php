<?php

namespace Tests\Feature\Konten;

use App\Models\AcaraDesa;
use App\Models\Admin;
use App\Models\FotoAcara;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daftar galeri hanya menampilkan satu foto sampul per acara, tetapi
 * sebelumnya memuat seluruh foto setiap acara ke memori untuk memilih satu
 * di antaranya. Satu halaman galeri berisi 12 acara; bila tiap acara punya
 * 50 foto, 600 baris dihidrasi menjadi model hanya untuk dibuang 588 di
 * antaranya.
 */
class SampulAcaraTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
    }

    private function acara(string $judul = 'Kerja Bakti', array $atribut = []): AcaraDesa
    {
        return AcaraDesa::create(array_merge([
            'judul' => $judul,
            'tanggal_acara' => now()->subWeek(),
            'status' => 'terbit',
        ], $atribut));
    }

    private function foto(AcaraDesa $acara, int $urutan, bool $sampul = false): FotoAcara
    {
        return FotoAcara::create([
            'acara_id' => $acara->id,
            'path' => "acara/{$acara->id}-{$urutan}.webp",
            'urutan' => $urutan,
            'sampul' => $sampul,
        ]);
    }

    // ------------------------------------------------------------------
    // Pemilihan sampul — harus sama persis dengan perilaku sebelumnya
    // ------------------------------------------------------------------

    public function test_foto_bertanda_sampul_yang_dipilih(): void
    {
        $acara = $this->acara();
        $this->foto($acara, 1);
        $ditandai = $this->foto($acara, 2, sampul: true);
        $this->foto($acara, 3);

        $this->assertSame($ditandai->id, $acara->fresh()->fotoSampul()?->id);
    }

    public function test_tanpa_penanda_foto_dengan_urutan_terkecil_yang_dipilih(): void
    {
        $acara = $this->acara();
        $this->foto($acara, 3);
        $pertama = $this->foto($acara, 1);
        $this->foto($acara, 2);

        $this->assertSame($pertama->id, $acara->fresh()->fotoSampul()?->id);
    }

    public function test_acara_tanpa_foto_tidak_punya_sampul(): void
    {
        $this->assertNull($this->acara()->fotoSampul());
    }

    // ------------------------------------------------------------------
    // Daftar tidak boleh menarik seluruh foto
    // ------------------------------------------------------------------

    /** @return array<int, AcaraDesa> */
    private function acaraDenganBanyakFoto(int $jumlahAcara, int $fotoPerAcara): array
    {
        $hasil = [];

        for ($i = 0; $i < $jumlahAcara; $i++) {
            $acara = $this->acara("Acara {$i}");

            for ($j = 1; $j <= $fotoPerAcara; $j++) {
                $this->foto($acara, $j, sampul: $j === 1);
            }

            $hasil[] = $acara;
        }

        return $hasil;
    }

    public function test_daftar_galeri_tidak_memuat_seluruh_foto(): void
    {
        $this->acaraDenganBanyakFoto(3, 20);

        $daftar = $this->get('/galeri')->assertOk()->viewData('daftar');

        $this->assertFalse(
            $daftar->first()->relationLoaded('foto'),
            'Daftar galeri seharusnya hanya memuat foto sampul, bukan seluruh foto.',
        );
    }

    public function test_beranda_tidak_memuat_seluruh_foto_acara(): void
    {
        $this->acaraDenganBanyakFoto(3, 20);

        $acara = $this->get('/')->assertOk()->viewData('acara');

        $this->assertFalse($acara->first()->relationLoaded('foto'));
    }

    public function test_daftar_acara_admin_tidak_memuat_seluruh_foto(): void
    {
        $this->acaraDenganBanyakFoto(3, 20);

        $daftar = $this->actingAs($this->admin, 'admin')
            ->get('/admin/acara')
            ->assertOk()
            ->viewData('daftar');

        $this->assertFalse($daftar->first()->relationLoaded('foto'));
    }

    /**
     * Ongkos sebenarnya bukan jumlah kueri — eager loading tetap satu kueri
     * berapa pun fotonya — melainkan baris yang dihidrasi menjadi model.
     * Itulah yang tumbuh mengikuti banyaknya foto yang diunggah aparat desa.
     */
    public function test_galeri_hanya_menghidrasi_satu_foto_per_acara(): void
    {
        $this->acaraDenganBanyakFoto(3, 20);

        $this->assertSame(3, $this->jumlahFotoDihidrasi(fn () => $this->get('/galeri')->assertOk()));
    }

    public function test_beranda_hanya_menghidrasi_satu_foto_per_acara(): void
    {
        $this->acaraDenganBanyakFoto(3, 20);

        $this->assertSame(3, $this->jumlahFotoDihidrasi(fn () => $this->get('/')->assertOk()));
    }

    /** Banyaknya baris foto_acara yang berubah menjadi model selama tindakan. */
    private function jumlahFotoDihidrasi(callable $tindakan): int
    {
        $jumlah = 0;

        FotoAcara::retrieved(function () use (&$jumlah): void {
            $jumlah++;
        });

        $tindakan();

        return $jumlah;
    }

    /**
     * Halaman detail justru memang membutuhkan seluruh fotonya — di sanalah
     * galerinya ditampilkan.
     */
    public function test_halaman_detail_tetap_memuat_seluruh_foto(): void
    {
        [$acara] = $this->acaraDenganBanyakFoto(1, 5);

        $dimuat = $this->get('/galeri/'.$acara->slug)->assertOk()->viewData('acara');

        $this->assertCount(5, $dimuat->foto);
    }

    private function jumlahKueri(callable $tindakan): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $tindakan();

        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $jumlah;
    }
}
