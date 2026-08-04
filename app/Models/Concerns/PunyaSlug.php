<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * PRD 6.17 — aturan slug.
 *
 * Slug dibuat sekali saat data pertama kali dibuat dan TIDAK ikut berubah
 * saat nama atau judulnya diubah. Tautan yang sudah tersebar — dibagikan di
 * grup WhatsApp warga, diindeks Google — akan mati kalau slug ikut berganti
 * setiap kali admin memperbaiki satu huruf pada judul.
 *
 * Admin tetap bisa menyunting slug secara manual bila memang menginginkannya.
 *
 * Model yang memakai trait ini mendeklarasikan sumber slugnya:
 *
 *     protected string $sumberSlug = 'judul';
 */
trait PunyaSlug
{
    public static function bootPunyaSlug(): void
    {
        static::creating(function ($model): void {
            if (blank($model->slug)) {
                $model->slug = $model->slugUnik(
                    Str::slug((string) $model->getAttribute($model->sumberSlug())),
                );
            }
        });

        static::updating(function ($model): void {
            // Slug hanya diperbarui bila admin benar-benar menyuntingnya
            // sendiri, bukan sebagai efek samping perubahan judul.
            if ($model->isDirty('slug') && blank($model->slug)) {
                $model->slug = $model->getOriginal('slug');
            }
        });
    }

    public function sumberSlug(): string
    {
        return property_exists($this, 'sumberSlug') ? $this->sumberSlug : 'nama';
    }

    /**
     * Menambahkan akhiran angka bila slug sudah dipakai baris lain.
     */
    public function slugUnik(string $dasar): string
    {
        $dasar = $dasar !== '' ? $dasar : 'item';
        $slug = $dasar;
        $urutan = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = $dasar.'-'.(++$urutan);
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
