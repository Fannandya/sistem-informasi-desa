<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKategoriFasilitas extends Model
{
    use HasFactory;

    protected $table = 'ref_kategori_fasilitas';
    protected $fillable = ['nama', 'urutan'];

    public function fasilitas(): HasMany
    {
        return $this->hasMany(FasilitasUmum::class, 'kategori_fasilitas_id');
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
