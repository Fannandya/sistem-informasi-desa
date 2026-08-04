<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * CMS Teks Tampilan — baris kosong (nilai null) berarti admin belum pernah
 * mengubah kunci ini; App\Support\Teks yang menerapkan fallback ke
 * config('teks-tampilan'). Seluruh tabel di-cache sebagai satu array karena
 * jumlah barisnya kecil dan stabil — lebih murah daripada cache per kunci.
 */
class TeksTampilan extends Model
{
    public const KUNCI_CACHE = 'teks_tampilan';

    protected $table = 'teks_tampilan';

    protected $fillable = ['kunci', 'nilai', 'grup', 'admin_id'];

    /** Memo per-request: Cache::rememberForever menembak store setiap dipanggil. */
    private static ?array $memo = null;

    /**
     * Instance aplikasi yang memiliki memo di atas. Di hosting PHP-FPM biasa,
     * satu proses = satu permintaan, jadi properti statis otomatis lenyap
     * antar permintaan. Di dalam proses PHPUnit yang panjang (banyak "request"
     * tersimulasi dalam satu proses), pembanding ini yang menggantikan peran
     * itu: begitu aplikasi di-boot ulang (setiap tes), memo lama dibuang.
     */
    private static ?object $memoUntukAplikasi = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::lupakanCache());
        static::deleted(fn () => static::lupakanCache());
    }

    /**
     * @return array<string, string|null>
     */
    public static function semua(): array
    {
        if (self::$memoUntukAplikasi !== app()) {
            self::$memo = null;
            self::$memoUntukAplikasi = app();
        }

        return self::$memo ??= Cache::rememberForever(
            self::KUNCI_CACHE,
            fn () => static::query()->pluck('nilai', 'kunci')->all(),
        );
    }

    private static function lupakanCache(): void
    {
        self::$memo = null;
        Cache::forget(self::KUNCI_CACHE);
    }
}
