<?php

namespace App\Imports;

use App\Models\Penduduk;

/**
 * Satu baris berkas impor yang sudah dibaca dan diterjemahkan.
 *
 * Objek ini dipakai dua kali: saat halaman pratinjau menampilkan apa yang akan
 * terjadi, dan saat admin menekan Lanjut. Keduanya memakai hasil pembacaan
 * yang sama persis, sehingga yang tersimpan tidak mungkin berbeda dari yang
 * ditampilkan.
 */
class BarisImpor
{
    /**
     * @param  int  $nomorBaris  nomor baris pada berkas asli, termasuk baris judul
     * @param  array<string, mixed>  $data  siap dikirim ke Penduduk::create()
     * @param  Penduduk|null  $miripDengan  warga yang sudah ada, bila terdeteksi ganda
     * @param  int|null  $gandaDenganBaris  nomor baris lain di berkas yang sama
     */
    public function __construct(
        public readonly int $nomorBaris,
        public readonly array $data,
        public readonly string $nama,
        public readonly ?string $tanggalLahir,
        public readonly string $jenisKelamin,
        public readonly string $wilayah,
        public readonly ?string $pekerjaan,
        public readonly ?string $pendidikan,
        public ?Penduduk $miripDengan = null,
        public ?int $gandaDenganBaris = null,
    ) {}

    public function terdeteksiGanda(): bool
    {
        return $this->miripDengan !== null || $this->gandaDenganBaris !== null;
    }

    /**
     * Penjelasan singkat mengapa baris ini dianggap ganda, untuk ditampilkan
     * ke admin agar ia bisa menilai sendiri.
     */
    public function alasanGanda(): string
    {
        if ($this->gandaDenganBaris !== null) {
            return "Sama dengan baris {$this->gandaDenganBaris} pada berkas ini.";
        }

        if ($this->miripDengan === null) {
            return '';
        }

        return $this->miripDengan->trashed()
            ? 'Sudah ada di arsip warga.'
            : 'Sudah ada di daftar warga aktif.';
    }

    public function tanggalLahirTerbaca(): string
    {
        return $this->tanggalLahir ?? 'tidak diisi';
    }
}
