<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefPekerjaan extends Model
{
    protected $table = 'ref_pekerjaan';

    protected $guarded = [];

    public function penduduk(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'pekerjaan_id');
    }

    public function sedangDipakai(): bool
    {
        return $this->jumlahPemakai() > 0;
    }

    public function jumlahPemakai(): int
    {
        return $this->penduduk()->withTrashed()->count();
    }
}
