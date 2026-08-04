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

    const pesan = {
        jenis: 'bahaya',
        judul: 'Gambar tidak bisa disisipkan di sini',
        pesan: 'Penyisipan gambar langsung ke dalam tulisan tidak didukung.',
        catatan: 'Gunakan kolom Gambar Sampul di bawah editor. Semua gambar melewati satu jalur '
            + 'agar ukurannya dikecilkan dan namanya diacak.',
    };

    // Memakai pusat dialog panel admin, bukan alert() bawaan peramban.
    if (window.Dialog) {
        window.Dialog.beriTahu(pesan);
    } else {
        console.warn(pesan.pesan);
    }
});
