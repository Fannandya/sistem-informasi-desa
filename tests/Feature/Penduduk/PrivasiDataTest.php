<?php

namespace Tests\Feature\Penduduk;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PRD 2.4 — larangan data privasi.
 *
 * Pengujian ini adalah pagar permanen, bukan sekadar pemeriksaan sekali
 * jalan. Larangan yang hanya ditulis di dokumen akan bocor cepat atau lambat:
 * seseorang menambahkan kolom NIK "supaya lebih lengkap", dan tidak ada yang
 * menyadarinya sampai data sudah terlanjur terkumpul.
 */
class PrivasiDataTest extends TestCase
{
    use RefreshDatabase;

    /** Pola nama kolom yang dilarang ada pada tabel penduduk. */
    public static function kolomTerlarangProvider(): array
    {
        return [
            'NIK' => ['nik'],
            'nomor induk kependudukan' => ['nomor_induk'],
            'nomor KK' => ['nomor_kk'],
            'kartu keluarga' => ['kartu_keluarga'],
            'nomor telepon' => ['telepon'],
            'nomor HP' => ['no_hp'],
            'WhatsApp' => ['whatsapp'],
            'alamat rumah detail' => ['alamat'],
            'agama' => ['agama'],
            'penghasilan' => ['penghasilan'],
            'golongan darah' => ['golongan_darah'],
        ];
    }

    #[DataProvider('kolomTerlarangProvider')]
    public function test_tabel_penduduk_tidak_punya_kolom_terlarang(string $kolom): void
    {
        $this->assertFalse(
            Schema::hasColumn('penduduk', $kolom),
            "Kolom \"{$kolom}\" dilarang ada pada tabel penduduk (PRD 2.4).",
        );
    }

    public function test_kolom_penduduk_persis_sesuai_yang_diizinkan(): void
    {
        // Daftar putih, bukan daftar hitam. Kolom baru apa pun akan membuat
        // pengujian ini gagal, sehingga penambahannya harus disengaja dan
        // ditinjau ulang terhadap PRD 2.4.
        $diizinkan = [
            'id',
            'nama_lengkap',
            'tanggal_lahir',
            'jenis_kelamin',
            'wilayah_id',
            'pekerjaan_id',
            'agama_id',
            'pendidikan',
            'status_perkawinan',
            'jenis_masuk',
            'tanggal_masuk',
            'alasan_keluar',
            'tanggal_keluar',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $sebenarnya = Schema::getColumnListing('penduduk');

        sort($diizinkan);
        sort($sebenarnya);

        $this->assertSame(
            $diizinkan,
            $sebenarnya,
            'Kolom tabel penduduk berubah. Periksa ulang terhadap PRD 2.4 sebelum menyesuaikan daftar ini.',
        );
    }

    public function test_umur_tidak_disimpan_sebagai_kolom(): void
    {
        // PRD 2.4: umur dihitung dari tanggal lahir, tidak pernah disimpan —
        // angka yang disimpan akan basi tanpa ada yang menyadarinya.
        $this->assertFalse(Schema::hasColumn('penduduk', 'umur'));
    }

    public function test_formulir_tambah_warga_tidak_memuat_kolom_terlarang(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $html = $this->actingAs($admin, 'admin')
            ->get(route('admin.penduduk.create'))
            ->assertOk()
            ->getContent();

        foreach (['name="nik"', 'name="nomor_kk"', 'name="telepon"', 'name="alamat"', 'name="agama"'] as $pola) {
            $this->assertStringNotContainsString($pola, $html, "Formulir memuat isian terlarang: {$pola}");
        }
    }

    public function test_kolom_terlarang_diabaikan_meski_dikirim_paksa(): void
    {
        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
        $pekerjaan = RefPekerjaan::create(['nama' => 'Petani']);

        $this->actingAs($admin, 'admin')->post(route('admin.penduduk.store'), [
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1990-05-17',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id,
            'pekerjaan_id' => $pekerjaan->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->toDateString(),
            // Kiriman jahat/iseng — tidak boleh tersimpan di mana pun.
            'nik' => '3204010101900001',
            'nomor_kk' => '3204011234567890',
            'telepon' => '081234567890',
        ]);

        $warga = Penduduk::first();

        $this->assertNotNull($warga);
        $this->assertArrayNotHasKey('nik', $warga->getAttributes());
        $this->assertArrayNotHasKey('nomor_kk', $warga->getAttributes());
        $this->assertArrayNotHasKey('telepon', $warga->getAttributes());
    }
}
