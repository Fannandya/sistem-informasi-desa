<?php

namespace App\Models;

use App\Models\Concerns\PunyaSlug;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AcaraDesa extends Model
{
    use PunyaSlug;

    protected $table = 'acara_desa';

    protected $guarded = [];

    protected string $sumberSlug = 'judul';

    protected function casts(): array
    {
        return ['tanggal_acara' => 'date'];
    }

    protected static function booted(): void
    {
        // Foto ikut terhapus lewat cascade pada foreign key, tetapi berkasnya
        // di penyimpanan harus dihapus sendiri sebelum barisnya hilang.
        static::deleting(function (self $acara): void {
            $layanan = app(LayananUnggahGambar::class);

            foreach ($acara->foto()->pluck('path') as $path) {
                $layanan->hapus($path);
            }
        });
    }

    public function foto(): HasMany
    {
        return $this->hasMany(FotoAcara::class, 'acara_id')->orderBy('urutan')->orderBy('id');
    }

    /**
     * Satu foto sampul saja, untuk daftar dan kartu.
     *
     * Daftar galeri hanya menampilkan satu foto per acara. Memuat relasi
     * `foto` di sana berarti menghidrasi seluruh foto setiap acara menjadi
     * model — 12 acara berisi 50 foto menjadi 600 baris — lalu membuang
     * hampir semuanya. Relasi ini menyerahkan pemilihannya ke basis data.
     *
     * Urutan pemilihannya sama persis dengan fotoSampul(): yang ditandai
     * admin lebih dulu, selebihnya urutan terkecil.
     */
    public function sampul(): HasOne
    {
        return $this->hasOne(FotoAcara::class, 'acara_id')->ofMany([
            'sampul' => 'max',
            'urutan' => 'min',
            'id' => 'min',
        ]);
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
        return $query->orderByDesc('tanggal_acara')->orderByDesc('id');
    }

    public function sudahTerbit(): bool
    {
        return $this->status === 'terbit';
    }

    /**
     * Foto sampul: yang ditandai admin, atau foto pertama bila belum ada
     * yang ditandai.
     */
    public function fotoSampul(): ?FotoAcara
    {
        // Halaman detail sudah memuat seluruh fotonya untuk galeri; memakai
        // koleksi itu menghindari satu kueri tambahan hanya untuk sampul.
        if ($this->relationLoaded('foto')) {
            return $this->foto->firstWhere('sampul', true) ?? $this->foto->first();
        }

        return $this->sampul;
    }

    public function urlSampul(): string
    {
        $sampul = $this->fotoSampul();

        return $sampul
            ? app(LayananUnggahGambar::class)->url($sampul->path, 'acara')
            : app(LayananUnggahGambar::class)->urlPlaceholder('acara');
    }

    public function urlSampulKecil(): string
    {
        $sampul = $this->fotoSampul();

        return $sampul
            ? app(LayananUnggahGambar::class)->urlThumbnail($sampul->path, 'acara')
            : app(LayananUnggahGambar::class)->urlPlaceholder('acara');
    }

    public function deskripsiMeta(): string
    {
        return Str::limit(trim(strip_tags((string) $this->narasi)) ?: $this->judul, 157);
    }
}
