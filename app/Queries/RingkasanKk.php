<?php

namespace App\Queries;

use App\Models\Penduduk;
use App\Models\RekapKk;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

/**
 * PRD 2.9 — sumber tunggal angka Jumlah KK.
 *
 * Dashboard admin (fitur 06) dan statistik publik (fitur 15) wajib memakai
 * kelas ini, tidak menghitung sendiri. Kalau keduanya menghitung sendiri,
 * cepat atau lambat halaman publik dan panel admin akan menampilkan jumlah
 * keluarga yang berbeda untuk desa yang sama.
 *
 * Angka selalu diambil dari tahun rekap terakhir yang memiliki data — bukan
 * otomatis tahun berjalan. Awal tahun, sebelum admin sempat merekap, memakai
 * tahun berjalan akan menampilkan "0 KK" seolah seluruh keluarga desa lenyap.
 */
class RingkasanKk
{
    private ?int $tahun = null;

    private bool $tahunDicari = false;

    public function __construct(?int $tahun = null)
    {
        if ($tahun !== null) {
            $this->tahun = $tahun;
            $this->tahunDicari = true;
        }
    }

    /**
     * Tahun yang dipakai sebagai acuan. Null bila belum ada rekap sama sekali.
     */
    public function tahunAcuan(): ?int
    {
        if (! $this->tahunDicari) {
            $this->tahun = RekapKk::max('tahun');
            $this->tahunDicari = true;
        }

        return $this->tahun === null ? null : (int) $this->tahun;
    }

    public function adaData(): bool
    {
        return $this->tahunAcuan() !== null;
    }

    /**
     * Total KK seluruh desa pada tahun acuan.
     */
    public function total(): int
    {
        $tahun = $this->tahunAcuan();

        return $tahun === null ? 0 : (int) RekapKk::tahun($tahun)->sum('jumlah_kk');
    }

    // ------------------------------------------------------------------
    // Kelengkapan
    // ------------------------------------------------------------------

    public function totalWilayah(): int
    {
        return Wilayah::count();
    }

    public function jumlahTerisi(): int
    {
        $tahun = $this->tahunAcuan();

        return $tahun === null ? 0 : RekapKk::tahun($tahun)->count();
    }

    public function jumlahBelumDiisi(): int
    {
        return max(0, $this->totalWilayah() - $this->jumlahTerisi());
    }

    /**
     * Rekap dianggap lengkap bila setiap RT yang terdaftar sudah punya angka.
     * Angka nol tetap dihitung sebagai "sudah diisi" — berbeda dari baris
     * yang belum ada sama sekali.
     */
    public function lengkap(): bool
    {
        return $this->adaData() && $this->jumlahBelumDiisi() === 0;
    }

    public function keteranganKelengkapan(): string
    {
        if (! $this->adaData()) {
            return 'Belum ada rekap KK sama sekali.';
        }

        return sprintf(
            '%d dari %d RT sudah diisi.',
            $this->jumlahTerisi(),
            $this->totalWilayah(),
        );
    }

    // ------------------------------------------------------------------
    // Turunan
    // ------------------------------------------------------------------

    /**
     * Rata-rata jiwa per keluarga. Null bila belum ada rekap, agar pemanggil
     * bisa menyembunyikannya alih-alih menampilkan angka menyesatkan.
     */
    public function rataJiwaPerKk(): ?float
    {
        $totalKk = $this->total();

        if ($totalKk === 0) {
            return null;
        }

        return round(Penduduk::query()->aktif()->count() / $totalKk, 2);
    }

    /**
     * Subtotal per dusun, untuk tabel sebaran wilayah.
     *
     * @return Collection<int, array{dusun: string, jumlah_kk: int, terisi: int, total_rt: int}>
     */
    public function perDusun(): Collection
    {
        $tahun = $this->tahunAcuan();

        $rekap = $tahun === null
            ? collect()
            : RekapKk::tahun($tahun)->pluck('jumlah_kk', 'wilayah_id');

        return Wilayah::urut()->get()
            ->groupBy('dusun')
            ->map(function (Collection $wilayah, string $dusun) use ($rekap) {
                $terisi = $wilayah->filter(fn (Wilayah $w) => $rekap->has($w->id));

                return [
                    'dusun' => $dusun,
                    'jumlah_kk' => (int) $terisi->sum(fn (Wilayah $w) => $rekap[$w->id]),
                    'terisi' => $terisi->count(),
                    'total_rt' => $wilayah->count(),
                ];
            })
            ->values();
    }
}
