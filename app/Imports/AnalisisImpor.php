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
use Throwable;

/**
 * Fitur 04 — membaca berkas impor tanpa menyimpan apa pun.
 *
 * Kelas ini sengaja tidak pernah menulis ke database. Tugasnya hanya
 * melaporkan apa yang akan terjadi, supaya admin yang memutuskan — bukan
 * sistem yang diam-diam melewati baris lalu memberi tahu setelahnya.
 *
 * Dijalankan dua kali dalam satu alur impor: sekali untuk halaman pratinjau,
 * dan sekali lagi saat admin menekan Lanjut. Pembacaan ulang disengaja: data
 * warga bisa berubah di antara kedua langkah itu bila ada admin lain yang
 * bekerja bersamaan, dan yang tersimpan harus mengikuti keadaan terbaru.
 */
class AnalisisImpor
{
    /** @var array<string, int> "dusun|rw|rt" => wilayah_id */
    private array $petaWilayah = [];

    /** @var array<string, int> nama pekerjaan huruf kecil => id */
    private array $petaPekerjaan = [];

    /** @var array<string, Penduduk> kunci warga => warga yang sudah ada */
    private array $wargaAda = [];

    /** @var array<string, int> kunci warga => nomor baris di berkas ini */
    private array $terlihatDiBerkas = [];

    /**
     * @param  array<int, array<int, string|null>>  $baris  baris mentah, termasuk baris judul
     *
     * @throws ImporDitolak
     */
    public function jalankan(array $baris): HasilAnalisisImpor
    {
        $judul = $this->bacaJudul($baris);
        $isi = array_slice($baris, 1);

        $hasil = new HasilAnalisisImpor();
        $hasil->totalBaris = count($isi);

        $this->pastikanDalamBatas($hasil->totalBaris);
        $this->siapkanReferensi();

        foreach ($isi as $index => $kolom) {
            // +2: baris pertama adalah judul, dan penomoran berkas dimulai dari 1.
            $this->periksaBaris($hasil, $index + 2, $this->petakan($judul, $kolom));
        }

        return $hasil;
    }

    /**
     * @param  array<int, string>  $judul
     * @param  array<string, string|null>  $data
     */
    private function periksaBaris(HasilAnalisisImpor $hasil, int $nomorBaris, array $data): void
    {
        $nama = trim((string) ($data['nama_lengkap'] ?? ''));

        if ($nama === '') {
            $hasil->gagal[] = ['baris' => $nomorBaris, 'nama' => '(kosong)', 'alasan' => 'Nama lengkap kosong.'];

            return;
        }

        $jk = $this->bacaJenisKelamin($data['jenis_kelamin'] ?? null);

        if ($jk === null) {
            $hasil->gagal[] = ['baris' => $nomorBaris, 'nama' => $nama, 'alasan' => 'Jenis kelamin harus L atau P.'];

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
            $hasil->gagal[] = [
                'baris' => $nomorBaris,
                'nama' => $nama,
                'alasan' => sprintf(
                    'Wilayah "%s RT %s/RW %s" belum terdaftar.',
                    $data['dusun'] ?? '-',
                    $data['rt'] ?? '-',
                    $data['rw'] ?? '-',
                ),
            ];

            return;
        }

        $wilayahId = $this->petaWilayah[$kunciWilayah];
        $tanggalLahir = $this->bacaTanggal($data['tanggal_lahir'] ?? null);

        if (filled($data['tanggal_lahir'] ?? null) && $tanggalLahir === null) {
            $hasil->gagal[] = ['baris' => $nomorBaris, 'nama' => $nama, 'alasan' => 'Format tanggal lahir tidak dikenali.'];

            return;
        }

        if ($tanggalLahir !== null && $tanggalLahir->isFuture()) {
            $hasil->gagal[] = ['baris' => $nomorBaris, 'nama' => $nama, 'alasan' => 'Tanggal lahir melewati hari ini.'];

            return;
        }

        $pekerjaanId = null;
        $namaPekerjaan = null;

        if (filled($data['pekerjaan'] ?? null)) {
            $namaPekerjaan = trim($data['pekerjaan']);
            $pekerjaanId = $this->petaPekerjaan[mb_strtolower($namaPekerjaan)] ?? null;

            if ($pekerjaanId === null) {
                $hasil->gagal[] = [
                    'baris' => $nomorBaris,
                    'nama' => $nama,
                    'alasan' => "Pekerjaan \"{$namaPekerjaan}\" belum terdaftar pada data referensi.",
                ];

                return;
            }
        }

        $pendidikan = $this->bacaPendidikan($data['pendidikan'] ?? null);

        $baris = new BarisImpor(
            nomorBaris: $nomorBaris,
            data: [
                'nama_lengkap' => $nama,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jk,
                'wilayah_id' => $wilayahId,
                'pekerjaan_id' => $pekerjaanId,
                'pendidikan' => $pendidikan,
                'jenis_masuk' => JenisMasuk::PendataanAwal,
                'tanggal_masuk' => now()->toDateString(),
            ],
            nama: $nama,
            tanggalLahir: $tanggalLahir?->toDateString(),
            jenisKelamin: $jk->label(),
            wilayah: $this->labelWilayah($data),
            pekerjaan: $namaPekerjaan,
            pendidikan: $pendidikan?->label(),
        );

        // Tanpa NIK atau nomor KK, kombinasi ini yang paling mendekati kunci
        // unik. Ia bukan kepastian, jadi hasilnya dilaporkan ke admin untuk
        // diputuskan, bukan dipakai sistem untuk melewati baris sendiri.
        $kunciWarga = $this->kunciWarga($nama, $baris->tanggalLahir, $wilayahId);

        if (isset($this->terlihatDiBerkas[$kunciWarga])) {
            $baris->gandaDenganBaris = $this->terlihatDiBerkas[$kunciWarga];
            $hasil->ganda[] = $baris;

            return;
        }

        $this->terlihatDiBerkas[$kunciWarga] = $nomorBaris;

        if (isset($this->wargaAda[$kunciWarga])) {
            $baris->miripDengan = $this->wargaAda[$kunciWarga];
            $hasil->ganda[] = $baris;

            return;
        }

        $hasil->siap[] = $baris;
    }

    // ------------------------------------------------------------------
    // Pembacaan berkas
    // ------------------------------------------------------------------

    /**
     * @param  array<int, array<int, string|null>>  $baris
     * @return array<int, string>
     *
     * @throws ImporDitolak
     */
    private function bacaJudul(array $baris): array
    {
        if ($baris === []) {
            throw ImporDitolak::berkasKosong();
        }

        $peta = [];
        $ditemukan = [];

        foreach ($baris[0] as $index => $teks) {
            $normal = KolomImporPenduduk::normalkan($teks);

            if ($normal === '') {
                continue;
            }

            // Diperiksa sebelum satu baris pun dibaca: berkas sumber yang
            // memuat NIK atau nomor KK tidak boleh diam-diam diabaikan.
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

        // Warga terarsip ikut dimuat: mengimpor ulang orang yang sudah
        // diarsipkan adalah hal yang perlu diketahui admin.
        $this->wargaAda = Penduduk::withTrashed()
            ->with('wilayah')
            ->get()
            ->mapWithKeys(fn (Penduduk $p) => [
                $this->kunciWarga($p->nama_lengkap, $p->tanggal_lahir?->toDateString(), $p->wilayah_id) => $p,
            ])
            ->all();
    }

    // ------------------------------------------------------------------
    // Penerjemahan nilai
    // ------------------------------------------------------------------

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

    /** @param array<string, string|null> $data */
    private function labelWilayah(array $data): string
    {
        return sprintf(
            '%s — RT %s/RW %s',
            $data['dusun'] ?? '-',
            Wilayah::normalkanNomor($data['rt'] ?? null),
            Wilayah::normalkanNomor($data['rw'] ?? null),
        );
    }

    private function bacaJenisKelamin(?string $nilai): ?JenisKelamin
    {
        return match (mb_strtoupper(trim((string) $nilai))) {
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
