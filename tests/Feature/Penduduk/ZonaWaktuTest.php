<?php

namespace Tests\Feature\Penduduk;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Zona waktu aplikasi.
 *
 * Skeleton Laravel mengunci `config/app.php` ke 'UTC' dan tidak membaca
 * APP_TIMEZONE. Untuk desa di Indonesia itu keliru: antara pukul 00.00–07.00
 * WIB, "hari ini" menurut server masih tanggal kemarin, sehingga admin yang
 * memilih tanggal hari ini dari date picker ditolak dengan pesan
 * "tidak boleh melewati hari ini".
 *
 * Bug ini pernah benar-benar terjadi saat pengujian manual fitur 04.
 */
class ZonaWaktuTest extends TestCase
{
    use RefreshDatabase;

    public function test_zona_waktu_dibaca_dari_env_bukan_dikunci_utc(): void
    {
        $this->assertSame(
            env('APP_TIMEZONE', 'UTC'),
            config('app.timezone'),
            'config/app.php harus membaca APP_TIMEZONE, bukan mengunci nilainya.',
        );
    }

    public function test_tanggal_hari_ini_diterima_sebagai_tanggal_keluar(): void
    {
        config()->set('app.timezone', 'Asia/Jakarta');
        date_default_timezone_set('Asia/Jakarta');

        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);

        $warga = Penduduk::create([
            'nama_lengkap' => 'Warga Uji',
            'tanggal_lahir' => '1980-01-01',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $wilayah->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear()->toDateString(),
        ]);

        // Tanggal yang dilihat admin di date picker adalah tanggal WIB.
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.penduduk.arsipkan', $warga), [
                'alasan_keluar' => 'meninggal',
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($warga->fresh()->trashed());
    }
}
