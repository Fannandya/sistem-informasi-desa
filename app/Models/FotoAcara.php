<?php

namespace App\Models;

use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoAcara extends Model
{
    protected $table = 'foto_acara';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['sampul' => 'boolean', 'urutan' => 'integer'];
    }

    protected static function booted(): void
    {
        static::deleted(function (self $foto): void {
            app(LayananUnggahGambar::class)->hapus($foto->path);
        });
    }

    public function acara(): BelongsTo
    {
        return $this->belongsTo(AcaraDesa::class, 'acara_id');
    }

    public function url(): string
    {
        return app(LayananUnggahGambar::class)->url($this->path, 'acara');
    }

    public function urlKecil(): string
    {
        return app(LayananUnggahGambar::class)->urlThumbnail($this->path, 'acara');
    }
}
