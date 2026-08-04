<?php

namespace App\Imports;

/**
 * Fitur 04 — definisi kolom berkas impor.
 *
 * Judul kolom ditetapkan di satu tempat karena tiga hal harus memakainya
 * sama persis: berkas contoh yang diunduh admin, pembacaan berkas saat
 * impor, dan berkas hasil ekspor (fitur 07). Kalau ketiganya menulis
 * judulnya sendiri, berkas hasil ekspor tidak akan bisa diimpor kembali.
 */
class KolomImporPenduduk
{
    /**
     * Judul kolom yang diharapkan → nama kolom internal.
     *
     * @var array<string, string>
     */
    public const PETA = [
        'nama lengkap' => 'nama_lengkap',
        'tanggal lahir' => 'tanggal_lahir',
        'jenis kelamin' => 'jenis_kelamin',
        'dusun' => 'dusun',
        'rw' => 'rw',
        'rt' => 'rt',
        'pekerjaan' => 'pekerjaan',
        'pendidikan' => 'pendidikan',
    ];

    /** Kolom yang wajib ada di berkas. */
    public const WAJIB = ['nama lengkap', 'jenis kelamin', 'dusun', 'rw', 'rt'];

    /**
     * Kolom yang dilarang keras (PRD 2.4). Berkas yang memuat salah satunya
     * ditolak sebelum satu baris pun diproses — bukan diabaikan diam-diam,
     * karena admin perlu tahu berkas sumbernya mengandung data terlarang.
     *
     * @var array<int, string>
     */
    public const TERLARANG = [
        'nik', 'no nik', 'nomor nik', 'nomor induk kependudukan',
        'kk', 'no kk', 'nomor kk', 'kartu keluarga', 'nomor kartu keluarga',
        'telepon', 'no telepon', 'nomor telepon', 'hp', 'no hp', 'nomor hp',
        'telp', 'whatsapp', 'wa',
        'alamat', 'alamat rumah', 'jalan',
        'agama', 'penghasilan', 'gaji', 'golongan darah', 'gol darah',
    ];

    /**
     * Judul kolom sebagaimana ditulis di berkas contoh dan berkas ekspor.
     *
     * Ditulis eksplisit, bukan hasil ucwords(): RT dan RW adalah singkatan
     * yang akan menjadi "Rt" dan "Rw" bila diproses otomatis.
     *
     * @var array<int, string>
     */
    public const JUDUL = [
        'Nama Lengkap',
        'Tanggal Lahir',
        'Jenis Kelamin',
        'Dusun',
        'RW',
        'RT',
        'Pekerjaan',
        'Pendidikan',
    ];

    /** @return array<int, string> */
    public static function judul(): array
    {
        return self::JUDUL;
    }

    /**
     * Menormalkan judul kolom dari berkas agar perbedaan huruf besar-kecil,
     * spasi ganda, dan garis bawah tidak membuat kolom dianggap tidak ada.
     */
    public static function normalkan(?string $judul): string
    {
        $judul = mb_strtolower(trim((string) $judul));
        $judul = str_replace(['_', '-', '.'], ' ', $judul);

        return preg_replace('/\s+/', ' ', $judul) ?? '';
    }

    public static function terlarang(string $judulTernormalisasi): bool
    {
        return in_array($judulTernormalisasi, self::TERLARANG, true);
    }
}
