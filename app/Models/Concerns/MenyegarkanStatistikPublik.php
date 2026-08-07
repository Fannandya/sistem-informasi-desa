<?php

namespace App\Models\Concerns;

use App\Queries\StatistikPublik;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Membuang cache statistik publik ketika data pembentuknya berubah.
 *
 * Dipasang pada model yang angkanya ikut menyusun halaman statistik dan
 * beranda: Penduduk, RekapKk, dan Wilayah. Tanpa ini, penyuntingan oleh
 * aparat desa baru terlihat di halaman publik setelah cache kedaluwarsa
 * tengah malam — cukup lama untuk membuat mereka mengira simpanannya gagal.
 */
trait MenyegarkanStatistikPublik
{
    public static function bootMenyegarkanStatistikPublik(): void
    {
        $lupakan = static fn () => StatistikPublik::lupakanCache();

        static::saved($lupakan);
        static::deleted($lupakan);

        // Pengarsipan warga memakai soft delete; memulihkannya juga mengubah
        // jumlah penduduk aktif.
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored($lupakan);
            static::forceDeleted($lupakan);
        }
    }
}
