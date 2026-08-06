<?php

namespace App\Enums;

enum TingkatPrestasi: string
{
    case Desa = 'desa';
    case Kecamatan = 'kecamatan';
    case Kabupaten = 'kabupaten';
    case Provinsi = 'provinsi';
    case Nasional = 'nasional';

    public function label(): string
    {
        return match ($this) {
            self::Desa => 'Tingkat Desa',
            self::Kecamatan => 'Tingkat Kecamatan',
            self::Kabupaten => 'Tingkat Kabupaten',
            self::Provinsi => 'Tingkat Provinsi',
            self::Nasional => 'Tingkat Nasional',
        };
    }

    public function warna(): string
    {
        return 'bg-primary-fixed text-on-primary-fixed-variant';
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
