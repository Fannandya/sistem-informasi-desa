<?php

namespace App\Models;

use App\Models\Concerns\PunyaGambar;
use App\Models\Concerns\PunyaSlug;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PotensiDesa extends Model
{
    use PunyaGambar, PunyaSlug;

    protected $table = 'potensi_desa';

    protected $fillable = ['nama', 'slug', 'kategori_potensi_id', 'deskripsi_singkat', 'deskripsi', 'foto', 'kontak', 'tampil', 'urutan'];

    protected array $kolomGambar = ['foto'];

    protected string $sumberSlug = 'nama';

    protected function casts(): array
    {
        return [
            'tampil' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RefKategoriPotensi::class, 'kategori_potensi_id');
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
        return app(LayananUnggahGambar::class)->url($this->foto, 'potensi');
    }

    public function urlFotoKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->foto, 'potensi');
    }

    public function ringkasan(): string
    {
        return $this->deskripsi_singkat
            ?: Str::limit(trim(strip_tags((string) $this->deskripsi)), 160);
    }
}
