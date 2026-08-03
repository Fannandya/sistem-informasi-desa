<?php

namespace App\Models\Concerns;

use App\Services\LayananUnggahGambar;

/**
 * Fitur 02 — menghapus gambar dari penyimpanan saat datanya dihapus.
 *
 * PRD 6.16: seluruh tabel yang memiliki gambar memakai penghapusan permanen,
 * sehingga gambar boleh ikut dihapus tanpa risiko data dipulihkan lagi.
 * Model yang memakai trait ini WAJIB tidak memakai SoftDeletes.
 *
 * Model cukup mendeklarasikan kolom gambarnya:
 *
 *     protected array $kolomGambar = ['foto'];
 */
trait PunyaGambar
{
    public static function bootPunyaGambar(): void
    {
        static::deleted(function ($model): void {
            if (in_array(
                \Illuminate\Database\Eloquent\SoftDeletes::class,
                class_uses_recursive($model),
                true,
            )) {
                // Data masih bisa dipulihkan — gambarnya tidak boleh dihapus.
                return;
            }

            $layanan = app(LayananUnggahGambar::class);

            foreach ($model->kolomGambar() as $kolom) {
                $layanan->hapus($model->getAttribute($kolom));
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public function kolomGambar(): array
    {
        return property_exists($this, 'kolomGambar') ? $this->kolomGambar : ['foto'];
    }
}
