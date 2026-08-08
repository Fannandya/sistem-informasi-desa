<?php

namespace App\Exports;

use App\Imports\KolomImporPenduduk;
use App\Models\Penduduk;
use App\Queries\PencarianPenduduk;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fitur 07 — ekspor data penduduk.
 *
 * Daftar kolom ditetapkan eksplisit di sini, bukan diambil dari kolom tabel.
 * Menambah kolom baru pada tabel penduduk kelak tidak boleh membuat kolom itu
 * ikut terekspor dengan sendirinya (PRD 2.4) — keluarnya data dari sistem
 * harus selalu keputusan yang disengaja.
 */
class EksporPenduduk implements FromQuery, ShouldAutoSize, WithCustomCsvSettings, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly PencarianPenduduk $pencarian,
        private readonly bool $arsip = false,
    ) {}

    public function query(): Builder
    {
        $query = $this->arsip
            ? Penduduk::query()->terarsip()
            : Penduduk::query()->aktif();

        return $this->pencarian
            ->terapkan($query->with(['wilayah', 'pekerjaan', 'agama']))
            ->orderBy('nama_lengkap');
    }

    /**
     * Judul kolom sama persis dengan berkas contoh impor, sehingga berkas
     * hasil ekspor bisa disunting lalu diimpor kembali tanpa penyesuaian.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        $judul = [
            'Nama Lengkap',
            'Umur',
            'Jenis Kelamin',
            'Dusun',
            'RW',
            'RT',
            'Pekerjaan',
            'Pendidikan',
            'Agama',
            'Status Perkawinan',
        ];

        if ($this->arsip) {
            $judul[] = 'Alasan Keluar';
            $judul[] = 'Tanggal Keluar';
        }

        return $judul;
    }

    /**
     * @param  Penduduk  $penduduk
     * @return array<int, string|int|null>
     */
    public function map($penduduk): array
    {
        $baris = [
            $penduduk->nama_lengkap,
            // Umur diekspor sebagai angka hasil hitungan. Tanggal lahir
            // tidak pernah ikut keluar dari sistem (PRD 2.5).
            $penduduk->umur(),
            $penduduk->jenis_kelamin->label(),
            $penduduk->wilayah->dusun,
            $penduduk->wilayah->rw,
            $penduduk->wilayah->rt,
            $penduduk->pekerjaan?->nama,
            $penduduk->pendidikan?->label(),
            $penduduk->agama?->nama,
            $penduduk->status_perkawinan?->label(),
        ];

        if ($this->arsip) {
            $baris[] = $penduduk->alasan_keluar?->label();
            $baris[] = $penduduk->tanggal_keluar?->format('Y-m-d');
        }

        return $baris;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array
    {
        return [
            // Tanpa BOM, Excel di Windows membaca berkas sebagai ANSI dan
            // nama warga beraksen tampil rusak ("Zulfikar" jadi "Zulfikarâ€¦").
            'use_bom' => true,
            'delimiter' => ',',
            'enclosure' => '"',
        ];
    }

    /**
     * Kolom yang boleh keluar dari sistem. Dipakai pengujian otomatis untuk
     * memastikan tidak ada kolom terlarang yang menyelinap.
     *
     * @return array<int, string>
     */
    public static function kolomDiizinkan(bool $arsip = false): array
    {
        return (new self(new PencarianPenduduk(), $arsip))->headings();
    }

    /**
     * Judul kolom impor yang setara, untuk memastikan keduanya tidak
     * menyimpang tanpa disadari.
     *
     * @return array<int, string>
     */
    public static function judulImporTerkait(): array
    {
        return KolomImporPenduduk::JUDUL;
    }
}
