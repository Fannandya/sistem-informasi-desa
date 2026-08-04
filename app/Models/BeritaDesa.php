<?php

namespace App\Models;

use App\Models\Concerns\PunyaGambar;
use App\Models\Concerns\PunyaSlug;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BeritaDesa extends Model
{
    use PunyaGambar, PunyaSlug;

    protected $table = 'berita_desa';

    protected $guarded = [];

    protected array $kolomGambar = ['gambar_sampul'];

    protected string $sumberSlug = 'judul';

    protected function casts(): array
    {
        return [
            'utama' => 'boolean',
            'tanggal_terbit' => 'datetime',
            'dilihat' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', 'terbit');
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal_terbit')->orderByDesc('id');
    }

    public function sudahTerbit(): bool
    {
        return $this->status === 'terbit';
    }

    public function urlSampul(): string
    {
        return app(LayananUnggahGambar::class)->url($this->gambar_sampul, 'berita');
    }

    public function urlSampulKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->gambar_sampul, 'berita');
    }

    /**
     * Ringkasan untuk kartu daftar. Bila admin tidak mengisinya, diambil dari
     * isi artikel — tag HTML dibuang agar tidak bocor sebagai teks mentah.
     */
    public function ringkasanTampil(): string
    {
        return $this->ringkasan ?: Str::limit(trim(strip_tags((string) $this->isi)), 180);
    }

    /**
     * Meta description dipotong 160 karakter; lebih dari itu terpotong sendiri
     * oleh mesin pencari di tempat yang tidak kita kendalikan.
     */
    public function deskripsiMeta(): string
    {
        return Str::limit(trim(strip_tags($this->ringkasanTampil())), 157);
    }

    public function waktuBaca(): int
    {
        $kata = str_word_count(strip_tags((string) $this->isi));

        return max(1, (int) ceil($kata / 200));
    }
}
