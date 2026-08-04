<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Exceptions\UnggahanGagal;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Penanganan unggah, ganti, dan hapus gambar pada formulir konten.
 *
 * Dipakai fitur 09–13 yang polanya sama persis. Tanpa ini, setiap controller
 * akan menyalin urutan "simpan baru dulu, hapus lama belakangan" — dan satu
 * salinan yang keliru urutannya cukup untuk membuat data kehilangan fotonya
 * saat penyimpanan gagal di tengah.
 */
trait MengelolaGambar
{
    /**
     * @param  string  $jenis  kunci pada config('unggahan.folder')
     * @return array<string, string|null>
     *
     * @throws UnggahanGagal
     */
    protected function prosesGambar(
        Request $request,
        Model $model,
        string $jenis,
        string $kolom = 'foto',
    ): array {
        if ($request->hasFile($kolom)) {
            return [$kolom => app(LayananUnggahGambar::class)->ganti(
                $model->{$kolom},
                $request->file($kolom),
                $jenis,
            )];
        }

        if ($request->boolean("hapus_{$kolom}")) {
            app(LayananUnggahGambar::class)->hapus($model->{$kolom});

            return [$kolom => null];
        }

        return [];
    }
}
