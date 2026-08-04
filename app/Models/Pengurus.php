<?php

namespace App\Models;

use App\Models\Concerns\PunyaGambar;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengurus extends Model
{
    use PunyaGambar;

    protected $table = 'pengurus_desa';

    protected $guarded = [];

    protected array $kolomGambar = ['foto'];

    protected function casts(): array
    {
        return [
            'tampil' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(RefOrganisasi::class, 'organisasi_id');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('tampil', true);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function urlFoto(): string
    {
        return app(LayananUnggahGambar::class)->url($this->foto, 'pengurus');
    }

    public function urlFotoKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->foto, 'pengurus');
    }

    /**
     * Nomor HP dalam bentuk yang bisa dipakai tautan wa.me — kode negara 62,
     * tanpa tanda baca. Nomor lokal "0812…" diubah menjadi "62812…".
     */
    public function nomorWhatsapp(): ?string
    {
        if (blank($this->nomor_hp)) {
            return null;
        }

        $angka = preg_replace('/\D/', '', $this->nomor_hp);

        if (blank($angka)) {
            return null;
        }

        return match (true) {
            str_starts_with($angka, '62') => $angka,
            str_starts_with($angka, '0') => '62'.substr($angka, 1),
            default => '62'.$angka,
        };
    }

    /**
     * Keterangan wilayah untuk pengurus RT/RW, mis. "RT 002 / RW 001".
     */
    public function keteranganWilayah(): ?string
    {
        return $this->wilayah?->labelSingkat();
    }
}
