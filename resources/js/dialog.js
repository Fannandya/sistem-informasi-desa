/*
 * Pusat dialog panel admin.
 *
 * Menggantikan confirm() dan alert() bawaan peramban. Dialog bawaan tidak
 * bisa diberi penjelasan panjang, tidak bisa menuntut admin mengetik ulang
 * nama sebagai pengaman, dan tampil berbeda-beda di tiap peramban.
 *
 * Hanya ada SATU dialog di dalam DOM. Halaman daftar bisa memuat 50 baris;
 * menempelkan satu modal per baris membuat dokumennya membengkak tanpa alasan.
 * Tombol cukup menandai formulirnya dengan atribut data-*, dan berkas ini yang
 * mengurus sisanya.
 */

const LEWATI = 'dialogSudahDisetujui';

let el = {};
let sedangDitangani = null;

function siapkanElemen() {
    el = {
        akar: document.getElementById('pusatDialog'),
        panel: document.getElementById('dialogPanel'),
        ikon: document.getElementById('dialogIkon'),
        judul: document.getElementById('dialogJudul'),
        pesan: document.getElementById('dialogPesan'),
        catatan: document.getElementById('dialogCatatan'),
        rincian: document.getElementById('dialogRincian'),
        blokKetik: document.getElementById('dialogBlokKetik'),
        labelKetik: document.getElementById('dialogLabelKetik'),
        inputKetik: document.getElementById('dialogInputKetik'),
        setuju: document.getElementById('dialogSetuju'),
        batal: document.getElementById('dialogBatal'),
    };

    return Boolean(el.akar);
}

const GAYA = {
    bahaya: {
        ikon: 'bg-red-100 text-red-600',
        tombol: 'bg-red-600 hover:bg-red-700 focus:ring-red-500/40',
        ikonMateri: 'warning',
    },
    sukses: {
        ikon: 'bg-emerald-100 text-emerald-600',
        tombol: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500/40',
        ikonMateri: 'check_circle',
    },
    tanya: {
        ikon: 'bg-primary/10 text-primary',
        tombol: 'bg-primary hover:bg-primary-container focus:ring-primary/40',
        ikonMateri: 'help',
    },
};

/**
 * @param {object} pilihan
 * @param {string}   pilihan.judul
 * @param {string}   [pilihan.pesan]
 * @param {string}   [pilihan.catatan]   peringatan tambahan bernada waspada
 * @param {string[]} [pilihan.rincian]   daftar butir, mis. pesan validasi
 * @param {string}   [pilihan.tombol]    label tombol utama
 * @param {string}   [pilihan.batal]     label tombol batal; kosongkan untuk dialog informasi
 * @param {string}   [pilihan.ketik]     teks yang harus diketik ulang admin
 * @param {'bahaya'|'sukses'|'tanya'} [pilihan.jenis]
 * @returns {Promise<boolean>}
 */
export function bukaDialog(pilihan) {
    if (!el.akar && !siapkanElemen()) {
        // Tanpa pusat dialog di halaman ini, aksi diteruskan apa adanya
        // supaya admin tidak terjebak tombol yang tidak melakukan apa-apa.
        return Promise.resolve(true);
    }

    const jenis = pilihan.jenis ?? 'tanya';
    const gaya = GAYA[jenis] ?? GAYA.tanya;

    el.ikon.className = `flex h-11 w-11 shrink-0 items-center justify-center rounded-full ${gaya.ikon}`;
    const ikonSpan = el.ikon.querySelector('.material-symbols-outlined');
    if (ikonSpan) {
        ikonSpan.textContent = gaya.ikonMateri;
    }

    el.judul.textContent = pilihan.judul ?? 'Konfirmasi';

    el.pesan.textContent = pilihan.pesan ?? '';
    el.pesan.classList.toggle('hidden', !pilihan.pesan);

    el.catatan.textContent = pilihan.catatan ?? '';
    el.catatan.classList.toggle('hidden', !pilihan.catatan);

    // replaceChildren + textContent, bukan innerHTML: isi rincian berasal dari
    // pesan validasi yang bisa memuat nama warga hasil ketikan admin.
    el.rincian.replaceChildren();
    const rincian = pilihan.rincian ?? [];
    rincian.forEach((baris) => {
        const li = document.createElement('li');
        li.textContent = baris;
        el.rincian.appendChild(li);
    });
    el.rincian.classList.toggle('hidden', rincian.length === 0);

    // Pengaman ketik-ulang untuk aksi yang tidak bisa dibatalkan.
    const ketik = pilihan.ketik ?? '';
    el.blokKetik.classList.toggle('hidden', !ketik);
    el.inputKetik.value = '';
    if (ketik) {
        el.labelKetik.textContent = ketik;
        el.inputKetik.placeholder = ketik;
    }

    el.setuju.textContent = pilihan.tombol ?? 'Lanjutkan';
    el.setuju.className =
        `rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition focus:ring-2 focus:outline-none ${gaya.tombol}`;

    el.batal.textContent = pilihan.batal ?? 'Batal';
    el.batal.classList.toggle('hidden', pilihan.batal === null);

    const perbaruiTombol = () => {
        const boleh = !ketik || el.inputKetik.value.trim() === ketik.trim();
        el.setuju.disabled = !boleh;
        el.setuju.classList.toggle('cursor-not-allowed', !boleh);
        el.setuju.classList.toggle('opacity-40', !boleh);
    };
    perbaruiTombol();

    el.akar.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => (ketik ? el.inputKetik : el.setuju).focus(), 50);

    return new Promise((selesai) => {
        const tutup = (hasil) => {
            el.akar.classList.add('hidden');
            document.body.style.overflow = '';
            el.inputKetik.removeEventListener('input', perbaruiTombol);
            el.setuju.removeEventListener('click', padaSetuju);
            el.batal.removeEventListener('click', padaBatal);
            el.akar.removeEventListener('click', padaLatar);
            document.removeEventListener('keydown', padaTombolPapanKetik);
            selesai(hasil);
        };

        const padaSetuju = () => { if (!el.setuju.disabled) tutup(true); };
        const padaBatal = () => tutup(false);
        const padaLatar = (e) => { if (e.target === el.akar) tutup(false); };
        const padaTombolPapanKetik = (e) => {
            if (e.key === 'Escape') tutup(false);
            if (e.key === 'Enter' && ketik && document.activeElement === el.inputKetik && !el.setuju.disabled) {
                e.preventDefault();
                tutup(true);
            }
        };

        el.inputKetik.addEventListener('input', perbaruiTombol);
        el.setuju.addEventListener('click', padaSetuju);
        el.batal.addEventListener('click', padaBatal);
        el.akar.addEventListener('click', padaLatar);
        document.addEventListener('keydown', padaTombolPapanKetik);
    });
}

/** Dialog informasi: hanya satu tombol tutup. */
export function beriTahu(pilihan) {
    return bukaDialog({ ...pilihan, batal: null, tombol: pilihan.tombol ?? 'Mengerti' });
}

// ---------------------------------------------------------------------------
// Konfirmasi otomatis untuk formulir bertanda data-konfirmasi
// ---------------------------------------------------------------------------

function bacaAtribut(form) {
    const d = form.dataset;

    return {
        judul: d.judul || 'Konfirmasi',
        pesan: d.pesan || '',
        catatan: d.catatan || '',
        tombol: d.tombol || 'Lanjutkan',
        ketik: d.ketik || '',
        jenis: d.jenis || (d.bahaya !== undefined ? 'bahaya' : 'tanya'),
    };
}

function pasangKonfirmasiFormulir() {
    document.addEventListener('submit', async (e) => {
        const form = e.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.konfirmasi === undefined) {
            return;
        }

        if (form[LEWATI]) {
            form[LEWATI] = false;

            return;
        }

        e.preventDefault();

        if (sedangDitangani) {
            return;
        }
        sedangDitangani = form;

        const setuju = await bukaDialog(bacaAtribut(form));
        sedangDitangani = null;

        if (setuju) {
            // Teks yang diketik admin ikut dikirim supaya server bisa
            // memverifikasinya sendiri. Pengaman yang hanya hidup di peramban
            // bukan pengaman.
            const namaField = form.dataset.ketikField;
            if (namaField) {
                let isian = form.querySelector(`input[name="${namaField}"]`);
                if (!isian) {
                    isian = document.createElement('input');
                    isian.type = 'hidden';
                    isian.name = namaField;
                    form.appendChild(isian);
                }
                isian.value = el.inputKetik.value.trim();
            }

            form[LEWATI] = true;
            // Formulir yang punya tombol submit bernama harus dikirim lewat
            // requestSubmit agar nilainya ikut terbawa.
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }
    }, true);
}

// ---------------------------------------------------------------------------
// Pesan dari server ditampilkan sebagai dialog
// ---------------------------------------------------------------------------

function tampilkanPesanServer() {
    const wadah = document.getElementById('pesanServer');

    if (!wadah) {
        return;
    }

    let muatan;
    try {
        muatan = JSON.parse(wadah.textContent);
    } catch {
        return;
    }

    if (muatan.galat) {
        beriTahu({ jenis: 'bahaya', judul: 'Tidak dapat dilanjutkan', pesan: muatan.galat });

        return;
    }

    if (muatan.kesalahan?.length) {
        beriTahu({
            jenis: 'bahaya',
            judul: 'Perubahan belum tersimpan',
            pesan: 'Periksa kembali isian berikut:',
            rincian: muatan.kesalahan,
        });

        return;
    }

    if (muatan.status) {
        beriTahu({ jenis: 'sukses', judul: 'Berhasil', pesan: muatan.status });
    }
}

// ---------------------------------------------------------------------------
// Dirty tracking
// ---------------------------------------------------------------------------

/** Cuplikan isi formulir sebagai teks, untuk dibandingkan nanti. */
function cuplikan(form) {
    return Array.from(form.elements)
        .filter((n) => n.name && n.type !== 'hidden' && !n.disabled)
        .map((n) => {
            if (n.type === 'checkbox' || n.type === 'radio') {
                return `${n.name}=${n.checked ? n.value : ''}`;
            }

            return `${n.name}=${n.value}`;
        })
        .join('&');
}

function pasangDirtyTracking() {
    const formulir = Array.from(document.querySelectorAll('form[data-lacak-perubahan]'));

    if (formulir.length === 0) {
        return;
    }

    const awal = new Map();
    formulir.forEach((f) => awal.set(f, cuplikan(f)));

    const adaPerubahan = () => formulir.some((f) => cuplikan(f) !== awal.get(f));

    // Mengirim formulir berarti perubahannya memang disengaja.
    formulir.forEach((f) => f.addEventListener('submit', () => awal.set(f, cuplikan(f))));

    document.addEventListener('click', async (e) => {
        const tautan = e.target.closest('a[href]');

        if (!tautan || !adaPerubahan()) {
            return;
        }

        const url = tautan.getAttribute('href');

        // Tautan dalam halaman, unduhan, dan tab baru tidak meninggalkan
        // formulir, jadi tidak perlu diperingatkan.
        if (!url || url.startsWith('#') || tautan.target === '_blank' || tautan.hasAttribute('download')) {
            return;
        }

        e.preventDefault();

        const lanjut = await bukaDialog({
            jenis: 'bahaya',
            judul: 'Perubahan belum disimpan',
            pesan: 'Ada isian yang sudah Anda ubah tetapi belum ditekan Simpan.',
            catatan: 'Bila Anda meninggalkan halaman ini sekarang, perubahan tersebut hilang.',
            tombol: 'Tinggalkan halaman',
            batal: 'Tetap di sini',
        });

        if (lanjut) {
            window.location.href = url;
        }
    }, true);

    // Jaring pengaman untuk tab yang ditutup atau dimuat ulang. Peramban
    // memaksakan dialognya sendiri di sini dan tidak bisa digantikan halaman
    // mana pun — tanpa ini, menutup tab membuang tulisan tanpa peringatan.
    window.addEventListener('beforeunload', (e) => {
        if (adaPerubahan()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

// ---------------------------------------------------------------------------

function mulai() {
    siapkanElemen();
    pasangKonfirmasiFormulir();
    pasangDirtyTracking();
    tampilkanPesanServer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}

window.Dialog = { buka: bukaDialog, beriTahu };
