<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefPekerjaan extends Model
{
    use HasFactory;
    protected $table = 'ref_pekerjaan';

    protected $fillable = ['nama', 'is_template'];

    protected $casts = [
        'is_template' => 'boolean',
    ];

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

    public function scopeTemplate($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_template', false);
    }
}
