<?php

namespace App\Models;

use App\Enums\TingkatPrestasi;
use App\Models\Concerns\PunyaGambar;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PrestasiDesa extends Model
{
    use PunyaGambar;

    protected $table = 'prestasi_desa';

    protected $guarded = [];

    protected array $kolomGambar = ['foto'];

    protected function casts(): array
    {
        return [
            'tingkat' => TingkatPrestasi::class,
            'tahun' => 'integer',
            'tampil' => 'boolean',
        ];
    }

    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('tampil', true);
    }

    /** Lini masa: capaian terbaru lebih dulu. */
    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tahun')->orderByDesc('id');
    }

    public function urlFoto(): string
    {
        return app(LayananUnggahGambar::class)->url($this->foto, 'prestasi');
    }

    public function urlFotoKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->foto, 'prestasi');
    }
}
