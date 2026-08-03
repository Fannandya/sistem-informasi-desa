<?php

namespace App\Enums;

/**
 * PRD 2.7 — sebab seorang warga keluar dari daftar aktif.
 *
 * Pengarsipan bukan berarti kematian. Warga bisa pindah domisili, atau
 * datanya ternyata ganda. Tanpa pembeda ini, statistik kematian desa akan
 * membengkak setiap kali admin merapikan data.
 */
enum AlasanKeluar: string
{
    case Meninggal = 'meninggal';
    case PindahKeluar = 'pindah_keluar';
    case Duplikat = 'duplikat';

    public function label(): string
    {
        return match ($this) {
            self::Meninggal => 'Meninggal Dunia',
            self::PindahKeluar => 'Pindah Keluar',
            self::Duplikat => 'Data Duplikat',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Meninggal => 'Hanya pilihan ini yang dihitung sebagai kematian pada statistik.',
            self::PindahKeluar => 'Warga pindah ke desa atau daerah lain.',
            self::Duplikat => 'Data ganda yang dibersihkan. Tidak dihitung sebagai kematian maupun kepindahan.',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Meninggal => 'bg-slate-100 text-slate-700',
            self::PindahKeluar => 'bg-amber-100 text-amber-800',
            self::Duplikat => 'bg-red-100 text-red-700',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $a) => [$a->value => $a->label()])
            ->all();
    }
}
