<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\PotensiDesa;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dialog konfirmasi panel admin.
 *
 * Seluruh aksi yang mengubah data harus melewati pusat dialog, bukan
 * confirm() bawaan peramban. Dialog bawaan tidak bisa memuat penjelasan
 * panjang, tidak bisa menuntut admin mengetik ulang nama sebagai pengaman,
 * dan tampil berbeda-beda di tiap peramban.
 */
class DialogKonfirmasiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);
        $this->wilayah = Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001']);
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function warga(array $atribut = []): Penduduk
    {
        return Penduduk::create(array_merge([
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1990-05-17',
            'jenis_kelamin' => 'L',
            'wilayah_id' => $this->wilayah->id,
            'jenis_masuk' => 'pendataan_awal',
            'tanggal_masuk' => now()->subYear(),
        ], $atribut));
    }

    private function warProsesArsip(array $atribut = []): Penduduk
    {
        $warga = $this->warga($atribut);
        $warga->fill([
            'alasan_keluar' => $atribut['alasan_keluar'] ?? 'duplikat',
            'tanggal_keluar' => $atribut['tanggal_keluar'] ?? now(),
        ])->save();
        $warga->delete();

        return $warga->fresh();
    }

    // ------------------------------------------------------------------
    // Tidak ada dialog bawaan peramban
    // ------------------------------------------------------------------

    public function test_tidak_ada_confirm_bawaan_peramban_di_halaman_admin(): void
    {
        $this->warga();
        PotensiDesa::create(['nama' => 'Keripik', 'kategori' => 'umkm', 'tampil' => true]);

        foreach ([
            'admin.penduduk.index',
            'admin.penduduk.arsip',
            'admin.wilayah.index',
            'admin.pekerjaan.index',
            'admin.potensi.index',
            'admin.organisasi.index',
            'admin.pengurus.index',
            'admin.berita.index',
            'admin.acara.index',
            'admin.rekap-kk.index',
        ] as $rute) {
            $html = $this->sebagaiAdmin()->get(route($rute))->assertOk()->getContent();

            $this->assertStringNotContainsString(
                'return confirm(',
                $html,
                "Halaman {$rute} masih memakai confirm() bawaan peramban.",
            );
        }
    }

    public function test_pusat_dialog_tersedia_di_setiap_halaman_admin(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="pusatDialog"', false)
            ->assertSee('id="dialogSetuju"', false)
            ->assertSee('id="dialogInputKetik"', false);
    }

    // ------------------------------------------------------------------
    // Konfirmasi pada aksi yang mengubah data
    // ------------------------------------------------------------------

    public function test_aksi_hapus_memakai_atribut_konfirmasi(): void
    {
        PotensiDesa::create(['nama' => 'Keripik Bu Sari', 'kategori' => 'umkm', 'tampil' => true]);

        $this->sebagaiAdmin()
            ->get(route('admin.potensi.index'))
            ->assertOk()
            ->assertSee('data-konfirmasi', false)
            ->assertSee('data-bahaya', false)
            ->assertSee('Hapus potensi desa?', false);
    }

    public function test_ekspor_memakai_konfirmasi(): void
    {
        $this->warga();

        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.index'))
            ->assertOk()
            ->assertSee('data-konfirmasi', false)
            ->assertSee('Unduh berkas XLSX?', false);
    }

    public function test_impor_memakai_konfirmasi_di_kedua_langkah(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.impor'))
            ->assertOk()
            ->assertSee('data-konfirmasi', false)
            ->assertSee('Periksa berkas ini?', false);
    }

    public function test_ganti_kata_sandi_memakai_konfirmasi(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.kata-sandi'))
            ->assertOk()
            ->assertSee('data-konfirmasi', false)
            ->assertSee('Ganti kata sandi?', false);
    }

    // ------------------------------------------------------------------
    // Dirty tracking
    // ------------------------------------------------------------------

    public function test_formulir_isian_ditandai_untuk_dirty_tracking(): void
    {
        $warga = $this->warga();

        foreach ([
            route('admin.penduduk.create'),
            route('admin.penduduk.edit', $warga),
            route('admin.potensi.create'),
            route('admin.berita.create'),
            route('admin.pengaturan'),
            route('admin.rekap-kk.index'),
        ] as $url) {
            $this->sebagaiAdmin()
                ->get($url)
                ->assertOk()
                ->assertSee('data-lacak-perubahan', false);
        }
    }

    // ------------------------------------------------------------------
    // Pesan server dikirim sebagai data dialog
    // ------------------------------------------------------------------

    public function test_pesan_berhasil_dikirim_sebagai_data_dialog(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.pekerjaan.store'), ['nama' => 'Petani']);

        $this->sebagaiAdmin()
            ->get(route('admin.pekerjaan.index'))
            ->assertOk()
            ->assertSee('id="pesanServer"', false);
    }

    public function test_kesalahan_validasi_dikirim_sebagai_data_dialog(): void
    {
        $this->sebagaiAdmin()
            ->from(route('admin.penduduk.create'))
            ->post(route('admin.penduduk.store'), []);

        $html = $this->sebagaiAdmin()
            ->get(route('admin.penduduk.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="pesanServer"', $html);
        $this->assertStringContainsString('wajib diisi', $html);
    }

    public function test_pesan_tetap_terbaca_tanpa_javascript(): void
    {
        // Bila JavaScript gagal dimuat, admin menekan Simpan lalu tidak
        // melihat apa pun tanpa cadangan ini.
        $this->sebagaiAdmin()->post(route('admin.pekerjaan.store'), ['nama' => 'Petani']);

        $this->sebagaiAdmin()
            ->get(route('admin.pekerjaan.index'))
            ->assertOk()
            ->assertSee('<noscript>', false);
    }

    // ==================================================================
    // Hapus permanen warga
    // ==================================================================

    public function test_tombol_hapus_permanen_hanya_ada_di_halaman_arsip(): void
    {
        $this->warga();
        $this->warProsesArsip(['nama_lengkap' => 'Sudah Diarsip']);

        // Daftar warga aktif hanya menawarkan pengarsipan.
        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.index'))
            ->assertOk()
            ->assertDontSee('Hapus permanen dari database?', false);

        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.arsip'))
            ->assertOk()
            ->assertSee('Hapus permanen dari database?', false)
            ->assertSee('data-ketik-field="konfirmasi_nama"', false);
    }

    public function test_warga_terarsip_bisa_dihapus_permanen(): void
    {
        $warga = $this->warProsesArsip();

        $this->sebagaiAdmin()
            ->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
                'konfirmasi_nama' => 'Budi Santoso',
            ])
            ->assertRedirect(route('admin.penduduk.arsip'))
            ->assertSessionHas('status');

        $this->assertSame(0, Penduduk::withTrashed()->count());
    }

    public function test_nama_yang_tidak_cocok_membatalkan_penghapusan(): void
    {
        $warga = $this->warProsesArsip();

        $this->sebagaiAdmin()
            ->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
                'konfirmasi_nama' => 'Budi Salah Ketik',
            ])
            ->assertSessionHas('galat');

        $this->assertSame(1, Penduduk::withTrashed()->count());
    }

    public function test_tanpa_konfirmasi_nama_penghapusan_ditolak(): void
    {
        // Pengaman yang hanya hidup di peramban bukan pengaman: server harus
        // menolak permintaan yang dikirim langsung.
        $warga = $this->warProsesArsip();

        $this->sebagaiAdmin()
            ->delete(route('admin.penduduk.hapus-permanen', $warga->id), [])
            ->assertSessionHasErrors('konfirmasi_nama');

        $this->assertSame(1, Penduduk::withTrashed()->count());
    }

    public function test_perbedaan_spasi_dan_huruf_besar_tetap_diterima(): void
    {
        $warga = $this->warProsesArsip();

        $this->sebagaiAdmin()
            ->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
                'konfirmasi_nama' => '  budi   santoso ',
            ])
            ->assertSessionHas('status');

        $this->assertSame(0, Penduduk::withTrashed()->count());
    }

    public function test_warga_aktif_tidak_bisa_dihapus_permanen(): void
    {
        // Warga harus diarsipkan lebih dulu, supaya tidak ada data yang lenyap
        // tanpa pernah tercatat alasan keluarnya.
        $warga = $this->warga();

        $this->sebagaiAdmin()
            ->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
                'konfirmasi_nama' => 'Budi Santoso',
            ])
            ->assertNotFound();

        $this->assertSame(1, Penduduk::count());
    }

    public function test_menghapus_warga_meninggal_memberi_tahu_dampak_statistiknya(): void
    {
        $tahun = now()->year;
        $warga = $this->warProsesArsip([
            'alasan_keluar' => 'meninggal',
            'tanggal_keluar' => now(),
        ]);

        $this->assertSame(1, Penduduk::query()->meninggalPadaTahun($tahun)->count());

        $this->sebagaiAdmin()->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
            'konfirmasi_nama' => 'Budi Santoso',
        ]);

        // Angka kematian tahun itu benar-benar berubah, dan admin diberi tahu.
        $this->assertSame(0, Penduduk::query()->meninggalPadaTahun($tahun)->count());
        $this->assertStringContainsString("kematian tahun {$tahun}", session('status'));
    }

    public function test_menghapus_data_duplikat_tidak_mengubah_statistik(): void
    {
        $warga = $this->warProsesArsip(['alasan_keluar' => 'duplikat']);

        $this->sebagaiAdmin()->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
            'konfirmasi_nama' => 'Budi Santoso',
        ]);

        $this->assertStringNotContainsString('kematian', session('status'));
    }

    public function test_dialog_memperingatkan_dampak_statistik_sebelum_menghapus(): void
    {
        $this->warProsesArsip([
            'nama_lengkap' => 'Almarhum Warga',
            'alasan_keluar' => 'meninggal',
            'tanggal_keluar' => now(),
        ]);

        $this->sebagaiAdmin()
            ->get(route('admin.penduduk.arsip'))
            ->assertOk()
            ->assertSee('angka kematian tahun '.now()->year.' berkurang satu', false);
    }

    public function test_tamu_tidak_bisa_menghapus_permanen(): void
    {
        $warga = $this->warProsesArsip();

        $this->delete(route('admin.penduduk.hapus-permanen', $warga->id), [
            'konfirmasi_nama' => 'Budi Santoso',
        ])->assertRedirect(route('admin.login'));

        $this->assertSame(1, Penduduk::withTrashed()->count());
    }
}
