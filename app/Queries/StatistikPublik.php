<?php

namespace App\Queries;

use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PRD 2.6 — statistik untuk halaman publik.
 *
 * Membungkus StatistikPenduduk lalu menerapkan aturan perlindungan sebelum
 * angkanya keluar dari aplikasi. Penyaringan dilakukan di lapisan data, bukan
 * disembunyikan di tampilan: angka yang hanya di-hide lewat CSS tetap terkirim
 * ke peramban dan bisa dibaca siapa saja lewat "lihat sumber halaman".
 *
 * Dua perlindungan yang diterapkan:
 * 1. Kelompok berisi kurang dari 5 warga tidak pernah menjadi angka pasti.
 *    Di RT kecil, "1 orang lulusan S3" praktis menunjuk satu orang tertentu.
 * 2. Rincian berhenti di tingkat dusun. Sebaran per RT terlalu sempit untuk
 *    tetap anonim.
 */
class StatistikPublik
{
    /** Ambang minimum agar sebuah kelompok boleh ditampilkan sebagai angka. */
    public const AMBANG = 5;

    private const PREFIX_CACHE = 'statistik_publik.';

    /** Seluruh kunci cache yang dikelola kelas ini. */
    private const KUNCI_CACHE = [
        'ringkasan',
        'sebaran_umur',
        'rasio_gender',
        'pendidikan',
        'sebaran_dusun',
        'jumlah_dusun',
        'tanggal_pembaruan',
        'piramida',
        'agama',
        'status_perkawinan',
        'pekerjaan',
    ];

    /**
     * Penanda bahwa cache sudah dikosongkan dalam permintaan ini.
     *
     * Impor warga menyimpan sampai 2.000 baris dalam satu transaksi, dan
     * setiap baris memicu invalidasi. Tanpa penanda ini, satu impor berubah
     * menjadi belasan ribu penghapusan cache — pada penyimpanan cache
     * database, belasan ribu kueri DELETE.
     *
     * Aman karena invariannya dijaga: penanda hanya bernilai true selama
     * cache memang kosong. Begitu sebuah nilai dihitung dan disimpan ulang,
     * hafalkan() mengembalikannya ke false.
     */
    private static bool $sudahDilupakan = false;

    /**
     * Instance aplikasi pemilik penanda di atas — pola yang sama dengan
     * TeksTampilan::$memoUntukAplikasi.
     *
     * Di PHP-FPM biasa satu proses melayani satu permintaan, sehingga
     * properti statis lenyap dengan sendirinya. Tidak demikian pada proses
     * yang berumur panjang (Octane, pekerja antrean) maupun di dalam satu
     * proses PHPUnit: penanda dari permintaan sebelumnya akan bertahan dan
     * membuat pengosongan cache berikutnya dilewati, padahal cache sudah
     * terisi lagi oleh proses lain.
     */
    private static ?object $penandaUntukAplikasi = null;

    public function __construct(
        private readonly StatistikPenduduk $statistik = new StatistikPenduduk(),
        private readonly RingkasanKk $kk = new RingkasanKk(),
    ) {}

    /**
     * @return array{jumlah_penduduk: int, jumlah_kk: int|null, tahun_kk: int|null, laki: int, perempuan: int, jiwa_per_kk: float|null, jumlah_dusun: int}
     */
    public function ringkasan(): array
    {
        return $this->hafalkan('ringkasan', function (): array {
            $gender = collect($this->statistik->rasioGender())->keyBy('kunci');

            return [
                'jumlah_penduduk' => $this->statistik->totalWargaAktif(),
                // Bila belum ada rekap sama sekali, kartunya disembunyikan
                // seluruhnya alih-alih menampilkan nol yang menyesatkan.
                'jumlah_kk' => $this->kk->adaData() ? $this->kk->total() : null,
                'tahun_kk' => $this->kk->tahunAcuan(),
                'laki' => (int) ($gender['L']['jumlah'] ?? 0),
                'perempuan' => (int) ($gender['P']['jumlah'] ?? 0),
                'jiwa_per_kk' => $this->kk->rataJiwaPerKk(),
                'jumlah_dusun' => $this->jumlahDusun(),
            ];
        });
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function sebaranUmur(): array
    {
        return $this->hafalkan(
            'sebaran_umur',
            fn () => $this->lindungi($this->statistik->sebaranUmur()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function rasioGender(): array
    {
        // Gender hanya dua kelompok dan selalu besar; tidak perlu digabung,
        // tetapi ambang tetap diterapkan demi konsistensi aturan.
        return $this->hafalkan(
            'rasio_gender',
            fn () => $this->lindungi($this->statistik->rasioGender()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function pendidikan(): array
    {
        return $this->hafalkan(
            'pendidikan',
            fn () => $this->lindungi($this->statistik->pendidikan()),
        );
    }

    /**
     * @return array<int, array{label: string, laki: int|null, laki_tampilan: string, perempuan: int|null, perempuan_tampilan: string}>
     */
    public function piramidaUmurGender(): array
    {
        return $this->hafalkan(
            'piramida',
            fn () => $this->lindungiPiramida($this->statistik->piramidaUmurGender()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function agama(): array
    {
        return $this->hafalkan(
            'agama',
            fn () => $this->lindungi($this->statistik->agama()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function statusPerkawinan(): array
    {
        return $this->hafalkan(
            'status_perkawinan',
            fn () => $this->lindungi($this->statistik->statusPerkawinan()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function sebaranPekerjaan(): array
    {
        return $this->hafalkan(
            'pekerjaan',
            fn () => $this->lindungi($this->statistik->sebaranPekerjaan()),
        );
    }

    /**
     * Sebaran per dusun. Tidak pernah sampai RW atau RT (PRD 2.6).
     *
     * @return array<int, array{label: string, jumlah_warga: int|null, tampilan: string, jumlah_kk: int, persen: float}>
     */
    public function sebaranDusun(): array
    {
        return $this->hafalkan('sebaran_dusun', fn () => $this->hitungSebaranDusun());
    }

    /**
     * @return array<int, array{label: string, jumlah_warga: int|null, tampilan: string, jumlah_kk: int, persen: float}>
     */
    private function hitungSebaranDusun(): array
    {
        $baris = collect($this->statistik->sebaranWilayah('dusun', 'desc'));
        $total = max(1, (int) $baris->sum('jumlah_warga'));

        $besar = $baris->filter(fn (array $b) => $b['jumlah_warga'] >= self::AMBANG);
        $kecil = $baris->reject(fn (array $b) => $b['jumlah_warga'] >= self::AMBANG);

        $hasil = $besar->map(fn (array $b) => [
            'label' => $b['label'],
            'jumlah_warga' => $b['jumlah_warga'],
            'tampilan' => number_format($b['jumlah_warga'], 0, ',', '.'),
            'jumlah_kk' => $b['jumlah_kk'],
            'persen' => round($b['jumlah_warga'] / $total * 100, 1),
        ])->values();

        // Dusun bertetangga yang sama-sama kecil digabung, bukan dibuang —
        // membuang akan membuat totalnya tidak cocok dengan jumlah penduduk.
        if ($kecil->isNotEmpty()) {
            $jumlahKecil = (int) $kecil->sum('jumlah_warga');

            $hasil->push([
                'label' => 'Lainnya',
                'jumlah_warga' => $jumlahKecil >= self::AMBANG ? $jumlahKecil : null,
                'tampilan' => $jumlahKecil >= self::AMBANG
                    ? number_format($jumlahKecil, 0, ',', '.')
                    : '<'.self::AMBANG,
                'jumlah_kk' => (int) $kecil->sum('jumlah_kk'),
                'persen' => round($jumlahKecil / $total * 100, 1),
            ]);
        }

        return $hasil->all();
    }

    public function jumlahDusun(): int
    {
        return $this->hafalkan(
            'jumlah_dusun',
            fn () => Wilayah::query()->distinct()->count('dusun'),
        );
    }

    public function tanggalPembaruan(): ?string
    {
        return $this->hafalkan('tanggal_pembaruan', function (): ?string {
            $terakhir = Penduduk::withTrashed()->max('updated_at');

            return $terakhir ? \Illuminate\Support\Carbon::parse($terakhir)->translatedFormat('d F Y') : null;
        });
    }

    // ------------------------------------------------------------------
    // Cache
    // ------------------------------------------------------------------

    /**
     * Menyimpan hasil perhitungan sampai tengah malam.
     *
     * Bukan rememberForever seperti PengaturanDesa dan TeksTampilan: sebaran
     * umur dihitung terhadap tanggal hari ini, sehingga hasil kemarin sudah
     * keliru begitu hari berganti — meski tidak ada satu baris pun yang
     * disunting.
     *
     * @template T
     *
     * @param  callable(): T  $hitung
     * @return T
     */
    private function hafalkan(string $nama, callable $hitung): mixed
    {
        self::segarkanPenanda();

        $nilai = Cache::remember(
            self::PREFIX_CACHE.$nama,
            now()->endOfDay(),
            $hitung,
        );

        // Cache kembali terisi, jadi penghapusan berikutnya harus benar-benar
        // dijalankan lagi. Lihat catatan pada $sudahDilupakan.
        self::$sudahDilupakan = false;

        return $nilai;
    }

    /** Banyaknya kunci yang dihapus sekali pengosongan cache. */
    public static function jumlahKunciCache(): int
    {
        return count(self::KUNCI_CACHE);
    }

    /**
     * Dipanggil oleh model yang datanya membentuk statistik ini —
     * lihat App\Models\Concerns\MenyegarkanStatistikPublik.
     */
    public static function lupakanCache(): void
    {
        self::segarkanPenanda();

        if (self::$sudahDilupakan) {
            return;
        }

        foreach (self::KUNCI_CACHE as $nama) {
            Cache::forget(self::PREFIX_CACHE.$nama);
        }

        self::$sudahDilupakan = true;
    }

    /**
     * Membuang penanda milik aplikasi sebelumnya. Tanpa ini, penanda dari
     * permintaan lama ikut terbawa dan pengosongan cache berikutnya dilewati
     * meski cachenya sudah terisi kembali.
     */
    private static function segarkanPenanda(): void
    {
        if (self::$penandaUntukAplikasi !== app()) {
            self::$sudahDilupakan = false;
            self::$penandaUntukAplikasi = app();
        }
    }

    /**
     * Menerapkan ambang kelompok kecil.
     *
     * Kelompok di bawah ambang tidak dihapus dari daftar — kehadirannya bukan
     * rahasia, hanya jumlah pastinya. Yang dikeluarkan hanya angkanya.
     *
     * @param  array<int, array{label: string, jumlah: int, persen: float}>  $baris
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    private function lindungi(array $baris): array
    {
        return collect($baris)
            ->map(fn (array $b) => [
                'label' => $b['label'],
                'jumlah' => $b['jumlah'] >= self::AMBANG ? $b['jumlah'] : null,
                'tampilan' => $b['jumlah'] >= self::AMBANG
                    ? number_format($b['jumlah'], 0, ',', '.')
                    : ($b['jumlah'] === 0 ? '0' : '<'.self::AMBANG),
                'persen' => $b['persen'],
            ])
            ->values()
            ->all();
    }

    /**
     * Sensor ambang untuk piramida — tiap baris punya DUA kolom angka
     * (laki & perempuan) yang disensor independen, karena satu sisi bisa
     * di bawah ambang sementara sisi lain tidak.
     *
     * @param  array<int, array{label: string, laki: int, perempuan: int}>  $baris
     * @return array<int, array{label: string, laki: int|null, laki_tampilan: string, perempuan: int|null, perempuan_tampilan: string}>
     */
    private function lindungiPiramida(array $baris): array
    {
        $tampilkan = fn (int $jumlah) => $jumlah >= self::AMBANG
            ? number_format($jumlah, 0, ',', '.')
            : ($jumlah === 0 ? '0' : '<'.self::AMBANG);

        return collect($baris)
            ->map(fn (array $b) => [
                'label' => $b['label'],
                'laki' => $b['laki'] >= self::AMBANG ? $b['laki'] : null,
                'laki_tampilan' => $tampilkan($b['laki']),
                'perempuan' => $b['perempuan'] >= self::AMBANG ? $b['perempuan'] : null,
                'perempuan_tampilan' => $tampilkan($b['perempuan']),
            ])
            ->values()
            ->all();
    }

    /**
     * Data siap kirim untuk grafik. Hanya memuat nilai yang sudah disaring.
     *
     * @return Collection<string, mixed>
     */
    public function untukGrafik(string $jenis): Collection
    {
        return collect(match ($jenis) {
            'umur' => $this->sebaranUmur(),
            'gender' => $this->rasioGender(),
            'pendidikan' => $this->pendidikan(),
            'piramida' => $this->piramidaUmurGender(),
            default => [],
        });
    }
}
