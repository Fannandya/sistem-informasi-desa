<?php

namespace App\Queries;

use App\Enums\AlasanKeluar;
use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\KelompokUmur;
use App\Enums\Pendidikan;
use App\Enums\StatusPerkawinan;
use App\Models\Penduduk;
use App\Models\RekapKk;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

/**
 * Fitur 06 — perhitungan statistik demografi.
 *
 * Seluruh angka dihitung lewat agregasi SQL, bukan mengambil seluruh baris
 * lalu menghitungnya di PHP. Pada shared hosting dengan max_execution_time
 * 30 detik (PRD 8), memuat 5.000 warga ke memori untuk setiap grafik akan
 * membuat dashboard gagal dimuat.
 *
 * Kelas ini juga dipakai fitur 15 (statistik publik) melalui pembungkus yang
 * menerapkan aturan penyembunyian kelompok kecil, sehingga angka admin dan
 * publik berasal dari perhitungan yang sama.
 */
class StatistikPenduduk
{
    /** Berapa banyak pekerjaan teratas yang ditampilkan sebelum digabung. */
    private const PEKERJAAN_TERATAS = 10;

    public function totalWargaAktif(): int
    {
        return Penduduk::query()->aktif()->count();
    }

    // ------------------------------------------------------------------
    // Sebaran umur — PRD 2.8
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{kunci: string, label: string, rentang: string, jumlah: int, persen: float}>
     */
    public function sebaranUmur(): array
    {
        ['hitungan' => $hitungan, 'total' => $total] = $this->hitungSebaranUmur();

        $hasil = [];

        foreach (KelompokUmur::cases() as $kelompok) {
            $jumlah = $hitungan[$kelompok->value];

            // "Tidak Diketahui" hanya ditampilkan bila memang ada isinya,
            // supaya grafik desa yang datanya lengkap tidak terganggu
            // kategori kosong.
            if ($kelompok === KelompokUmur::TidakDiketahui && $jumlah === 0) {
                continue;
            }

            $hasil[] = [
                'kunci' => $kelompok->value,
                'label' => $kelompok->label(),
                'rentang' => $kelompok->rentang(),
                'jumlah' => $jumlah,
                'persen' => $this->persen($jumlah, $total),
            ];
        }

        return $hasil;
    }

    /**
     * Menghitung seluruh kelompok umur dalam satu kueri.
     *
     * Sebelumnya tiap kelompok dihitung dengan COUNT-nya sendiri: tujuh
     * pemindaian tabel penduduk untuk satu grafik. Batas tanggalnya diambil
     * dari KelompokUmur, sumber yang sama dengan penyaringan per kelompok.
     *
     * @return array{hitungan: array<string, int>, total: int}
     */
    private function hitungSebaranUmur(): array
    {
        $pilih = [];
        $binding = [];

        foreach (KelompokUmur::cases() as $kelompok) {
            $alias = $this->aliasKelompok($kelompok);
            $rentang = $kelompok->rentangTanggalLahir();

            if ($rentang === null) {
                $pilih[] = "SUM(CASE WHEN tanggal_lahir IS NULL THEN 1 ELSE 0 END) as {$alias}";

                continue;
            }

            // date() dipakai agar setara dengan whereDate() pada penyaringan
            // per kelompok: kolom tanggal disimpan beserta jamnya oleh
            // sebagian driver, dan '2007-08-07 00:00:00' <= '2007-08-07'
            // bernilai salah bila dibandingkan sebagai teks — tepat pada hari
            // ulang tahun, orangnya hilang dari kelompok mana pun.
            $syarat = ['tanggal_lahir IS NOT NULL', 'date(tanggal_lahir) <= ?'];
            $binding[] = $rentang['sampai'];

            if ($rentang['sesudah'] !== null) {
                $syarat[] = 'date(tanggal_lahir) > ?';
                $binding[] = $rentang['sesudah'];
            }

            $pilih[] = 'SUM(CASE WHEN '.implode(' AND ', $syarat).' THEN 1 ELSE 0 END) as '.$alias;
        }

        // Total dihitung terpisah dari penjumlahan kelompok, persis seperti
        // sebelumnya: warga dengan tanggal lahir di masa depan tidak masuk
        // kelompok mana pun, tetapi tetap warga.
        $pilih[] = 'COUNT(*) as total';

        $baris = Penduduk::query()->aktif()
            ->selectRaw(implode(', ', $pilih), $binding)
            ->first();

        $hitungan = [];

        foreach (KelompokUmur::cases() as $kelompok) {
            $hitungan[$kelompok->value] = (int) ($baris?->{$this->aliasKelompok($kelompok)} ?? 0);
        }

        return ['hitungan' => $hitungan, 'total' => (int) ($baris?->total ?? 0)];
    }

    private function aliasKelompok(KelompokUmur $kelompok): string
    {
        return 'umur_'.$kelompok->value;
    }

    // ------------------------------------------------------------------
    // Rasio gender
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{kunci: string, label: string, jumlah: int, persen: float}>
     */
    public function rasioGender(): array
    {
        $jumlah = Penduduk::query()->aktif()
            ->selectRaw('jenis_kelamin, COUNT(*) as total')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $total = (int) $jumlah->sum();

        return collect(JenisKelamin::cases())
            ->map(fn (JenisKelamin $jk) => [
                'kunci' => $jk->value,
                'label' => $jk->label(),
                'jumlah' => (int) ($jumlah[$jk->value] ?? 0),
                'persen' => $this->persen((int) ($jumlah[$jk->value] ?? 0), $total),
            ])
            ->all();
    }

    /** Lebar tiap kelompok umur pada piramida, dalam tahun. */
    private const LEBAR_PITA_PIRAMIDA = 5;

    /** Umur mulai kelompok terbuka terakhir ("100+"). */
    private const PITA_PIRAMIDA_TERAKHIR = 100;

    // ------------------------------------------------------------------
    // Piramida penduduk (umur 5 tahunan x gender) — terpisah dari
    // KelompokUmur, yang tetap dipakai grafik "Sebaran Kelompok Umur".
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, laki: int, perempuan: int}>
     */
    public function piramidaUmurGender(): array
    {
        $pilih = [];
        $binding = [];
        $hariIni = now();
        $pita = $this->pitaPiramida();

        foreach ($pita as $i => $p) {
            foreach (['L', 'P'] as $jk) {
                $alias = "pita_{$i}_{$jk}";
                $syarat = ['jenis_kelamin = ?', 'tanggal_lahir IS NOT NULL', 'date(tanggal_lahir) <= ?'];
                $binding[] = $jk;
                $binding[] = $hariIni->copy()->subYears($p['min'])->toDateString();

                if ($p['maks'] !== null) {
                    $syarat[] = 'date(tanggal_lahir) > ?';
                    $binding[] = $hariIni->copy()->subYears($p['maks'] + 1)->toDateString();
                }

                $pilih[] = 'SUM(CASE WHEN '.implode(' AND ', $syarat).' THEN 1 ELSE 0 END) as '.$alias;
            }
        }

        $baris = Penduduk::query()->aktif()->selectRaw(implode(', ', $pilih), $binding)->first();

        $hasil = [];

        foreach ($pita as $i => $p) {
            $hasil[] = [
                'label' => $p['label'],
                'laki' => (int) ($baris?->{"pita_{$i}_L"} ?? 0),
                'perempuan' => (int) ($baris?->{"pita_{$i}_P"} ?? 0),
            ];
        }

        return $hasil;
    }

    /** @return array<int, array{label: string, min: int, maks: int|null}> */
    private function pitaPiramida(): array
    {
        $pita = [];

        for ($mulai = 0; $mulai < self::PITA_PIRAMIDA_TERAKHIR; $mulai += self::LEBAR_PITA_PIRAMIDA) {
            $akhir = $mulai + self::LEBAR_PITA_PIRAMIDA - 1;
            $pita[] = ['label' => "{$mulai}–{$akhir}", 'min' => $mulai, 'maks' => $akhir];
        }

        $pita[] = ['label' => self::PITA_PIRAMIDA_TERAKHIR.'+', 'min' => self::PITA_PIRAMIDA_TERAKHIR, 'maks' => null];

        return $pita;
    }

    // ------------------------------------------------------------------
    // Mata pencaharian
    // ------------------------------------------------------------------

    /**
     * Sepuluh pekerjaan terbanyak; sisanya digabung menjadi "Lainnya".
     * Warga tanpa data pekerjaan tampil terpisah sebagai "Belum diisi",
     * supaya kekosongan data terlihat alih-alih tersembunyi.
     *
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function mataPencaharian(): array
    {
        $total = $this->totalWargaAktif();

        $perPekerjaan = Penduduk::query()->aktif()
            ->whereNotNull('pekerjaan_id')
            ->join('ref_pekerjaan', 'penduduk.pekerjaan_id', '=', 'ref_pekerjaan.id')
            ->selectRaw('ref_pekerjaan.nama as nama, COUNT(*) as total')
            ->groupBy('ref_pekerjaan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'nama');

        $teratas = $perPekerjaan->take(self::PEKERJAAN_TERATAS);
        $sisa = (int) $perPekerjaan->slice(self::PEKERJAAN_TERATAS)->sum();
        $belumDiisi = Penduduk::query()->aktif()->whereNull('pekerjaan_id')->count();

        $hasil = $teratas
            ->map(fn ($jumlah, $nama) => [
                'label' => $nama,
                'jumlah' => (int) $jumlah,
                'persen' => $this->persen((int) $jumlah, $total),
            ])
            ->values()
            ->all();

        if ($sisa > 0) {
            $hasil[] = ['label' => 'Lainnya', 'jumlah' => $sisa, 'persen' => $this->persen($sisa, $total)];
        }

        if ($belumDiisi > 0) {
            $hasil[] = ['label' => 'Belum diisi', 'jumlah' => $belumDiisi, 'persen' => $this->persen($belumDiisi, $total)];
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Pendidikan
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function pendidikan(): array
    {
        $total = $this->totalWargaAktif();

        $jumlah = Penduduk::query()->aktif()
            ->selectRaw('pendidikan, COUNT(*) as total')
            ->groupBy('pendidikan')
            ->pluck('total', 'pendidikan');

        $hasil = collect(Pendidikan::cases())
            ->map(fn (Pendidikan $p) => [
                'label' => $p->label(),
                'jumlah' => (int) ($jumlah[$p->value] ?? 0),
                'persen' => $this->persen((int) ($jumlah[$p->value] ?? 0), $total),
            ])
            ->filter(fn (array $baris) => $baris['jumlah'] > 0)
            ->values()
            ->all();

        $belumDiisi = (int) ($jumlah[''] ?? 0) + Penduduk::query()->aktif()->whereNull('pendidikan')->count();

        if ($belumDiisi > 0) {
            $hasil[] = ['label' => 'Belum diisi', 'jumlah' => $belumDiisi, 'persen' => $this->persen($belumDiisi, $total)];
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Agama
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function agama(): array
    {
        $total = $this->totalWargaAktif();

        $perAgama = Penduduk::query()->aktif()
            ->whereNotNull('agama_id')
            ->join('ref_agama', 'penduduk.agama_id', '=', 'ref_agama.id')
            ->selectRaw('ref_agama.nama as nama, COUNT(*) as total')
            ->groupBy('ref_agama.nama')
            ->orderByDesc('total')
            ->pluck('total', 'nama');

        $hasil = $perAgama
            ->map(fn ($jumlah, $nama) => [
                'label' => $nama,
                'jumlah' => (int) $jumlah,
                'persen' => $this->persen((int) $jumlah, $total),
            ])
            ->values()
            ->all();

        // Terpisah dari baris "Tidak Ingin Menyebutkan" (agama_id nyata):
        // ini murni warga yang belum pernah didata agamanya.
        $belumDiisi = Penduduk::query()->aktif()->whereNull('agama_id')->count();

        if ($belumDiisi > 0) {
            $hasil[] = ['label' => 'Belum diisi', 'jumlah' => $belumDiisi, 'persen' => $this->persen($belumDiisi, $total)];
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Status perkawinan
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function statusPerkawinan(): array
    {
        $total = $this->totalWargaAktif();

        $jumlah = Penduduk::query()->aktif()
            ->selectRaw('status_perkawinan, COUNT(*) as total')
            ->groupBy('status_perkawinan')
            ->pluck('total', 'status_perkawinan');

        $hasil = collect(StatusPerkawinan::cases())
            ->map(fn (StatusPerkawinan $s) => [
                'label' => $s->label(),
                'jumlah' => (int) ($jumlah[$s->value] ?? 0),
                'persen' => $this->persen((int) ($jumlah[$s->value] ?? 0), $total),
            ])
            ->filter(fn (array $baris) => $baris['jumlah'] > 0)
            ->values()
            ->all();

        // Baris NULL pada groupBy muncul dengan kunci array '' (koersi PHP
        // untuk kunci null), jadi angkanya sudah termasuk warga yang belum
        // diisi — tidak perlu whereNull() terpisah, itu hanya akan
        // menghitung dua kali baris yang sama.
        $belumDiisi = (int) ($jumlah[''] ?? 0);

        if ($belumDiisi > 0) {
            $hasil[] = ['label' => 'Belum diisi', 'jumlah' => $belumDiisi, 'persen' => $this->persen($belumDiisi, $total)];
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Sebaran pekerjaan lengkap (halaman publik — tidak dibatasi 10 teratas)
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function sebaranPekerjaan(): array
    {
        $total = $this->totalWargaAktif();

        $perPekerjaan = Penduduk::query()->aktif()
            ->whereNotNull('pekerjaan_id')
            ->join('ref_pekerjaan', 'penduduk.pekerjaan_id', '=', 'ref_pekerjaan.id')
            ->selectRaw('ref_pekerjaan.nama as nama, COUNT(*) as total')
            ->groupBy('ref_pekerjaan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'nama');

        $hasil = $perPekerjaan
            ->map(fn ($jumlah, $nama) => [
                'label' => $nama,
                'jumlah' => (int) $jumlah,
                'persen' => $this->persen((int) $jumlah, $total),
            ])
            ->values()
            ->all();

        $belumDiisi = Penduduk::query()->aktif()->whereNull('pekerjaan_id')->count();

        if ($belumDiisi > 0) {
            $hasil[] = ['label' => 'Belum diisi', 'jumlah' => $belumDiisi, 'persen' => $this->persen($belumDiisi, $total)];
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Sebaran wilayah
    // ------------------------------------------------------------------

    /**
     * @param  'dusun'|'rw'|'rt'  $tingkat
     * @param  'desc'|'asc'  $urutan
     * @return array<int, array{label: string, jumlah_warga: int, jumlah_kk: int, jiwa_per_kk: float|null}>
     */
    public function sebaranWilayah(string $tingkat = 'dusun', string $urutan = 'desc'): array
    {
        $ringkasanKk = new RingkasanKk;
        $tahun = $ringkasanKk->tahunAcuan();

        $wargaPerWilayah = Penduduk::query()->aktif()
            ->selectRaw('wilayah_id, COUNT(*) as total')
            ->groupBy('wilayah_id')
            ->pluck('total', 'wilayah_id');

        $kkPerWilayah = $tahun === null
            ? collect()
            : RekapKk::tahun($tahun)->pluck('jumlah_kk', 'wilayah_id');

        // Wilayah yang belum punya warga tetap tampil dengan angka nol,
        // bukan hilang dari daftar — RT kosong adalah informasi, bukan
        // ketiadaan informasi.
        $baris = Wilayah::urut()->get()
            ->groupBy(fn (Wilayah $w) => match ($tingkat) {
                'rt' => $w->dusun.' — RT '.$w->rt.'/RW '.$w->rw,
                'rw' => $w->dusun.' — RW '.$w->rw,
                default => $w->dusun,
            })
            ->map(function (Collection $wilayah, string $label) use ($wargaPerWilayah, $kkPerWilayah) {
                $warga = (int) $wilayah->sum(fn (Wilayah $w) => $wargaPerWilayah[$w->id] ?? 0);
                $kk = (int) $wilayah->sum(fn (Wilayah $w) => $kkPerWilayah[$w->id] ?? 0);

                return [
                    'label' => $label,
                    'jumlah_warga' => $warga,
                    'jumlah_kk' => $kk,
                    'jiwa_per_kk' => $kk > 0 ? round($warga / $kk, 2) : null,
                ];
            })
            ->values();

        return ($urutan === 'asc'
            ? $baris->sortBy('jumlah_warga')
            : $baris->sortByDesc('jumlah_warga'))
            ->values()
            ->all();
    }

    // ------------------------------------------------------------------
    // Tren kelahiran & kematian
    // ------------------------------------------------------------------

    /**
     * Perkembangan mutasi penduduk per tahun.
     *
     * Pindah masuk dan pindah keluar ikut ditampilkan agar pertumbuhan
     * penduduk terbaca utuh: desa bisa saja tumbuh karena pendatang, bukan
     * karena kelahiran. Warga terarsip dengan alasan "duplikat" tidak muncul
     * di sini karena bukan peristiwa kependudukan.
     *
     * @return array<int, array{tahun: int, kelahiran: int, kematian: int, pindah_masuk: int, pindah_keluar: int}>
     */
    public function trenMutasi(int $jumlahTahun = 5): array
    {
        $tahunAkhir = now()->year;
        $tahunAwal = $tahunAkhir - max(1, $jumlahTahun) + 1;

        $lahir = $this->hitungPerTahun('jenis_masuk', JenisMasuk::Lahir->value, 'tanggal_masuk', $tahunAwal, $tahunAkhir, false);
        $pindahMasuk = $this->hitungPerTahun('jenis_masuk', JenisMasuk::PindahMasuk->value, 'tanggal_masuk', $tahunAwal, $tahunAkhir, false);
        $meninggal = $this->hitungPerTahun('alasan_keluar', AlasanKeluar::Meninggal->value, 'tanggal_keluar', $tahunAwal, $tahunAkhir, true);
        $pindahKeluar = $this->hitungPerTahun('alasan_keluar', AlasanKeluar::PindahKeluar->value, 'tanggal_keluar', $tahunAwal, $tahunAkhir, true);

        $hasil = [];

        // Tahun tanpa kejadian tetap tampil dengan nilai nol, tidak dilewati —
        // garis grafik yang melompati tahun akan terbaca seolah tidak ada data.
        foreach (range($tahunAwal, $tahunAkhir) as $tahun) {
            $hasil[] = [
                'tahun' => $tahun,
                'kelahiran' => (int) ($lahir[$tahun] ?? 0),
                'kematian' => (int) ($meninggal[$tahun] ?? 0),
                'pindah_masuk' => (int) ($pindahMasuk[$tahun] ?? 0),
                'pindah_keluar' => (int) ($pindahKeluar[$tahun] ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * @return Collection<int, int> tahun => jumlah
     */
    private function hitungPerTahun(
        string $kolomJenis,
        string $nilai,
        string $kolomTanggal,
        int $tahunAwal,
        int $tahunAkhir,
        bool $hanyaTerarsip,
    ): Collection {
        $query = $hanyaTerarsip
            ? Penduduk::query()->onlyTrashed()
            : Penduduk::query()->withTrashed();

        return $query
            ->where($kolomJenis, $nilai)
            ->whereNotNull($kolomTanggal)
            ->whereYear($kolomTanggal, '>=', $tahunAwal)
            ->whereYear($kolomTanggal, '<=', $tahunAkhir)
            ->selectRaw($this->ekspresiTahun($kolomTanggal).' as tahun, COUNT(*) as total')
            ->groupBy('tahun')
            ->pluck('total', 'tahun');
    }

    /**
     * Pengambilan tahun dari kolom tanggal.
     *
     * MySQL dan SQLite memakai fungsi yang berbeda, sementara produksi
     * berjalan di MySQL dan pengujian di SQLite. Tanpa pembeda ini, statistik
     * tren akan lulus tes tetapi gagal di server.
     */
    private function ekspresiTahun(string $kolom): string
    {
        return Penduduk::query()->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$kolom}) AS INTEGER)"
            : "YEAR({$kolom})";
    }

    private function persen(int $jumlah, int $total): float
    {
        return $total === 0 ? 0.0 : round($jumlah / $total * 100, 1);
    }
}
