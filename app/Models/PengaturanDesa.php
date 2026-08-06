<?php

namespace App\Models;

use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LogicException;

/**
 * Fitur 03 — identitas dan profil desa.
 *
 * Tabel singleton: selalu satu baris dengan id = 1. Diakses lewat
 * PengaturanDesa::ambil(), yang hasilnya di-cache karena view composer
 * memanggilnya pada setiap permintaan halaman.
 */
class PengaturanDesa extends Model
{
    public const ID = 1;

    public const KUNCI_CACHE = 'pengaturan_desa';

    protected $table = 'pengaturan_desa';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'medsos' => 'array',
            'luas_desa' => 'decimal:2',
            'tahun_berdiri' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (static::whereKey(self::ID)->exists()) {
                throw new LogicException(
                    'pengaturan_desa adalah tabel singleton — barisnya hanya boleh satu.',
                );
            }

            $model->id = self::ID;
        });

        static::saved(fn () => Cache::forget(self::KUNCI_CACHE));
        static::deleted(fn () => Cache::forget(self::KUNCI_CACHE));
    }

    /**
     * Satu-satunya baris pengaturan. Dibuat otomatis bila belum ada, agar
     * aplikasi tetap berjalan pada pemasangan baru yang belum di-seed.
     */
    public static function ambil(): self
    {
        return Cache::rememberForever(
            self::KUNCI_CACHE,
            fn () => static::firstOrCreate(['id' => self::ID], ['nama_desa' => 'Desa']),
        );
    }

    // ------------------------------------------------------------------
    // Turunan untuk tampilan
    // ------------------------------------------------------------------

    /**
     * Misi ditulis admin sebagai satu teks multi-baris, lalu ditampilkan
     * sebagai daftar di halaman publik. Penomoran manual seperti "1." atau
     * penanda "-" dibuang agar tidak dobel dengan penomoran daftar HTML.
     *
     * @return array<int, string>
     */
    public function misiSebagaiDaftar(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->misi))
            ->map(fn (string $baris) => trim(preg_replace('/^\s*(?:\d+[.)]|[-*\x{2022}])\s*/u', '', $baris)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Wilayah administratif dalam satu baris, melewati bagian yang kosong.
     */
    public function wilayahLengkap(): string
    {
        return collect([$this->kecamatan, $this->kabupaten, $this->provinsi])
            ->filter()
            ->implode(', ');
    }

    /**
     * Tautan media sosial yang benar-benar terisi.
     *
     * @return array<string, string>
     */
    public function medsosTerisi(): array
    {
        return collect($this->medsos ?? [])
            ->filter(fn ($nilai) => filled($nilai))
            ->all();
    }

    public function punyaKontak(): bool
    {
        return filled($this->alamat_kantor)
            || filled($this->telepon_kantor)
            || filled($this->email)
            || filled($this->website)
            || $this->medsosTerisi() !== [];
    }

    /**
     * Tautan "buka di Google Maps" untuk footer. `peta_embed` hanya berisi
     * URL /maps/embed yang dibuat untuk iframe, bukan halaman Maps biasa —
     * jadi bila alamat kantor terisi, dibuat tautan pencarian Maps dari teks
     * alamat itu. Baru jatuh ke `peta_embed` bila alamat tidak diisi.
     */
    public function tautanGoogleMaps(): ?string
    {
        if (filled($this->alamat_kantor)) {
            $kueri = trim($this->alamat_kantor.', '.$this->wilayahLengkap(), ', ');

            return 'https://www.google.com/maps/search/?api=1&query='.urlencode($kueri);
        }

        return $this->peta_embed ?: null;
    }

    public function urlLogo(): string
    {
        return app(LayananUnggahGambar::class)->url($this->logo, 'bawaan');
    }

    public function urlSampul(): string
    {
        return app(LayananUnggahGambar::class)->url($this->foto_sampul, 'bawaan');
    }

    /**
     * Deskripsi untuk meta tag. Bila admin belum mengisinya, dipakai potongan
     * sejarah atau visi desa — bukan teks bawaan yang sama di semua halaman.
     */
    public function deskripsiMeta(): string
    {
        $sumber = $this->meta_description
            ?: $this->sejarah
            ?: $this->visi
            ?: "Profil dan informasi resmi {$this->nama_desa}.";

        return Str::limit(trim(strip_tags($sumber)), 157);
    }
}
