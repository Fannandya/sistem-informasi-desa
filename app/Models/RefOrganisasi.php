<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefOrganisasi extends Model
{
    protected $table = 'ref_organisasi';

    protected $guarded = [];

    public function pengurus(): HasMany
    {
        return $this->hasMany(Pengurus::class, 'organisasi_id');
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function sedangDipakai(): bool
    {
        return $this->pengurus()->exists();
    }

    public function jumlahPemakai(): int
    {
        return $this->pengurus()->count();
    }
}
