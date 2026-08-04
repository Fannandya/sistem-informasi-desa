/*
 * Editor teks kaya untuk isi berita (fitur 12).
 *
 * Entry terpisah supaya Trix hanya diunduh pada halaman tulis berita, bukan
 * di setiap halaman admin.
 */
import Trix from 'trix';
import 'trix/dist/trix.css';

// Unggah berkas lewat editor sengaja dimatikan: gambar berita masuk lewat
// kolom sampul yang melewati LayananUnggahGambar, sehingga semua unggahan
// tetap satu jalur (validasi, kompresi, penamaan acak).
document.addEventListener('trix-file-accept', (e) => {
    e.preventDefault();
    alert('Penyisipan gambar langsung di editor tidak didukung. Gunakan kolom Gambar Sampul.');
});
