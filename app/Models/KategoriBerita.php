<?php

namespace App\Models;

use App\Models\Concerns\PunyaSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBerita extends Model
{
    use PunyaSlug;

    protected $table = 'kategori_berita';

    protected $guarded = [];

    protected string $sumberSlug = 'nama';

    public function berita(): HasMany
    {
        return $this->hasMany(BeritaDesa::class, 'kategori_id');
    }

    public function sedangDipakai(): bool
    {
        return $this->berita()->exists();
    }

    public function jumlahPemakai(): int
    {
        return $this->berita()->count();
    }
}
