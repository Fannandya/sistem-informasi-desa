<?php

namespace App\Enums;

enum KategoriFasilitas: string
{
    case Pendidikan = 'pendidikan';
    case Kesehatan = 'kesehatan';
    case Ibadah = 'ibadah';
    case Pemerintahan = 'pemerintahan';
    case Olahraga = 'olahraga';
    case Umum = 'umum';

    public function label(): string
    {
        return match ($this) {
            self::Pendidikan => 'Pendidikan',
            self::Kesehatan => 'Kesehatan',
            self::Ibadah => 'Tempat Ibadah',
            self::Pemerintahan => 'Pemerintahan',
            self::Olahraga => 'Olahraga',
            self::Umum => 'Fasilitas Umum',
        };
    }

    public function contoh(): string
    {
        return match ($this) {
            self::Pendidikan => 'SD, SMP, PAUD, TK, perpustakaan',
            self::Kesehatan => 'Puskesmas, Posyandu, Poskesdes',
            self::Ibadah => 'Masjid, musala, gereja, pura',
            self::Pemerintahan => 'Balai desa, kantor desa, pos kamling',
            self::Olahraga => 'Lapangan bola, lapangan voli, GOR',
            self::Umum => 'Pasar, MCK umum, TPS, jembatan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Pendidikan => 'bg-blue-100 text-blue-800',
            self::Kesehatan => 'bg-rose-100 text-rose-800',
            self::Ibadah => 'bg-emerald-100 text-emerald-800',
            self::Pemerintahan => 'bg-slate-200 text-slate-700',
            self::Olahraga => 'bg-orange-100 text-orange-800',
            self::Umum => 'bg-cyan-100 text-cyan-800',
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
