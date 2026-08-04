<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    use HasFactory;
    protected $table = 'wilayah';

    protected $guarded = [];

    public function penduduk(): HasMany
    {
        return $this->hasMany(Penduduk::class);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('dusun')->orderBy('rw')->orderBy('rt');
    }

    /**
     * "Dusun Krajan — RT 002 / RW 001"
     */
    public function label(): string
    {
        return "{$this->dusun} — RT {$this->rt} / RW {$this->rw}";
    }

    public function labelSingkat(): string
    {
        return "RT {$this->rt}/RW {$this->rw}";
    }

    /**
     * Wilayah tidak boleh dihapus selama masih dirujuk data lain, karena
     * penghapusannya akan memutus data warga dari sebaran wilayahnya.
     */
    public function sedangDipakai(): bool
    {
        return $this->jumlahPemakai() > 0;
    }

    public function jumlahPemakai(): int
    {
        return $this->penduduk()->withTrashed()->count();
    }

    /**
     * Nomor RT/RW ditulis dengan angka nol di depan agar pengurutan dan
     * pencarian konsisten: "2" dan "02" tidak boleh jadi dua wilayah.
     */
    public static function normalkanNomor(?string $nomor): string
    {
        $angka = preg_replace('/\D/', '', (string) $nomor);

        return $angka === '' ? '' : str_pad($angka, 3, '0', STR_PAD_LEFT);
    }
}
