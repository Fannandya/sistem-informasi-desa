<?php

namespace App\Enums;

/**
 * 6 pilihan sejajar, bukan "Kawin" yang dipecah — dikonfirmasi eksplisit
 * saat perancangan fitur ini.
 */
enum StatusPerkawinan: string
{
    case BelumKawin = 'belum_kawin';
    case Kawin = 'kawin';
    case KawinTercatat = 'kawin_tercatat';
    case KawinTidakTercatat = 'kawin_tidak_tercatat';
    case CeraiHidup = 'cerai_hidup';
    case CeraiMati = 'cerai_mati';

    public function label(): string
    {
        return match ($this) {
            self::BelumKawin => 'Belum Kawin',
            self::Kawin => 'Kawin',
            self::KawinTercatat => 'Kawin Tercatat',
            self::KawinTidakTercatat => 'Kawin Tidak Tercatat',
            self::CeraiHidup => 'Cerai Hidup',
            self::CeraiMati => 'Cerai Mati',
        };
    }

    /** @return array<string, string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
