<?php

namespace App\Enums;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * PRD 2.8 — definisi baku kelompok umur.
 *
 * Satu-satunya sumber pengelompokan umur. Dashboard admin (fitur 06) dan
 * statistik publik (fitur 15) wajib memakai enum ini, bukan mendefinisikan
 * rentangnya sendiri — kalau berbeda, kedua halaman akan menampilkan angka
 * yang saling bertentangan untuk data yang sama.
 */
enum KelompokUmur: string
{
    case BalitaAnak = 'balita_anak';
    case AnakSekolah = 'anak_sekolah';
    case Remaja = 'remaja';
    case DewasaMuda = 'dewasa_muda';
    case Dewasa = 'dewasa';
    case Lansia = 'lansia';
    case TidakDiketahui = 'tidak_diketahui';

    public function label(): string
    {
        return match ($this) {
            self::BalitaAnak => 'Balita & Anak',
            self::AnakSekolah => 'Anak Sekolah',
            self::Remaja => 'Remaja',
            self::DewasaMuda => 'Dewasa Muda',
            self::Dewasa => 'Dewasa',
            self::Lansia => 'Lansia',
            self::TidakDiketahui => 'Tidak Diketahui',
        };
    }

    /**
     * Batas bawah umur, inklusif. Null untuk kelompok tanpa tanggal lahir.
     */
    public function minimal(): ?int
    {
        return match ($this) {
            self::BalitaAnak => 0,
            self::AnakSekolah => 6,
            self::Remaja => 13,
            self::DewasaMuda => 19,
            self::Dewasa => 36,
            self::Lansia => 61,
            self::TidakDiketahui => null,
        };
    }

    /**
     * Batas atas umur, inklusif. Null berarti tanpa batas atas.
     */
    public function maksimal(): ?int
    {
        return match ($this) {
            self::BalitaAnak => 5,
            self::AnakSekolah => 12,
            self::Remaja => 18,
            self::DewasaMuda => 35,
            self::Dewasa => 60,
            self::Lansia, self::TidakDiketahui => null,
        };
    }

    public function rentang(): string
    {
        return match ($this) {
            self::Lansia => '61 tahun ke atas',
            self::TidakDiketahui => 'tanpa tanggal lahir',
            default => "{$this->minimal()}–{$this->maksimal()} tahun",
        };
    }

    /**
     * Kelompok untuk sebuah umur.
     */
    public static function dariUmur(?int $umur): self
    {
        if ($umur === null || $umur < 0) {
            return self::TidakDiketahui;
        }

        foreach (self::berumur() as $kelompok) {
            $maks = $kelompok->maksimal();

            if ($umur >= $kelompok->minimal() && ($maks === null || $umur <= $maks)) {
                return $kelompok;
            }
        }

        return self::TidakDiketahui;
    }

    /**
     * Seluruh kelompok yang punya rentang umur, tanpa "Tidak Diketahui".
     *
     * @return array<int, self>
     */
    public static function berumur(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $k) => $k !== self::TidakDiketahui,
        ));
    }

    /**
     * Menyaring query berdasarkan kelompok umur.
     *
     * Penyaringan dilakukan lewat rentang `tanggal_lahir`, bukan menghitung
     * umur di dalam SQL. Fungsi penghitung umur berbeda antara MySQL dan
     * SQLite, sementara pengujian berjalan di SQLite dan produksi di MySQL.
     */
    /**
     * Rentang tanggal lahir yang termasuk kelompok ini pada hari acuan.
     *
     * Satu-satunya tempat umur diterjemahkan menjadi tanggal. Dipakai oleh
     * penyaringan per kelompok maupun oleh perhitungan sebaran umur yang
     * menjumlahkan semua kelompok sekaligus — bila keduanya menghitung
     * sendiri-sendiri, cepat atau lambat batasnya akan berbeda.
     *
     * @return array{sampai: string, sesudah: string|null}|null
     *   sampai  — lahir pada atau sebelum tanggal ini (batas umur minimal)
     *   sesudah — lahir setelah tanggal ini (batas umur maksimal), null bila
     *             kelompoknya tidak berbatas atas
     *   null    — kelompok ini tidak ditentukan oleh tanggal lahir
     */
    public function rentangTanggalLahir(?CarbonInterface $acuan = null): ?array
    {
        if ($this === self::TidakDiketahui) {
            return null;
        }

        $hariIni = $acuan?->copy() ?? now();

        return [
            // Umur >= minimal berarti lahir pada atau sebelum (hari ini - minimal tahun).
            'sampai' => $hariIni->copy()->subYears($this->minimal())->toDateString(),
            // Umur <= maksimal berarti lahir setelah (hari ini - (maksimal+1) tahun).
            'sesudah' => $this->maksimal() === null
                ? null
                : $hariIni->copy()->subYears($this->maksimal() + 1)->toDateString(),
        ];
    }

    public function terapkanFilter(Builder $query, ?CarbonInterface $acuan = null): Builder
    {
        $rentang = $this->rentangTanggalLahir($acuan);

        if ($rentang === null) {
            return $query->whereNull('tanggal_lahir');
        }

        $query->whereNotNull('tanggal_lahir')
            ->whereDate('tanggal_lahir', '<=', $rentang['sampai']);

        if ($rentang['sesudah'] !== null) {
            $query->whereDate('tanggal_lahir', '>', $rentang['sesudah']);
        }

        return $query;
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $k) => [$k->value => $k->label().' ('.$k->rentang().')'])
            ->all();
    }
}
