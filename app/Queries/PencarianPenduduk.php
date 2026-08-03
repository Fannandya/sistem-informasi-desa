<?php

namespace App\Queries;

use App\Enums\AlasanKeluar;
use App\Enums\JenisKelamin;
use App\Enums\KelompokUmur;
use App\Enums\Pendidikan;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Fitur 04 — pencarian dan saringan data warga.
 *
 * Dipisah dari controller karena tiga tempat memakai saringan yang sama
 * persis: daftar warga aktif, halaman arsip, dan ekspor (fitur 07). Kalau
 * logikanya disalin, cepat atau lambat berkas ekspor akan berisi data yang
 * berbeda dari yang tampil di layar.
 */
class PencarianPenduduk
{
    /** Seluruh kunci saringan yang dikenali, sekaligus urutan tampil chip-nya. */
    private const KUNCI = [
        'q', 'dusun', 'rw', 'rt', 'jenis_kelamin',
        'kelompok_umur', 'pekerjaan_id', 'pendidikan',
        'alasan_keluar', 'dari', 'sampai',
    ];

    /** @param array<string, string|null> $filter */
    public function __construct(private readonly array $filter = []) {}

    public static function dariRequest(Request $request): self
    {
        $filter = collect(self::KUNCI)
            ->mapWithKeys(fn (string $kunci) => [$kunci => $request->input($kunci)])
            ->filter(fn ($nilai) => filled($nilai))
            ->all();

        return new self($filter);
    }

    public function terapkan(Builder $query): Builder
    {
        $query->cariNama($this->nilai('q'));

        // Saringan wilayah bertingkat: dusun → RW → RT.
        if ($this->punya('dusun') || $this->punya('rw') || $this->punya('rt')) {
            $query->whereHas('wilayah', function (Builder $w): void {
                if ($this->punya('dusun')) {
                    $w->where('dusun', $this->nilai('dusun'));
                }
                if ($this->punya('rw')) {
                    $w->where('rw', Wilayah::normalkanNomor($this->nilai('rw')));
                }
                if ($this->punya('rt')) {
                    $w->where('rt', Wilayah::normalkanNomor($this->nilai('rt')));
                }
            });
        }

        if ($jk = $this->enum('jenis_kelamin', JenisKelamin::class)) {
            $query->where('jenis_kelamin', $jk);
        }

        if ($kelompok = $this->enum('kelompok_umur', KelompokUmur::class)) {
            $query->diKelompokUmur($kelompok);
        }

        if ($this->punya('pekerjaan_id')) {
            $query->where('pekerjaan_id', $this->nilai('pekerjaan_id'));
        }

        if ($pendidikan = $this->enum('pendidikan', Pendidikan::class)) {
            $query->where('pendidikan', $pendidikan);
        }

        // --- Khusus halaman arsip ---

        if ($alasan = $this->enum('alasan_keluar', AlasanKeluar::class)) {
            $query->where('alasan_keluar', $alasan);
        }

        if ($this->punya('dari')) {
            $query->whereDate('tanggal_keluar', '>=', $this->nilai('dari'));
        }

        if ($this->punya('sampai')) {
            $query->whereDate('tanggal_keluar', '<=', $this->nilai('sampai'));
        }

        return $query;
    }

    /**
     * Saringan yang sedang aktif, siap ditampilkan sebagai chip yang bisa
     * dihapus satu per satu.
     *
     * @return array<int, array{kunci: string, label: string, sisa: array<string, string>}>
     */
    public function aktif(): array
    {
        $hasil = [];

        foreach (self::KUNCI as $kunci) {
            if (! $this->punya($kunci)) {
                continue;
            }

            $hasil[] = [
                'kunci' => $kunci,
                'label' => $this->labelChip($kunci),
                'sisa' => collect($this->filter)->except($kunci)->all(),
            ];
        }

        return $hasil;
    }

    public function adaFilter(): bool
    {
        return $this->filter !== [];
    }

    /** @return array<string, string> */
    public function parameter(): array
    {
        return $this->filter;
    }

    public function nilai(string $kunci): ?string
    {
        $nilai = $this->filter[$kunci] ?? null;

        return $nilai === null ? null : (string) $nilai;
    }

    private function punya(string $kunci): bool
    {
        return filled($this->filter[$kunci] ?? null);
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T|null
     */
    private function enum(string $kunci, string $enum): mixed
    {
        return $this->punya($kunci) ? $enum::tryFrom($this->nilai($kunci)) : null;
    }

    private function labelChip(string $kunci): string
    {
        return match ($kunci) {
            'q' => 'Nama: "'.$this->nilai('q').'"',
            'dusun' => 'Dusun: '.$this->nilai('dusun'),
            'rw' => 'RW '.$this->nilai('rw'),
            'rt' => 'RT '.$this->nilai('rt'),
            'jenis_kelamin' => JenisKelamin::tryFrom($this->nilai('jenis_kelamin'))?->label() ?? '-',
            'kelompok_umur' => KelompokUmur::tryFrom($this->nilai('kelompok_umur'))?->label() ?? '-',
            'pekerjaan_id' => 'Pekerjaan: '.(RefPekerjaan::find($this->nilai('pekerjaan_id'))?->nama ?? '-'),
            'pendidikan' => Pendidikan::tryFrom($this->nilai('pendidikan'))?->label() ?? '-',
            'alasan_keluar' => AlasanKeluar::tryFrom($this->nilai('alasan_keluar'))?->label() ?? '-',
            'dari' => 'Keluar sejak '.$this->nilai('dari'),
            'sampai' => 'Keluar sampai '.$this->nilai('sampai'),
            default => $kunci,
        };
    }
}
