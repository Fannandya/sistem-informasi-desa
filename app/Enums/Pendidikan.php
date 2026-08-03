<?php

namespace App\Enums;

enum Pendidikan: string
{
    case BelumSekolah = 'belum_sekolah';
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';
    case D1D3 = 'd1_d3';
    case S1 = 's1';
    case S2 = 's2';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::BelumSekolah => 'Belum / Tidak Sekolah',
            self::SD => 'SD / Sederajat',
            self::SMP => 'SMP / Sederajat',
            self::SMA => 'SMA / SMK / Sederajat',
            self::D1D3 => 'Diploma (D1–D3)',
            self::S1 => 'Sarjana (S1 / D4)',
            self::S2 => 'Magister (S2)',
            self::S3 => 'Doktor (S3)',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $p) => [$p->value => $p->label()])
            ->all();
    }
}
