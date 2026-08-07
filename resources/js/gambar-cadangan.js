/**
 * Pengganti gambar yang gagal dimuat.
 *
 * LayananUnggahGambar tidak lagi memeriksa keberadaan berkas saat membentuk
 * URL — pemeriksaan itu memaksa satu permintaan ke penyimpanan untuk setiap
 * gambar di setiap kunjungan. Konsekuensinya, berkas yang benar-benar hilang
 * kini sampai ke peramban sebagai gambar rusak. Di sinilah ia ditangani.
 *
 * Peristiwa `error` pada <img> tidak menggelembung, jadi penyimaknya dipasang
 * pada fase capture.
 */

const PLACEHOLDER_BAWAAN = '/images/placeholder/umum.svg';

document.addEventListener(
    'error',
    (peristiwa) => {
        const el = peristiwa.target;

        if (!(el instanceof HTMLImageElement) || el.dataset.cadanganDipakai) {
            return;
        }

        // Ditandai supaya placeholder yang juga gagal dimuat tidak
        // memicu penggantian berulang tanpa henti.
        el.dataset.cadanganDipakai = '1';
        el.src = el.dataset.cadangan ?? PLACEHOLDER_BAWAAN;
    },
    true,
);
