<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapKk extends Model
{
    protected $table = 'rekap_kk';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'jumlah_kk' => 'integer',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }
}
