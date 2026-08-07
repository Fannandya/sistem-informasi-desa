<?php

namespace App\Models;

use App\Enums\AlasanKeluar;
use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\KelompokUmur;
use App\Enums\Pendidikan;
use App\Models\Concerns\MenyegarkanStatistikPublik;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fitur 04 — data dasar warga desa.
 *
 * Satu-satunya tabel di aplikasi ini yang memakai soft delete (PRD 6.16).
 * Pengarsipan di sini berarti warga keluar dari daftar aktif, bukan
 * terhapus — sebabnya dicatat pada `alasan_keluar` agar statistik kematian
 * tidak tercampur dengan kepindahan.
 */
class Penduduk extends Model
{
    use HasFactory;
    use MenyegarkanStatistikPublik;
    use SoftDeletes;

    protected $table = 'penduduk';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'jenis_kelamin' => JenisKelamin::class,
            'pendidikan' => Pendidikan::class,
            'jenis_masuk' => JenisMasuk::class,
            'alasan_keluar' => AlasanKeluar::class,
        ];
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function pekerjaan(): BelongsTo
    {
        return $this->belongsTo(RefPekerjaan::class, 'pekerjaan_id');
    }

    // ------------------------------------------------------------------
    // Umur — dihitung, tidak pernah disimpan (PRD 2.4)
    // ------------------------------------------------------------------

    /**
     * Umur dalam tahun penuh terhadap hari ini. Null bila tanggal lahir
     * belum diisi.
     */
    public function umur(?CarbonInterface $acuan = null): ?int
    {
        return $this->tanggal_lahir?->diffInYears($acuan ?? now());
    }

    public function kelompokUmur(?CarbonInterface $acuan = null): KelompokUmur
    {
        return KelompokUmur::dariUmur($this->umur($acuan));
    }

    public function umurTerbaca(): string
    {
        $umur = $this->umur();

        return $umur === null ? 'Tidak diketahui' : "{$umur} tahun";
    }

    // ------------------------------------------------------------------
    // Status
    // ------------------------------------------------------------------

    public function terarsip(): bool
    {
        return $this->trashed();
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    /**
     * Warga yang masih tercatat sebagai penduduk desa.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeTerarsip(Builder $query): Builder
    {
        return $query->onlyTrashed();
    }

    /**
     * Kelahiran pada satu tahun — PRD 2.7.
     *
     * Bukan "baris yang ditambahkan tahun ini", melainkan warga yang memang
     * dicatat lahir. Warga terarsip tetap dihitung: bayi yang lahir lalu
     * meninggal pada tahun yang sama tetap sebuah kelahiran.
     */
    public function scopeLahirPadaTahun(Builder $query, int $tahun): Builder
    {
        return $query->withTrashed()
            ->where('jenis_masuk', JenisMasuk::Lahir)
            ->whereYear('tanggal_masuk', $tahun);
    }

    /**
     * Kematian pada satu tahun — PRD 2.7.
     *
     * Warga terarsip dengan alasan "duplikat" atau "pindah keluar" tidak
     * ikut terhitung.
     */
    public function scopeMeninggalPadaTahun(Builder $query, int $tahun): Builder
    {
        return $query->onlyTrashed()
            ->where('alasan_keluar', AlasanKeluar::Meninggal)
            ->whereYear('tanggal_keluar', $tahun);
    }

    public function scopeMasukPadaTahun(Builder $query, JenisMasuk $jenis, int $tahun): Builder
    {
        return $query->withTrashed()
            ->where('jenis_masuk', $jenis)
            ->whereYear('tanggal_masuk', $tahun);
    }

    public function scopeKeluarPadaTahun(Builder $query, AlasanKeluar $alasan, int $tahun): Builder
    {
        return $query->onlyTrashed()
            ->where('alasan_keluar', $alasan)
            ->whereYear('tanggal_keluar', $tahun);
    }

    /**
     * Pencarian nama, termasuk pencocokan sebagian kata.
     */
    public function scopeCariNama(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where('nama_lengkap', 'like', '%'.trim($kata).'%');
    }

    public function scopeDiKelompokUmur(Builder $query, KelompokUmur $kelompok): Builder
    {
        return $kelompok->terapkanFilter($query);
    }
}
