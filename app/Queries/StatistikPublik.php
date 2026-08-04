<?php

namespace App\Queries;

use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

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

    public function __construct(
        private readonly StatistikPenduduk $statistik = new StatistikPenduduk(),
        private readonly RingkasanKk $kk = new RingkasanKk(),
    ) {}

    /**
     * @return array{jumlah_penduduk: int, jumlah_kk: int|null, tahun_kk: int|null, laki: int, perempuan: int, jiwa_per_kk: float|null}
     */
    public function ringkasan(): array
    {
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
        ];
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function sebaranUmur(): array
    {
        return $this->lindungi($this->statistik->sebaranUmur());
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function rasioGender(): array
    {
        // Gender hanya dua kelompok dan selalu besar; tidak perlu digabung,
        // tetapi ambang tetap diterapkan demi konsistensi aturan.
        return $this->lindungi($this->statistik->rasioGender());
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float}>
     */
    public function pendidikan(): array
    {
        return $this->lindungi($this->statistik->pendidikan());
    }

    /**
     * Sebaran per dusun. Tidak pernah sampai RW atau RT (PRD 2.6).
     *
     * @return array<int, array{label: string, jumlah_warga: int|null, tampilan: string, jumlah_kk: int, persen: float}>
     */
    public function sebaranDusun(): array
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
        return Wilayah::query()->distinct()->count('dusun');
    }

    public function tanggalPembaruan(): ?string
    {
        $terakhir = Penduduk::withTrashed()->max('updated_at');

        return $terakhir ? \Illuminate\Support\Carbon::parse($terakhir)->translatedFormat('d F Y') : null;
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
            default => [],
        });
    }
}
