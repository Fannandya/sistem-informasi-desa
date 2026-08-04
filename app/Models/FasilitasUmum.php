<?php

namespace App\Models;

use App\Models\Concerns\PunyaGambar;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FasilitasUmum extends Model
{
    use PunyaGambar;

    protected $table = 'fasilitas_umum';

    protected $fillable = ['nama', 'kategori_fasilitas_id', 'deskripsi', 'alamat', 'wilayah_id', 'foto', 'tampil'];

    protected array $kolomGambar = ['foto'];

    protected function casts(): array
    {
        return [
            'tampil' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RefKategoriFasilitas::class, 'kategori_fasilitas_id');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('tampil', true);
    }

    public function urlFoto(): string
    {
        return app(LayananUnggahGambar::class)->url($this->foto, 'fasilitas');
    }

    public function urlFotoKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->foto, 'fasilitas');
    }

    public function lokasiTerbaca(): ?string
    {
        return collect([$this->alamat, $this->wilayah?->labelSingkat()])
            ->filter()
            ->implode(' · ') ?: null;
    }
}
