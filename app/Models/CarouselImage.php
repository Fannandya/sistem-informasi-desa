<?php

namespace App\Models;

use App\Models\Concerns\PunyaGambar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarouselImage extends Model
{
    use HasFactory, PunyaGambar;

    protected $fillable = ['path', 'judul', 'deskripsi', 'urutan', 'tampil', 'admin_id'];

    protected $casts = [
        'tampil' => 'boolean',
    ];

    protected function kolomGambar(): array
    {
        return ['path'];
    }

    public function scopeTampil($query)
    {
        return $query->where('tampil', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }
}
