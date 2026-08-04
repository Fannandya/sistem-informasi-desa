<?php

namespace Tests\Feature\Penduduk;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RekapKk;
use App\Models\Wilayah;
use App\Queries\RingkasanKk;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapKkTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    /** @var array<int, Wilayah> */
    private array $rt = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $this->rt = [
            Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '001', 'urutan' => 1]),
            Wilayah::create(['dusun' => 'Krajan', 'rw' => '001', 'rt' => '002', 'urutan' => 2]),
            Wilayah::create(['dusun' => 'Sukasari', 'rw' => '002', 'rt' => '001', 'urutan' => 3]),
        ];
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    /** @param array<int, int|string|null> $jumlah wilayah index => nilai */
    private function simpan(int $tahun, array $jumlah)
    {
        $isian = [];
        foreach ($jumlah as $index => $nilai) {
            $isian[$this->rt[$index]->id] = $nilai;
        }

        return $this->sebagaiAdmin()->post(route('admin.rekap-kk.simpan', $tahun), ['jumlah' => $isian]);
    }

    private function rekap(int $index, int $tahun, int $jumlah): RekapKk
    {
        return RekapKk::create([
            'wilayah_id' => $this->rt[$index]->id,
            'tahun' => $tahun,
            'jumlah_kk' => $jumlah,
        ]);
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_tamu_ditolak(): void
    {
        $this->get(route('admin.rekap-kk.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.rekap-kk.simpan', now()->year))->assertRedirect(route('admin.login'));
        $this->post(route('admin.rekap-kk.salin', now()->year))->assertRedirect(route('admin.login'));
    }

    public function test_halaman_dapat_dibuka_dan_menampilkan_seluruh_rt(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.rekap-kk.index'))
            ->assertOk()
            ->assertSee('Krajan')
            ->assertSee('Sukasari')
            ->assertSee('RT 001 / RW 001');
    }

    public function test_tahun_di_luar_rentang_wajar_ditolak(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.rekap-kk.simpan', now()->year + 5), ['jumlah' => []])
            ->assertNotFound();

        $this->sebagaiAdmin()
            ->post(route('admin.rekap-kk.simpan', 1900), ['jumlah' => []])
            ->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Penyimpanan
    // ------------------------------------------------------------------

    public function test_seluruh_isian_tersimpan_dalam_satu_aksi(): void
    {
        $this->simpan(now()->year, [0 => 42, 1 => 38, 2 => 51])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.rekap-kk.index', ['tahun' => now()->year]));

        $this->assertSame(3, RekapKk::count());
        $this->assertSame(131, (int) RekapKk::sum('jumlah_kk'));
    }

    public function test_penginput_tercatat(): void
    {
        $this->simpan(now()->year, [0 => 42]);

        $this->assertSame($this->admin->id, RekapKk::first()->admin_id);
    }

    public function test_menyimpan_ulang_memperbarui_bukan_menggandakan(): void
    {
        $this->simpan(now()->year, [0 => 42]);
        $this->simpan(now()->year, [0 => 45]);

        $this->assertSame(1, RekapKk::count());
        $this->assertSame(45, RekapKk::first()->jumlah_kk);
    }

    public function test_nol_disimpan_sebagai_angka_bukan_dianggap_kosong(): void
    {
        // Nol berarti "sudah diperiksa, memang tidak ada KK" — berbeda dari
        // belum diisi. Kalau tertukar, kelengkapan rekap jadi salah.
        $this->simpan(now()->year, [0 => 0]);

        $this->assertSame(1, RekapKk::count());
        $this->assertSame(0, RekapKk::first()->jumlah_kk);
        $this->assertSame(1, (new RingkasanKk(now()->year))->jumlahTerisi());
    }

    public function test_isian_yang_dikosongkan_dikembalikan_menjadi_belum_diisi(): void
    {
        $this->simpan(now()->year, [0 => 42, 1 => 38]);
        $this->assertSame(2, RekapKk::count());

        $this->simpan(now()->year, [0 => 42, 1 => '']);

        $this->assertSame(1, RekapKk::count());
        $this->assertSame(1, (new RingkasanKk(now()->year))->jumlahBelumDiisi() - 1);
    }

    public function test_angka_negatif_dan_bukan_bilangan_ditolak(): void
    {
        $this->simpan(now()->year, [0 => -5])->assertSessionHasErrors();
        $this->simpan(now()->year, [0 => 'seratus'])->assertSessionHasErrors();

        $this->assertSame(0, RekapKk::count());
    }

    public function test_satu_rt_hanya_boleh_punya_satu_angka_per_tahun(): void
    {
        $this->rekap(0, now()->year, 42);

        $this->expectException(QueryException::class);

        RekapKk::create([
            'wilayah_id' => $this->rt[0]->id,
            'tahun' => now()->year,
            'jumlah_kk' => 99,
        ]);
    }

    public function test_menyimpan_satu_tahun_tidak_mengubah_tahun_lain(): void
    {
        $this->simpan(now()->year - 1, [0 => 40, 1 => 30, 2 => 20]);
        $this->simpan(now()->year, [0 => 45]);

        $this->assertSame(90, (int) RekapKk::tahun(now()->year - 1)->sum('jumlah_kk'));
        $this->assertSame(45, (int) RekapKk::tahun(now()->year)->sum('jumlah_kk'));
    }

    public function test_wilayah_asing_diabaikan_meski_dikirim_paksa(): void
    {
        $this->sebagaiAdmin()->post(route('admin.rekap-kk.simpan', now()->year), [
            'jumlah' => [99999 => 100],
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, RekapKk::count());
    }

    // ------------------------------------------------------------------
    // Salin tahun sebelumnya
    // ------------------------------------------------------------------

    public function test_salin_memuat_angka_ke_formulir_tanpa_menyimpannya(): void
    {
        $this->simpan(now()->year - 1, [0 => 40, 1 => 30]);

        $this->sebagaiAdmin()
            ->post(route('admin.rekap-kk.salin', now()->year))
            ->assertRedirect(route('admin.rekap-kk.index', ['tahun' => now()->year]))
            ->assertSessionHas('status')
            ->assertSessionHasInput('jumlah');

        // Belum tersimpan — admin masih harus memeriksa lalu menekan Simpan.
        $this->assertSame(0, RekapKk::tahun(now()->year)->count());
    }

    public function test_salin_tanpa_data_tahun_sebelumnya_memberi_pesan(): void
    {
        $this->sebagaiAdmin()
            ->post(route('admin.rekap-kk.salin', now()->year))
            ->assertSessionHas('galat');
    }

    // ------------------------------------------------------------------
    // RingkasanKk — PRD 2.9
    // ------------------------------------------------------------------

    public function test_tahun_acuan_adalah_tahun_terakhir_yang_punya_data(): void
    {
        $this->rekap(0, now()->year - 2, 10);
        $this->rekap(1, now()->year - 1, 20);

        // Bukan tahun berjalan: awal tahun sebelum admin sempat merekap,
        // memakai tahun berjalan akan menampilkan "0 KK".
        $this->assertSame(now()->year - 1, (new RingkasanKk())->tahunAcuan());
        $this->assertSame(20, (new RingkasanKk())->total());
    }

    public function test_tanpa_rekap_sama_sekali_ditandai_tidak_ada_data(): void
    {
        $ringkasan = new RingkasanKk();

        $this->assertFalse($ringkasan->adaData());
        $this->assertNull($ringkasan->tahunAcuan());
        $this->assertSame(0, $ringkasan->total());
        $this->assertNull($ringkasan->rataJiwaPerKk());
        $this->assertFalse($ringkasan->lengkap());
    }

    public function test_kelengkapan_dihitung_dari_jumlah_rt_terdaftar(): void
    {
        $this->rekap(0, now()->year, 40);
        $this->rekap(1, now()->year, 30);

        $ringkasan = new RingkasanKk(now()->year);

        $this->assertSame(3, $ringkasan->totalWilayah());
        $this->assertSame(2, $ringkasan->jumlahTerisi());
        $this->assertSame(1, $ringkasan->jumlahBelumDiisi());
        $this->assertFalse($ringkasan->lengkap());
        $this->assertStringContainsString('2 dari 3 RT', $ringkasan->keteranganKelengkapan());

        $this->rekap(2, now()->year, 20);
        $this->assertTrue((new RingkasanKk(now()->year))->lengkap());
    }

    public function test_rt_baru_membuat_rekap_menjadi_belum_lengkap(): void
    {
        // RT yang ditambahkan setelah rekap dibuat harus otomatis terhitung
        // sebagai belum diisi, bukan diam-diam dianggap lengkap.
        foreach ([0, 1, 2] as $i) {
            $this->rekap($i, now()->year, 30);
        }
        $this->assertTrue((new RingkasanKk(now()->year))->lengkap());

        Wilayah::create(['dusun' => 'Cimekar', 'rw' => '003', 'rt' => '001']);

        $this->assertFalse((new RingkasanKk(now()->year))->lengkap());
        $this->assertSame(1, (new RingkasanKk(now()->year))->jumlahBelumDiisi());
    }

    public function test_rata_jiwa_per_kk_dihitung_dari_warga_aktif(): void
    {
        foreach (range(1, 100) as $i) {
            Penduduk::create([
                'nama_lengkap' => "Warga {$i}",
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'wilayah_id' => $this->rt[0]->id,
                'jenis_masuk' => 'pendataan_awal',
                'tanggal_masuk' => now(),
            ]);
        }

        $this->rekap(0, now()->year, 25);

        $this->assertSame(4.0, (new RingkasanKk(now()->year))->rataJiwaPerKk());
    }

    public function test_subtotal_per_dusun_menghitung_rt_terisi_saja(): void
    {
        $this->rekap(0, now()->year, 40); // Krajan RT 001
        $this->rekap(2, now()->year, 20); // Sukasari RT 001

        $perDusun = (new RingkasanKk(now()->year))->perDusun()->keyBy('dusun');

        $this->assertSame(40, $perDusun['Krajan']['jumlah_kk']);
        $this->assertSame(1, $perDusun['Krajan']['terisi']);
        $this->assertSame(2, $perDusun['Krajan']['total_rt']);

        $this->assertSame(20, $perDusun['Sukasari']['jumlah_kk']);
        $this->assertSame(1, $perDusun['Sukasari']['total_rt']);
    }

    public function test_menghapus_wilayah_ikut_menghapus_rekapnya(): void
    {
        $this->rekap(0, now()->year, 40);

        $this->rt[0]->delete();

        $this->assertSame(0, RekapKk::count());
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    public function test_dashboard_menampilkan_jumlah_kk_dan_tahunnya(): void
    {
        $this->rekap(0, now()->year, 40);
        $this->rekap(1, now()->year, 30);
        $this->rekap(2, now()->year, 20);

        $this->sebagaiAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Jumlah KK')
            ->assertSee('90')
            ->assertSee('data tahun '.now()->year);
    }

    public function test_dashboard_menandai_rekap_yang_belum_lengkap(): void
    {
        $this->rekap(0, now()->year, 40);

        $this->sebagaiAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('2 RT belum diisi');
    }

    public function test_dashboard_tanpa_rekap_mengajak_mengisi(): void
    {
        $this->sebagaiAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Isi rekap KK');
    }
}
