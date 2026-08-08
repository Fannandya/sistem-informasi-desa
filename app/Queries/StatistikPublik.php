<?php

namespace App\Queries;

use App\Models\Penduduk;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PRD 2.6 — statistik untuk halaman publik.
 *
 * Membungkus StatistikPenduduk lalu menerapkan aturan perlindungan sebelum
 * angkanya keluar dari aplikasi. Penyaringan dilakukan di lapisan data, bukan
 * disembunyikan di tampilan: angka yang hanya di-hide lewat CSS tetap terkirim
 * ke peramban dan bisa dibaca siapa saja lewat "lihat sumber halaman".
 *
 * Tiga perlindungan yang diterapkan:
 * 1. Kelompok berisi kurang dari 5 warga tidak pernah menjadi angka pasti —
 *    termasuk persennya. Di RT kecil, "1 orang lulusan S3" praktis menunjuk
 *    satu orang tertentu, dan pada desa berpenduduk ribuan satu angka di
 *    belakang koma sudah cukup untuk mengembalikan angka bulatnya.
 * 2. Bila hanya satu baris yang tersensor pada tabel yang habis dibagi,
 *    satu baris lain ikut ditutup — tanpa itu angka yang disensor bisa
 *    dihitung kembali dengan pengurangan. Lihat samarkanPelengkap().
 * 3. Rincian berhenti di tingkat dusun. Sebaran per RT terlalu sempit untuk
 *    tetap anonim.
 */
class StatistikPublik
{
    /** Ambang minimum agar sebuah kelompok boleh ditampilkan sebagai angka. */
    public const AMBANG = 5;

    /**
     * Keterangan untuk baris yang ditutup demi menghalangi pengurangan —
     * lihat samarkanPelengkap(). Sengaja bukan "<5": angkanya bisa saja
     * besar, dan yang hilang adalah angkanya, bukan ukurannya.
     */
    public const LABEL_DISAMARKAN = 'Disamarkan';

    private const PREFIX_CACHE = 'statistik_publik.';

    /** Seluruh kunci cache yang dikelola kelas ini. */
    private const KUNCI_CACHE = [
        'ringkasan',
        'sebaran_umur',
        'rasio_gender',
        'pendidikan',
        'sebaran_dusun',
        'jumlah_dusun',
        'tanggal_pembaruan',
        'piramida',
        'agama',
        'status_perkawinan',
        'pekerjaan',
    ];

    /**
     * Penanda bahwa cache sudah dikosongkan dalam permintaan ini.
     *
     * Impor warga menyimpan sampai 2.000 baris dalam satu transaksi, dan
     * setiap baris memicu invalidasi. Tanpa penanda ini, satu impor berubah
     * menjadi belasan ribu penghapusan cache — pada penyimpanan cache
     * database, belasan ribu kueri DELETE.
     *
     * Aman karena invariannya dijaga: penanda hanya bernilai true selama
     * cache memang kosong. Begitu sebuah nilai dihitung dan disimpan ulang,
     * hafalkan() mengembalikannya ke false.
     */
    private static bool $sudahDilupakan = false;

    /**
     * Instance aplikasi pemilik penanda di atas — pola yang sama dengan
     * TeksTampilan::$memoUntukAplikasi.
     *
     * Di PHP-FPM biasa satu proses melayani satu permintaan, sehingga
     * properti statis lenyap dengan sendirinya. Tidak demikian pada proses
     * yang berumur panjang (Octane, pekerja antrean) maupun di dalam satu
     * proses PHPUnit: penanda dari permintaan sebelumnya akan bertahan dan
     * membuat pengosongan cache berikutnya dilewati, padahal cache sudah
     * terisi lagi oleh proses lain.
     */
    private static ?object $penandaUntukAplikasi = null;

    public function __construct(
        private readonly StatistikPenduduk $statistik = new StatistikPenduduk(),
        private readonly RingkasanKk $kk = new RingkasanKk(),
    ) {}

    /**
     * @return array{jumlah_penduduk: int, jumlah_kk: int|null, tahun_kk: int|null, laki: int, perempuan: int, jiwa_per_kk: float|null, jumlah_dusun: int}
     */
    public function ringkasan(): array
    {
        return $this->hafalkan('ringkasan', function (): array {
            $gender = collect($this->statistik->rasioGender())->keyBy('kunci');

            return [
                'jumlah_penduduk' => $this->statistik->totalWargaAktif(),
                // Bila belum ada rekap sama sekali, kartunya disembunyikan
                // seluruhnya alih-alih menampilkan nol yang menyesatkan.
                'jumlah_kk' => $this->kk->adaData() ? $this->kk->total() : null,
                'tahun_kk' => $this->kk->tahunAcuan(),
                'laki' => (int) ($gender['L']['jumlah'] ?? 0),
                'perempuan' => (int) ($gender['P']['jumlah'] ?? 0),
                'jiwa_per_kk' => $this->kk->rataJiwaPerKk(),
                'jumlah_dusun' => $this->jumlahDusun(),
            ];
        });
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function sebaranUmur(): array
    {
        return $this->hafalkan(
            'sebaran_umur',
            fn () => $this->lindungi($this->statistik->sebaranUmur()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function rasioGender(): array
    {
        // Gender hanya dua kelompok dan selalu besar; tidak perlu digabung,
        // tetapi ambang tetap diterapkan demi konsistensi aturan.
        //
        // Sensor pelengkap justru dimatikan di sini. ringkasan() mencetak
        // 'laki' dan 'perempuan' apa adanya, tanpa ambang, di kartu paling
        // atas halaman yang sama — menutup salah satu baris di tabel ini
        // hanya membuang informasi yang toh sudah terbit dua paragraf di
        // atasnya, tanpa satu pun angka yang jadi lebih terlindungi.
        return $this->hafalkan(
            'rasio_gender',
            fn () => $this->lindungi($this->statistik->rasioGender(), sensorPelengkap: false),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function pendidikan(): array
    {
        return $this->hafalkan(
            'pendidikan',
            fn () => $this->lindungi($this->statistik->pendidikan()),
        );
    }

    /**
     * @return array<int, array{label: string, laki: int|null, laki_tampilan: string, perempuan: int|null, perempuan_tampilan: string}>
     */
    public function piramidaUmurGender(): array
    {
        return $this->hafalkan(
            'piramida',
            fn () => $this->lindungiPiramida($this->statistik->piramidaUmurGender()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function agama(): array
    {
        return $this->hafalkan(
            'agama',
            fn () => $this->lindungi($this->statistik->agama()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function statusPerkawinan(): array
    {
        return $this->hafalkan(
            'status_perkawinan',
            fn () => $this->lindungi($this->statistik->statusPerkawinan()),
        );
    }

    /**
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    public function sebaranPekerjaan(): array
    {
        return $this->hafalkan(
            'pekerjaan',
            fn () => $this->lindungi($this->statistik->sebaranPekerjaan()),
        );
    }

    /**
     * Sebaran per dusun. Tidak pernah sampai RW atau RT (PRD 2.6).
     *
     * @return array<int, array{label: string, jumlah_warga: int|null, tampilan: string, jumlah_kk: int, persen: float}>
     */
    public function sebaranDusun(): array
    {
        return $this->hafalkan('sebaran_dusun', fn () => $this->hitungSebaranDusun());
    }

    /**
     * @return array<int, array{label: string, jumlah_warga: int|null, tampilan: string, jumlah_kk: int, persen: float}>
     */
    private function hitungSebaranDusun(): array
    {
        $baris = collect($this->statistik->sebaranWilayah('dusun', 'desc'));
        $total = max(1, (int) $baris->sum('jumlah_warga'));

        $besar = $baris->filter(fn (array $b) => $b['jumlah_warga'] >= self::AMBANG);
        $kecil = $baris->reject(fn (array $b) => $b['jumlah_warga'] >= self::AMBANG);

        $hasil = $besar->map(fn (array $b) => [
            'label' => $b['label'],
            'jumlah_warga' => $b['jumlah_warga'],
            'tampilan' => number_format($b['jumlah_warga'], 0, ',', '.'),
            'jumlah_kk' => $b['jumlah_kk'],
            'persen' => round($b['jumlah_warga'] / $total * 100, 1),
        ])->values();

        // Dusun bertetangga yang sama-sama kecil digabung, bukan dibuang —
        // membuang akan membuat totalnya tidak cocok dengan jumlah penduduk.
        if ($kecil->isNotEmpty()) {
            $jumlahKecil = (int) $kecil->sum('jumlah_warga');

            $hasil->push([
                'label' => 'Lainnya',
                'jumlah_warga' => $jumlahKecil >= self::AMBANG ? $jumlahKecil : null,
                'tampilan' => $jumlahKecil >= self::AMBANG
                    ? number_format($jumlahKecil, 0, ',', '.')
                    : '<'.self::AMBANG,
                'jumlah_kk' => (int) $kecil->sum('jumlah_kk'),
                'persen' => round($jumlahKecil / $total * 100, 1),
            ]);
        }

        return $hasil->all();
    }

    public function jumlahDusun(): int
    {
        return $this->hafalkan(
            'jumlah_dusun',
            fn () => Wilayah::query()->distinct()->count('dusun'),
        );
    }

    public function tanggalPembaruan(): ?string
    {
        return $this->hafalkan('tanggal_pembaruan', function (): ?string {
            $terakhir = Penduduk::withTrashed()->max('updated_at');

            return $terakhir ? \Illuminate\Support\Carbon::parse($terakhir)->translatedFormat('d F Y') : null;
        });
    }

    // ------------------------------------------------------------------
    // Cache
    // ------------------------------------------------------------------

    /**
     * Menyimpan hasil perhitungan sampai tengah malam.
     *
     * Bukan rememberForever seperti PengaturanDesa dan TeksTampilan: sebaran
     * umur dihitung terhadap tanggal hari ini, sehingga hasil kemarin sudah
     * keliru begitu hari berganti — meski tidak ada satu baris pun yang
     * disunting.
     *
     * @template T
     *
     * @param  callable(): T  $hitung
     * @return T
     */
    private function hafalkan(string $nama, callable $hitung): mixed
    {
        self::segarkanPenanda();

        $nilai = Cache::remember(
            self::PREFIX_CACHE.$nama,
            now()->endOfDay(),
            $hitung,
        );

        // Cache kembali terisi, jadi penghapusan berikutnya harus benar-benar
        // dijalankan lagi. Lihat catatan pada $sudahDilupakan.
        self::$sudahDilupakan = false;

        return $nilai;
    }

    /** Banyaknya kunci yang dihapus sekali pengosongan cache. */
    public static function jumlahKunciCache(): int
    {
        return count(self::KUNCI_CACHE);
    }

    /**
     * Dipanggil oleh model yang datanya membentuk statistik ini —
     * lihat App\Models\Concerns\MenyegarkanStatistikPublik.
     */
    public static function lupakanCache(): void
    {
        self::segarkanPenanda();

        if (self::$sudahDilupakan) {
            return;
        }

        foreach (self::KUNCI_CACHE as $nama) {
            Cache::forget(self::PREFIX_CACHE.$nama);
        }

        self::$sudahDilupakan = true;
    }

    /**
     * Membuang penanda milik aplikasi sebelumnya. Tanpa ini, penanda dari
     * permintaan lama ikut terbawa dan pengosongan cache berikutnya dilewati
     * meski cachenya sudah terisi kembali.
     */
    private static function segarkanPenanda(): void
    {
        if (self::$penandaUntukAplikasi !== app()) {
            self::$sudahDilupakan = false;
            self::$penandaUntukAplikasi = app();
        }
    }

    /**
     * Menerapkan ambang kelompok kecil.
     *
     * Kelompok di bawah ambang tidak dihapus dari daftar — kehadirannya bukan
     * rahasia, hanya jumlah pastinya. Yang dikeluarkan hanya angkanya.
     *
     * Persen ikut hilang bersama jumlahnya. Menutup kolom "Jumlah" saja
     * tidak menutup apa pun selama kolom "Persen" di sebelahnya masih
     * terisi: penduduk desa jarang melewati beberapa ribu jiwa, dan pada
     * ukuran sebesar itu satu angka di belakang koma sudah menentukan angka
     * bulatnya. "0,2% dari 882" hanya bisa berarti dua orang.
     *
     * @param  array<int, array{label: string, jumlah: int, persen: float}>  $baris
     * @param  bool  $sensorPelengkap  lihat samarkanPelengkap(); dimatikan
     *                                 untuk tabel yang angkanya toh sudah
     *                                 terbit utuh di tempat lain
     * @return array<int, array{label: string, jumlah: int|null, tampilan: string, persen: float|null}>
     */
    private function lindungi(array $baris, bool $sensorPelengkap = true): array
    {
        $baris = array_values($baris);

        $hasil = array_map(fn (array $b) => [
            'label' => $b['label'],
            'jumlah' => $b['jumlah'] >= self::AMBANG ? $b['jumlah'] : null,
            'tampilan' => $this->tampilkan($b['jumlah']),
            // Satu aturan yang bisa diperiksa ulang sekali lihat: persen ada
            // persis ketika jumlahnya ada.
            'persen' => $b['jumlah'] >= self::AMBANG ? $b['persen'] : null,
        ], $baris);

        if (! $sensorPelengkap) {
            return $hasil;
        }

        return $this->samarkanPelengkap($hasil, array_column($baris, 'jumlah'), 'jumlah', 'tampilan', 'persen');
    }

    /**
     * Sensor ambang untuk piramida — tiap baris punya DUA kolom angka
     * (laki & perempuan) yang disensor independen, karena satu sisi bisa
     * di bawah ambang sementara sisi lain tidak.
     *
     * @param  array<int, array{label: string, laki: int, perempuan: int}>  $baris
     * @return array<int, array{label: string, laki: int|null, laki_tampilan: string, perempuan: int|null, perempuan_tampilan: string}>
     */
    private function lindungiPiramida(array $baris): array
    {
        $baris = array_values($baris);

        $hasil = array_map(fn (array $b) => [
            'label' => $b['label'],
            'laki' => $b['laki'] >= self::AMBANG ? $b['laki'] : null,
            'laki_tampilan' => $this->tampilkan($b['laki']),
            'perempuan' => $b['perempuan'] >= self::AMBANG ? $b['perempuan'] : null,
            'perempuan_tampilan' => $this->tampilkan($b['perempuan']),
        ], $baris);

        // Tiap kolom gender adalah tabel tertutupnya sendiri: jumlah seluruh
        // sel "laki" mendekati ringkasan['laki'], begitu pula perempuan.
        // Karena itu keduanya diperiksa terpisah — penyamaran di satu kolom
        // tidak boleh mengurangi informasi di kolom sebelahnya.
        $hasil = $this->samarkanPelengkap($hasil, array_column($baris, 'laki'), 'laki', 'laki_tampilan');

        return $this->samarkanPelengkap($hasil, array_column($baris, 'perempuan'), 'perempuan', 'perempuan_tampilan');
    }

    /** Bentuk tampilan sebuah angka setelah ambang diterapkan. */
    private function tampilkan(int $jumlah): string
    {
        if ($jumlah >= self::AMBANG) {
            return number_format($jumlah, 0, ',', '.');
        }

        // Nol bukan rahasia — tidak ada seorang pun yang bisa dikenali dari
        // kelompok kosong — jadi ia tetap tampil sebagai angka.
        return $jumlah === 0 ? '0' : '<'.self::AMBANG;
    }

    /**
     * Sensor pelengkap — menutup celah pengurangan.
     *
     * Tabel Agama, Status Perkawinan, dan Pekerjaan adalah pembagian habis
     * atas seluruh warga: barisnya selalu berjumlah persis sama dengan
     * "Jumlah Penduduk" yang tercetak apa adanya di halaman yang sama. Bila
     * hanya SATU baris yang tersensor, angka pastinya kembali terbaca lewat
     * pengurangan sederhana — total dikurangi semua baris yang tampil.
     * "Islam 850, Konghucu <5, Belum diisi 30, total 882" berarti Konghucu
     * tepat dua orang, dan perlindungan ambangnya jadi sia-sia.
     *
     * Karena itu satu baris lain ikut ditutup sehingga tersisa dua nilai tak
     * diketahui untuk satu persamaan — tidak lagi terpecahkan. Yang dipilih
     * adalah baris tampil terkecil: keputusannya bisa diulang oleh siapa pun
     * yang membaca kode ini, dan informasi yang hilang paling sedikit.
     *
     * Nol tidak pernah dihitung sebagai baris tersensor maupun dipilih
     * sebagai pelengkap. Bila tidak ada baris tampil yang berisi, tidak ada
     * yang perlu ditutup: tanpa sisa pengurangan yang bersih, persamaannya
     * memang tidak bisa dipecahkan.
     *
     * Satu perkecualian pada "baris tampil terkecil": baris yang nilainya
     * PERSIS di ambang tidak menyamarkan apa pun. Pembaca sudah tahu baris
     * tersamarkan bernilai minimal AMBANG, jadi ketika sisanya hanya
     * AMBANG + 1, satu-satunya pembagian yang mungkin adalah "AMBANG dan
     * satu orang" — kedua angka kembali terbaca. Karena itu baris berikutnya
     * ikut ditutup sampai ada satu yang benar-benar di atas ambang. Syarat
     * berhentinya hanya melihat nilai baris tampil, yang memang boleh
     * terbit; ia tidak pernah bergantung pada angka yang sedang disensor,
     * sehingga banyaknya baris tersamarkan tidak ikut membocorkannya.
     *
     * Bila SELURUH baris tampil bernilai persis di ambang, tidak ada lagi
     * yang bisa ditutup — menutup semuanya pun tidak menambah kemungkinan.
     * Itu batas bawaan tabel sekecil itu, bukan sesuatu yang bisa
     * diperbaiki di sini.
     *
     * @param  array<int, array<string, mixed>>  $hasil  baris yang sudah lewat sensor utama
     * @param  array<int, int>  $asli  angka sebenarnya, indeksnya sejajar dengan $hasil
     * @param  string|null  $kunciPersen  kolom persen yang ikut ditutup, bila tabelnya punya
     * @return array<int, array<string, mixed>>
     */
    private function samarkanPelengkap(
        array $hasil,
        array $asli,
        string $kunciJumlah,
        string $kunciTampilan,
        ?string $kunciPersen = null,
    ): array {
        $tersensor = array_filter($asli, fn (int $jumlah) => $jumlah > 0 && $jumlah < self::AMBANG);

        if (count($tersensor) !== 1) {
            return $hasil;
        }

        $kandidat = [];

        foreach ($hasil as $i => $baris) {
            if ($baris[$kunciJumlah] !== null && $baris[$kunciJumlah] > 0) {
                $kandidat[$i] = $baris[$kunciJumlah];
            }
        }

        // Dari yang terkecil: informasi yang hilang paling sedikit, dan
        // urutannya bisa diulang oleh siapa pun yang membaca kode ini.
        asort($kandidat);

        foreach ($kandidat as $i => $nilai) {
            // Bukan "<5": angkanya bisa saja besar, dan keterangan yang
            // keliru lebih buruk daripada keterangan yang jujur soal
            // ketiadaannya.
            $hasil[$i][$kunciJumlah] = null;
            $hasil[$i][$kunciTampilan] = self::LABEL_DISAMARKAN;

            if ($kunciPersen !== null) {
                $hasil[$i][$kunciPersen] = null;
            }

            if ($nilai > self::AMBANG) {
                break;
            }
        }

        return $hasil;
    }

    /**
     * Data siap kirim untuk grafik. Hanya memuat nilai yang sudah disaring.
     *
     * @return Collection<string, mixed>
     */
    public function untukGrafik(string $jenis): Collection
    {
        return collect(match ($jenis) {
            'umur' => $this->sebaranUmur(),
            'gender' => $this->rasioGender(),
            'pendidikan' => $this->pendidikan(),
            'piramida' => $this->piramidaUmurGender(),
            default => [],
        });
    }
}
