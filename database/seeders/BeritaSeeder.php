<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BeritaDesa;
use App\Models\KategoriBerita;
use Illuminate\Database\Seeder;

class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pengumuman', 'Kegiatan', 'Pembangunan', 'Kesehatan'] as $nama) {
            KategoriBerita::firstOrCreate(['nama' => $nama]);
        }

        if (BeritaDesa::exists()) {
            return;
        }

        $kategori = KategoriBerita::pluck('id', 'nama');
        $adminId = Admin::value('id');

        $berita = [
            ['Musyawarah Desa Bahas APBDes 2027', 'Pengumuman', 12, true,
                'Musyawarah desa digelar di balai desa dan dihadiri seluruh ketua RT, ketua RW, perwakilan BPD, serta tokoh masyarakat.'],
            ['Posyandu Anggrek Buka Setiap Tanggal 10', 'Kesehatan', 25, false,
                'Layanan meliputi penimbangan balita, imunisasi, dan pemeriksaan lansia. Warga diimbau membawa buku KIA.'],
            ['Pengaspalan Jalan Dusun Krajan Dimulai', 'Pembangunan', 5, false,
                'Pengerjaan dimulai dari simpang balai desa sepanjang 800 meter dan diperkirakan selesai dalam tiga minggu.'],
            ['Kerja Bakti Bersihkan Saluran Air', 'Kegiatan', 18, false,
                'Kerja bakti dilaksanakan serentak di seluruh dusun menjelang musim hujan.'],
            ['Pendaftaran Bantuan Pangan Dibuka', 'Pengumuman', 3, false,
                'Warga yang memenuhi syarat dapat mendaftar di kantor desa pada jam kerja.'],
            ['Pelatihan Pengolahan Singkong untuk UMKM', 'Kegiatan', 40, false,
                'Pelatihan diikuti 25 pelaku UMKM desa dan menghadirkan pendamping dari dinas terkait.'],
            ['Perbaikan Jaringan Air Bersih Dusun Cimekar', 'Pembangunan', 55, false,
                'Perbaikan mencakup penggantian pipa utama sepanjang 400 meter.'],
            ['Imunisasi Tambahan untuk Balita', 'Kesehatan', 70, false,
                'Pelaksanaan bekerja sama dengan Puskesmas Kecamatan Cibeureum.'],
            ['Lomba Kebersihan Antar-RT', 'Kegiatan', 90, false,
                'Penilaian meliputi kebersihan lingkungan, penataan tanaman, dan pengelolaan sampah rumah tangga.'],
            ['Jadwal Pelayanan Administrasi Desa', 'Pengumuman', 110, false,
                'Pelayanan surat-menyurat dibuka Senin sampai Jumat pukul 08.00 hingga 14.00.'],
        ];

        foreach ($berita as [$judul, $namaKategori, $hariLalu, $utama, $ringkasan]) {
            BeritaDesa::create([
                'judul' => $judul,
                'kategori_id' => $kategori[$namaKategori],
                'ringkasan' => $ringkasan,
                'isi' => '<p>'.$ringkasan.'</p>'
                    .'<p>Kegiatan ini merupakan bagian dari program kerja pemerintah desa tahun berjalan. '
                    .'Warga yang membutuhkan informasi lebih lanjut dapat menghubungi kantor desa atau ketua RT masing-masing.</p>'
                    .'<h2>Informasi Tambahan</h2>'
                    .'<ul><li>Tempat: Balai Desa Sukamaju</li><li>Narahubung: Sekretaris Desa</li></ul>',
                'status' => 'terbit',
                'utama' => $utama,
                'tanggal_terbit' => now()->subDays($hariLalu),
                'dilihat' => mt_rand(15, 480),
                'admin_id' => $adminId,
            ]);
        }

        BeritaDesa::create([
            'judul' => 'Draf: Rencana Pembangunan Jembatan Dusun Sukasari',
            'kategori_id' => $kategori['Pembangunan'],
            'ringkasan' => 'Masih dalam tahap penyusunan anggaran.',
            'isi' => '<p>Naskah masih disusun dan belum siap diterbitkan.</p>',
            'status' => 'draf',
            'admin_id' => $adminId,
        ]);
    }
}
