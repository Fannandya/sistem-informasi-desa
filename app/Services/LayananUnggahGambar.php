<?php

namespace App\Services;

use App\Exceptions\UnggahanGagal;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Fitur 02 — satu-satunya jalur unggah gambar di aplikasi ini.
 *
 * Seluruh fitur yang mengunggah foto memanggil layanan ini, sehingga aturan
 * validasi, penamaan, kompresi, dan penyimpanan tidak pernah berbeda antar
 * fitur. Disk tujuan ditentukan config/unggahan.php, bukan oleh pemanggil.
 */
class LayananUnggahGambar
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Aturan validasi berkas gambar, untuk dipakai ulang di FormRequest.
     *
     * @return array<int, mixed>
     */
    public static function aturanValidasi(bool $wajib = false): array
    {
        return array_filter([
            $wajib ? 'required' : 'nullable',
            'image',
            'mimes:'.implode(',', config('unggahan.ekstensi_diizinkan')),
            'max:'.config('unggahan.maks_kb'),
        ]);
    }

    /**
     * Menyimpan satu gambar beserta thumbnailnya.
     *
     * @param  string  $jenis  Kunci pada config('unggahan.folder'), mis. 'potensi'.
     * @return string Path gambar utama di disk, mis. `potensi/a1b2c3.webp`.
     *
     * @throws UnggahanGagal
     */
    public function simpan(UploadedFile $berkas, string $jenis): string
    {
        $this->pastikanBerkasSah($berkas);

        $folder = $this->folder($jenis);
        $namaBerkas = Str::random(32).'.webp';
        $path = $folder.'/'.$namaBerkas;
        $pathThumbnail = $this->pathThumbnail($path);

        try {
            $gambar = $this->manager->read($berkas->getRealPath());
        } catch (Throwable $e) {
            throw UnggahanGagal::berkasRusak($e);
        }

        // scaleDown tidak pernah memperbesar gambar yang sudah lebih kecil.
        $utama = (string) $gambar
            ->scaleDown(width: config('unggahan.lebar_maks'))
            ->toWebp(quality: config('unggahan.kualitas_webp'));

        $thumbnail = (string) $this->manager->read($berkas->getRealPath())
            ->scaleDown(width: config('unggahan.lebar_thumbnail'))
            ->toWebp(quality: config('unggahan.kualitas_webp'));

        try {
            $this->disk()->put($path, $utama);
            $this->disk()->put($pathThumbnail, $thumbnail);
        } catch (Throwable $e) {
            // Jangan tinggalkan berkas separuh jadi bila salah satu gagal.
            $this->hapusDiam($path);
            $this->hapusDiam($pathThumbnail);

            throw UnggahanGagal::penyimpananTidakTerjangkau($e);
        }

        return $path;
    }

    /**
     * Mengganti gambar: menyimpan yang baru lebih dulu, baru menghapus yang lama.
     *
     * Urutannya disengaja — bila penyimpanan gambar baru gagal, gambar lama
     * masih utuh dan data tidak kehilangan fotonya.
     *
     * @throws UnggahanGagal
     */
    public function ganti(?string $pathLama, UploadedFile $berkasBaru, string $jenis): string
    {
        $pathBaru = $this->simpan($berkasBaru, $jenis);

        $this->hapus($pathLama);

        return $pathBaru;
    }

    /**
     * Menghapus gambar beserta thumbnailnya.
     *
     * Kegagalan penghapusan tidak melempar exception — data tetap boleh
     * tersimpan meski berkas lama tertinggal. Berkas tertinggal terdeteksi
     * lewat `php artisan unggahan:berkas-yatim`.
     */
    public function hapus(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $this->hapusDiam($path);
        $this->hapusDiam($this->pathThumbnail($path));
    }

    /**
     * URL publik gambar utama, atau gambar pengganti bila belum ada foto.
     */
    public function url(?string $path, string $jenis = 'bawaan'): string
    {
        if (blank($path) || ! $this->disk()->exists($path)) {
            return $this->urlPlaceholder($jenis);
        }

        return $this->disk()->url($path);
    }

    /**
     * URL thumbnail. Dipakai pada daftar, kartu, dan cuplikan landing page
     * agar halaman tidak memuat gambar ukuran penuh.
     */
    public function urlThumbnail(?string $path, string $jenis = 'bawaan'): string
    {
        if (blank($path)) {
            return $this->urlPlaceholder($jenis);
        }

        $thumbnail = $this->pathThumbnail($path);

        if ($this->disk()->exists($thumbnail)) {
            return $this->disk()->url($thumbnail);
        }

        // Thumbnail hilang bukan alasan menyembunyikan gambar.
        return $this->url($path, $jenis);
    }

    public function urlPlaceholder(string $jenis = 'bawaan'): string
    {
        return asset(
            config("unggahan.placeholder.{$jenis}", config('unggahan.placeholder.bawaan')),
        );
    }

    public function pathThumbnail(string $path): string
    {
        $folder = dirname($path);
        $nama = basename($path);
        $sub = config('unggahan.folder_thumbnail');

        return ($folder === '.' ? '' : $folder.'/').$sub.'/'.$nama;
    }

    public function disk(): Filesystem
    {
        return Storage::disk(config('unggahan.disk'));
    }

    private function folder(string $jenis): string
    {
        $folder = config("unggahan.folder.{$jenis}");

        if ($folder === null) {
            throw UnggahanGagal::jenisTidakDikenal($jenis);
        }

        return $folder;
    }

    private function pastikanBerkasSah(UploadedFile $berkas): void
    {
        if (! $berkas->isValid()) {
            throw UnggahanGagal::berkasTidakValid();
        }

        $ekstensi = mb_strtolower((string) $berkas->getClientOriginalExtension());

        if (! in_array($ekstensi, config('unggahan.ekstensi_diizinkan'), true)) {
            throw UnggahanGagal::formatTidakDidukung($ekstensi);
        }

        if (! in_array($berkas->getMimeType(), config('unggahan.mime_diizinkan'), true)) {
            throw UnggahanGagal::formatTidakDidukung((string) $berkas->getMimeType());
        }

        if ($berkas->getSize() > config('unggahan.maks_kb') * 1024) {
            throw UnggahanGagal::terlaluBesar();
        }
    }

    private function hapusDiam(string $path): void
    {
        try {
            if ($this->disk()->exists($path)) {
                $this->disk()->delete($path);
            }
        } catch (Throwable $e) {
            Log::warning('Gagal menghapus berkas unggahan.', [
                'path' => $path,
                'disk' => config('unggahan.disk'),
                'pesan' => $e->getMessage(),
            ]);
        }
    }
}
