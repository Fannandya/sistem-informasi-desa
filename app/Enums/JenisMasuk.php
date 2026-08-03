<?php

namespace App\Enums;

/**
 * PRD 2.7 — cara seorang warga tercatat di sistem.
 *
 * Kolom ini ada karena penambahan baris bukan berarti kelahiran. Tanpa
 * pembeda ini, seorang pendatang akan terhitung sebagai bayi yang lahir dan
 * statistik desa jadi salah secara sistematis.
 */
enum JenisMasuk: string
{
    case Lahir = 'lahir';
    case PindahMasuk = 'pindah_masuk';
    case PendataanAwal = 'pendataan_awal';

    public function label(): string
    {
        return match ($this) {
            self::Lahir => 'Lahir',
            self::PindahMasuk => 'Pindah Masuk',
            self::PendataanAwal => 'Pendataan Awal',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Lahir => 'Bayi yang lahir di desa ini. Hanya pilihan ini yang dihitung sebagai kelahiran.',
            self::PindahMasuk => 'Warga yang pindah masuk dari desa atau daerah lain.',
            self::PendataanAwal => 'Warga yang sudah lama tinggal di sini, baru dicatat sekarang.',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $j) => [$j->value => $j->label()])
            ->all();
    }
}
