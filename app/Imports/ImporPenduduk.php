<?php

namespace App\Imports;

use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use App\Exceptions\ImporDitolak;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Fitur 04 — impor data warga dari Excel/CSV.
 *
 * Seluruh proses berjalan di dalam satu transaksi. Impor yang gagal di tengah
 * jalan tidak boleh meninggalkan separuh data: admin akan mengulang impor dan
 * separuh yang terlanjur masuk menjadi duplikat yang sulit ditelusuri.
 */
class ImporPenduduk
{
    private HasilImporPenduduk $hasil;

    /** @var array<string, int> peta "dusun|rw|rt" => wilayah_id */
    private array $petaWilayah = [];

    /** @var array<string, int> peta nama pekerjaan (huruf kecil) => id */
    private array $petaPekerjaan = [];

    /** @var array<string, true> kunci warga yang sudah ada, untuk deteksi ganda */
    private array $wargaAda = [];

    public function __construct(private readonly bool $lewatiDuplikat = true)
    {
        $this->hasil = new HasilImporPenduduk();
    }

    /**
     * @param  array<int, array<int, string|null>>  $baris  baris mentah, termasuk baris judul
     *
     * @throws ImporDitolak
     */
    public function jalankan(array $baris): HasilImporPenduduk
    {
        $judul = $this->bacaJudul($baris);
        $isi = array_slice($baris, 1);

        $this->hasil->totalBaris = count($isi);

        $this->pastikanDalamBatas($this->hasil->totalBaris);

        $this->siapkanReferensi();

        DB::transaction(function () use ($isi, $judul): void {
            foreach ($isi as $index => $kolom) {
                // +2: baris pertama adalah judul, dan penomoran dimulai dari 1.
                $this->prosesBaris($index + 2, $this->petakan($judul, $kolom));
            }
        });

        return $this->hasil;
    }

    /**
     * Memeriksa baris judul dan menolak berkas yang memuat kolom terlarang.
     *
     * @param  array<int, array<int, string|null>>  $baris
     * @return array<int, string> indeks kolom => nama kolom internal
     *
     * @throws ImporDitolak
     */
    private function bacaJudul(array $baris): array
    {
        if ($baris === []) {
            throw ImporDitolak::berkasKosong();
        }

        $judulMentah = $baris[0];
        $peta = [];
        $ditemukan = [];

        foreach ($judulMentah as $index => $teks) {
            $normal = KolomImporPenduduk::normalkan($teks);

            if ($normal === '') {
                continue;
            }

            // Pemeriksaan ini dilakukan sebelum satu baris pun diproses:
            // berkas sumber yang memuat NIK atau nomor KK tidak boleh
            // diam-diam diabaikan, admin perlu tahu berkasnya bermasalah.
            if (KolomImporPenduduk::terlarang($normal)) {
                throw ImporDitolak::kolomTerlarang($teks ?? $normal);
            }

            if (isset(KolomImporPenduduk::PETA[$normal])) {
                $peta[$index] = KolomImporPenduduk::PETA[$normal];
                $ditemukan[] = $normal;
            }
        }

        $kurang = array_diff(KolomImporPenduduk::WAJIB, $ditemukan);

        if ($kurang !== []) {
            throw ImporDitolak::kolomWajibHilang(array_values($kurang));
        }

        return $peta;
    }

    /**
     * @param  array<int, string>  $judul
     * @param  array<int, string|null>  $kolom
     * @return array<string, string|null>
     */
    private function petakan(array $judul, array $kolom): array
    {
        $data = [];

        foreach ($judul as $index => $nama) {
            $nilai = $kolom[$index] ?? null;
            $data[$nama] = is_string($nilai) ? trim($nilai) : $nilai;
        }

        return $data;
    }

    private function pastikanDalamBatas(int $jumlah): void
    {
        $maks = config('impor.maks_baris');

        if ($jumlah > $maks) {
            throw ImporDitolak::terlaluBanyakBaris($jumlah, $maks);
        }

        if ($jumlah === 0) {
            throw ImporDitolak::berkasKosong();
        }
    }

    private function siapkanReferensi(): void
    {
        // Dimuat sekali di awal, bukan query per baris — 2.000 baris berarti
        // 2.000 query tambahan, dan shared hosting akan timeout.
        $this->petaWilayah = Wilayah::all()
            ->mapWithKeys(fn (Wilayah $w) => [$this->kunciWilayah($w->dusun, $w->rw, $w->rt) => $w->id])
            ->all();

        $this->petaPekerjaan = RefPekerjaan::all()
            ->mapWithKeys(fn (RefPekerjaan $p) => [mb_strtolower($p->nama) => $p->id])
            ->all();

        $this->wargaAda = Penduduk::withTrashed()
            ->get(['nama_lengkap', 'tanggal_lahir', 'wilayah_id'])
            ->mapWithKeys(fn (Penduduk $p) => [
                $this->kunciWarga($p->nama_lengkap, $p->tanggal_lahir?->toDateString(), $p->wilayah_id) => true,
            ])
            ->all();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function prosesBaris(int $nomorBaris, array $data): void
    {
        $nama = trim((string) ($data['nama_lengkap'] ?? ''));

        if ($nama === '') {
            $this->hasil->catatGagal($nomorBaris, '(kosong)', 'Nama lengkap kosong.');

            return;
        }

        $jk = $this->bacaJenisKelamin($data['jenis_kelamin'] ?? null);

        if ($jk === null) {
            $this->hasil->catatGagal($nomorBaris, $nama, 'Jenis kelamin harus L atau P.');

            return;
        }

        $kunciWilayah = $this->kunciWilayah(
            (string) ($data['dusun'] ?? ''),
            Wilayah::normalkanNomor($data['rw'] ?? null),
            Wilayah::normalkanNomor($data['rt'] ?? null),
        );

        if (! isset($this->petaWilayah[$kunciWilayah])) {
            // Wilayah baru tidak dibuat otomatis: berkas dengan salah ketik
            // dusun akan diam-diam menambah wilayah palsu dan memecah
            // statistik sebaran.
            $this->hasil->catatGagal($nomorBaris, $nama, sprintf(
                'Wilayah "%s RT %s/RW %s" belum terdaftar.',
                $data['dusun'] ?? '-',
                $data['rt'] ?? '-',
                $data['rw'] ?? '-',
            ));

            return;
        }

        $wilayahId = $this->petaWilayah[$kunciWilayah];

        $tanggalLahir = $this->bacaTanggal($data['tanggal_lahir'] ?? null);

        if (filled($data['tanggal_lahir'] ?? null) && $tanggalLahir === null) {
            $this->hasil->catatGagal($nomorBaris, $nama, 'Format tanggal lahir tidak dikenali.');

            return;
        }

        if ($tanggalLahir !== null && $tanggalLahir->isFuture()) {
            $this->hasil->catatGagal($nomorBaris, $nama, 'Tanggal lahir melewati hari ini.');

            return;
        }

        $pekerjaanId = null;

        if (filled($data['pekerjaan'] ?? null)) {
            $pekerjaanId = $this->petaPekerjaan[mb_strtolower(trim($data['pekerjaan']))] ?? null;

            if ($pekerjaanId === null) {
                $this->hasil->catatGagal($nomorBaris, $nama, sprintf(
                    'Pekerjaan "%s" belum terdaftar pada data referensi.',
                    $data['pekerjaan'],
                ));

                return;
            }
        }

        // Tanpa NIK atau nomor KK, tidak ada kunci unik yang benar-benar
        // pasti. Kombinasi ini yang paling mendekati, dan admin diberi tahu
        // baris mana yang dilewati agar bisa memeriksanya sendiri.
        $kunciWarga = $this->kunciWarga($nama, $tanggalLahir?->toDateString(), $wilayahId);

        if (isset($this->wargaAda[$kunciWarga])) {
            if ($this->lewatiDuplikat) {
                $this->hasil->catatDuplikat($nomorBaris, $nama, 'Sudah ada warga dengan nama, tanggal lahir, dan wilayah yang sama.');

                return;
            }
        }

        try {
            Penduduk::create([
                'nama_lengkap' => $nama,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jk,
                'wilayah_id' => $wilayahId,
                'pekerjaan_id' => $pekerjaanId,
                'pendidikan' => $this->bacaPendidikan($data['pendidikan'] ?? null),
                'jenis_masuk' => JenisMasuk::PendataanAwal,
                'tanggal_masuk' => now()->toDateString(),
            ]);
        } catch (Throwable $e) {
            $this->hasil->catatGagal($nomorBaris, $nama, 'Gagal disimpan: '.$e->getMessage());

            return;
        }

        $this->wargaAda[$kunciWarga] = true;
        $this->hasil->berhasil++;
    }

    private function kunciWilayah(string $dusun, string $rw, string $rt): string
    {
        return mb_strtolower(trim($dusun)).'|'.$rw.'|'.$rt;
    }

    private function kunciWarga(string $nama, ?string $tanggalLahir, int $wilayahId): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($nama)))
            .'|'.($tanggalLahir ?? '-')
            .'|'.$wilayahId;
    }

    private function bacaJenisKelamin(?string $nilai): ?JenisKelamin
    {
        $nilai = mb_strtoupper(trim((string) $nilai));

        return match ($nilai) {
            'L', 'LK', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA', 'M' => JenisKelamin::LakiLaki,
            'P', 'PR', 'PEREMPUAN', 'WANITA', 'F' => JenisKelamin::Perempuan,
            default => null,
        };
    }

    private function bacaPendidikan(?string $nilai): ?Pendidikan
    {
        if (blank($nilai)) {
            return null;
        }

        $normal = mb_strtolower(trim($nilai));

        // Cocokkan dengan nilai enum maupun labelnya, supaya admin boleh
        // menulis "SMA" atau "SMA / SMK / Sederajat".
        foreach (Pendidikan::cases() as $pendidikan) {
            if ($normal === $pendidikan->value || $normal === mb_strtolower($pendidikan->label())) {
                return $pendidikan;
            }
        }

        return match ($normal) {
            'tidak sekolah', 'belum sekolah', 'belum tamat sd' => Pendidikan::BelumSekolah,
            'sd', 'sd/sederajat', 'sekolah dasar' => Pendidikan::SD,
            'smp', 'smp/sederajat', 'mts' => Pendidikan::SMP,
            'sma', 'smk', 'sma/smk', 'slta', 'ma' => Pendidikan::SMA,
            'd1', 'd2', 'd3', 'diploma' => Pendidikan::D1D3,
            's1', 'sarjana', 'd4' => Pendidikan::S1,
            's2', 'magister' => Pendidikan::S2,
            's3', 'doktor' => Pendidikan::S3,
            default => null,
        };
    }

    private function bacaTanggal(mixed $nilai): ?Carbon
    {
        if (blank($nilai)) {
            return null;
        }

        // Excel menyimpan tanggal sebagai angka hari sejak 1900-01-01.
        if (is_numeric($nilai)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $nilai),
                );
            } catch (Throwable) {
                return null;
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'Y/m/d'] as $format) {
            try {
                $tanggal = Carbon::createFromFormat($format, trim((string) $nilai));

                if ($tanggal !== false) {
                    return $tanggal->startOfDay();
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }
}
