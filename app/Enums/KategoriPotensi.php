<?php

namespace App\Enums;

enum KategoriPotensi: string
{
    case Umkm = 'umkm';
    case HasilBumi = 'hasil_bumi';
    case Wisata = 'wisata';
    case Kerajinan = 'kerajinan';

    public function label(): string
    {
        return match ($this) {
            self::Umkm => 'UMKM',
            self::HasilBumi => 'Hasil Bumi',
            self::Wisata => 'Wisata',
            self::Kerajinan => 'Kerajinan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Umkm => 'bg-blue-100 text-blue-800',
            self::HasilBumi => 'bg-emerald-100 text-emerald-800',
            self::Wisata => 'bg-purple-100 text-purple-800',
            self::Kerajinan => 'bg-amber-100 text-amber-800',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $k) => [$k->value => $k->label()])
            ->all();
    }
}
