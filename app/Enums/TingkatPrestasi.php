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

    /** Warna menguat seiring naiknya tingkat, agar hierarkinya terbaca. */
    public function warna(): string
    {
        return match ($this) {
            self::Desa => 'bg-slate-200 text-slate-700',
            self::Kecamatan => 'bg-cyan-100 text-cyan-800',
            self::Kabupaten => 'bg-blue-100 text-blue-800',
            self::Provinsi => 'bg-violet-100 text-violet-800',
            self::Nasional => 'bg-amber-100 text-amber-900',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
