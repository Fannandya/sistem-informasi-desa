import {
    Chart,
    BarController, BarElement,
    LineController, LineElement, PointElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    Tooltip, Legend, Filler,
} from 'chart.js';

// Hanya komponen yang benar-benar dipakai yang didaftarkan, supaya berkas
// bundel tetap kecil — halaman ini dibuka lewat koneksi seluler desa.
Chart.register(
    BarController, BarElement,
    LineController, LineElement, PointElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    Tooltip, Legend, Filler,
);

Chart.defaults.font.family =
    'ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;

/** Palet dipakai konsisten di dashboard admin dan statistik publik. */
const WARNA = {
    utama: '#059669',
    utamaMuda: '#6ee7b7',
    biru: '#2563eb',
    biruMuda: '#93c5fd',
    ungu: '#7c3aed',
    jingga: '#ea580c',
    abu: '#94a3b8',
    deret: ['#059669', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#ca8a04', '#db2777', '#65a30d', '#4f46e5', '#0d9488', '#94a3b8'],
};

const angka = (n) => new Intl.NumberFormat('id-ID').format(n);

/**
 * Memuat data grafik lalu merendernya.
 *
 * Setiap kanvas mengurus dirinya sendiri: satu endpoint gagal tidak
 * mengosongkan seluruh dashboard, hanya kartu itu yang menampilkan pesan.
 */
async function muatGrafik(idKanvas, url, bangunKonfigurasi) {
    const kanvas = document.getElementById(idKanvas);
    if (!kanvas) return;

    const wadah = kanvas.closest('[data-grafik]');
    const statusMuat = wadah?.querySelector('[data-status="muat"]');
    const statusKosong = wadah?.querySelector('[data-status="kosong"]');
    const statusGagal = wadah?.querySelector('[data-status="gagal"]');

    const tampilkan = (el) => {
        [statusMuat, statusKosong, statusGagal].forEach((n) => n?.classList.add('hidden'));
        kanvas.classList.add('hidden');
        if (el === kanvas) kanvas.classList.remove('hidden');
        else el?.classList.remove('hidden');
    };

    try {
        const respons = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!respons.ok) throw new Error(`HTTP ${respons.status}`);

        const muatan = await respons.json();
        const baris = muatan.data ?? [];

        const adaIsi = baris.some((b) => (b.jumlah ?? b.jumlah_warga ?? b.kelahiran ?? b.laki ?? b.perempuan ?? 0) > 0);
        if (!adaIsi) {
            tampilkan(statusKosong);
            return;
        }

        tampilkan(kanvas);

        const lama = Chart.getChart(kanvas);
        if (lama) lama.destroy();

        new Chart(kanvas, bangunKonfigurasi(baris, muatan));
    } catch (e) {
        console.error(`Gagal memuat grafik ${idKanvas}:`, e);
        tampilkan(statusGagal);
    }
}

// ---------------------------------------------------------------------------
// Konfigurasi tiap grafik
// ---------------------------------------------------------------------------

const konfigurasiSebaranUmur = (baris) => ({
    type: 'bar',
    data: {
        labels: baris.map((b) => b.label),
        datasets: [{
            label: 'Jumlah warga',
            data: baris.map((b) => b.jumlah),
            backgroundColor: WARNA.utama,
            borderRadius: 6,
            maxBarThickness: 56,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (k) => `${angka(k.raw)} warga (${baris[k.dataIndex].persen}%)`,
                    afterLabel: (k) => baris[k.dataIndex].rentang,
                },
            },
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } },
        },
    },
});

const konfigurasiRasioGender = (baris) => ({
    type: 'doughnut',
    data: {
        labels: baris.map((b) => b.label),
        datasets: [{
            data: baris.map((b) => b.jumlah),
            backgroundColor: [WARNA.biru, WARNA.ungu],
            borderWidth: 0,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: (k) => `${k.label}: ${angka(k.raw)} (${baris[k.dataIndex].persen}%)`,
                },
            },
        },
    },
});

const konfigurasiMataPencaharian = (baris) => ({
    type: 'bar',
    data: {
        labels: baris.map((b) => b.label),
        datasets: [{
            label: 'Jumlah warga',
            data: baris.map((b) => b.jumlah),
            backgroundColor: baris.map((b) =>
                b.label === 'Belum diisi' ? WARNA.abu : WARNA.utama),
            borderRadius: 5,
            maxBarThickness: 22,
        }],
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (k) => `${angka(k.raw)} warga (${baris[k.dataIndex].persen}%)`,
                },
            },
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
            y: { grid: { display: false } },
        },
    },
});

const konfigurasiTrenMutasi = (baris) => ({
    type: 'line',
    data: {
        labels: baris.map((b) => b.tahun),
        datasets: [
            {
                label: 'Kelahiran',
                data: baris.map((b) => b.kelahiran),
                borderColor: WARNA.utama,
                backgroundColor: 'rgba(5, 150, 105, 0.08)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Kematian',
                data: baris.map((b) => b.kematian),
                borderColor: WARNA.jingga,
                backgroundColor: 'transparent',
                tension: 0.3,
            },
            {
                label: 'Pindah masuk',
                data: baris.map((b) => b.pindah_masuk),
                borderColor: WARNA.biruMuda,
                borderDash: [5, 4],
                backgroundColor: 'transparent',
                tension: 0.3,
            },
            {
                label: 'Pindah keluar',
                data: baris.map((b) => b.pindah_keluar),
                borderColor: WARNA.abu,
                borderDash: [5, 4],
                backgroundColor: 'transparent',
                tension: 0.3,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } },
        },
    },
});

const konfigurasiPendidikan = (baris) => ({
    type: 'bar',
    data: {
        labels: baris.map((b) => b.label),
        datasets: [{
            label: 'Jumlah warga',
            data: baris.map((b) => b.jumlah),
            backgroundColor: baris.map((b, i) =>
                b.label === 'Belum diisi' ? WARNA.abu : WARNA.deret[i % WARNA.deret.length]),
            borderRadius: 5,
            maxBarThickness: 40,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (k) => `${angka(k.raw)} warga (${baris[k.dataIndex].persen}%)`,
                },
            },
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } },
        },
    },
});

const konfigurasiPiramida = (baris) => ({
    type: 'bar',
    data: {
        labels: baris.map((b) => b.label),
        datasets: [
            {
                label: 'Laki-laki',
                data: baris.map((b) => (b.laki === null ? 0 : -b.laki)),
                backgroundColor: WARNA.biru,
                maxBarThickness: 16,
            },
            {
                label: 'Perempuan',
                data: baris.map((b) => b.perempuan ?? 0),
                backgroundColor: WARNA.ungu,
                maxBarThickness: 16,
            },
        ],
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: (k) => {
                        const b = baris[k.dataIndex];
                        const laki = k.dataset.label === 'Laki-laki';
                        const nilai = laki ? b.laki : b.perempuan;
                        const tampilan = laki ? b.laki_tampilan : b.perempuan_tampilan;

                        return nilai === null
                            ? `${k.dataset.label}: kurang dari 5 orang`
                            : `${k.dataset.label}: ${tampilan} warga`;
                    },
                },
            },
        },
        scales: {
            x: {
                stacked: true,
                ticks: { callback: (v) => angka(Math.abs(v)), precision: 0 },
                grid: { color: '#f1f5f9' },
            },
            y: { stacked: true, grid: { display: false } },
        },
    },
});

// Diekspos sebagai objek global, bukan modul ES, supaya halaman Blade cukup
// memuatnya lewat @vite tanpa perlu tahu nama berkas hasil build.
window.Grafik = {
    muat: muatGrafik,
    WARNA,
    sebaranUmur: konfigurasiSebaranUmur,
    rasioGender: konfigurasiRasioGender,
    mataPencaharian: konfigurasiMataPencaharian,
    pendidikan: konfigurasiPendidikan,
    trenMutasi: konfigurasiTrenMutasi,
    piramida: konfigurasiPiramida,
};
