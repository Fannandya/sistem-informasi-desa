<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKategoriPotensi extends Model
{
    use HasFactory;

    protected $table = 'ref_kategori_potensi';
    protected $fillable = ['nama', 'urutan'];

    public function potensi(): HasMany
    {
        return $this->hasMany(PotensiDesa::class, 'kategori_potensi_id');
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }

    public function warna(): string
    {
        return 'bg-primary-fixed text-on-primary-fixed-variant';
    }

    public function label(): string
    {
        return $this->nama;
    }
}
