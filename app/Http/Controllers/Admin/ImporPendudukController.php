<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ImporDitolak;
use App\Http\Controllers\Controller;
use App\Imports\AnalisisImpor;
use App\Imports\BarisMentah;
use App\Imports\HasilAnalisisImpor;
use App\Imports\ImporPenduduk;
use App\Imports\KolomImporPenduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fitur 04 — impor data warga.
 *
 * Alurnya tiga langkah dan tidak pernah menyimpan apa pun tanpa persetujuan:
 *   1. unggah berkas
 *   2. pratinjau — sistem melaporkan apa yang akan ditambahkan, apa yang
 *      terdeteksi ganda beserta data pembandingnya, dan baris mana yang
 *      bermasalah
 *   3. admin menekan Lanjut (dengan memilih sendiri data ganda mana yang
 *      tetap diimpor) atau Batal
 */
class ImporPendudukController extends Controller
{
    /** Folder penampung berkas antara langkah pratinjau dan konfirmasi. */
    private const FOLDER_SEMENTARA = 'impor-sementara';

    private const KUNCI_SESI = 'impor_penduduk_token';

    public function tampilkan(): View
    {
        return view('admin.penduduk.impor', [
            'wilayahList' => Wilayah::urut()->get(),
            'pekerjaanList' => RefPekerjaan::orderBy('nama')->get(),
        ]);
    }

    /**
     * Berkas contoh berisi judul kolom yang benar plus dua baris isian
     * sebagai panduan. Tanpa ini admin akan menebak-nebak nama kolom.
     */
    public function contoh(): StreamedResponse
    {
        $judul = KolomImporPenduduk::judul();
        $wilayah = Wilayah::urut()->first();
        $pekerjaan = RefPekerjaan::orderBy('nama')->first();

        $contoh = [
            ['Siti Nurhayati', '1985-04-17', 'P', $wilayah?->dusun ?? 'Dusun Krajan', $wilayah?->rw ?? '001', $wilayah?->rt ?? '001', $pekerjaan?->nama ?? 'Petani', 'SMA'],
            ['Budi Santoso', '17/04/1985', 'L', $wilayah?->dusun ?? 'Dusun Krajan', $wilayah?->rw ?? '001', $wilayah?->rt ?? '001', '', 'S1'],
        ];

        $response = response()->streamDownload(function () use ($judul, $contoh): void {
            $keluaran = fopen('php://output', 'w');

            // BOM UTF-8 agar huruf beraksen tampil benar saat dibuka di Excel.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, $judul);
            foreach ($contoh as $baris) {
                fputcsv($keluaran, $baris);
            }

            fclose($keluaran);
        }, 'contoh-impor-penduduk.csv');

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

        return $response;
    }

    /**
     * Langkah 2 — membaca berkas dan menampilkan apa yang akan terjadi.
     * Tidak ada satu baris pun yang disimpan di sini.
     */
    public function pratinjau(Request $request): RedirectResponse|View
    {
        $request->validate([
            'berkas' => [
                'required', 'file',
                'mimes:'.implode(',', config('impor.ekstensi')),
                'max:'.config('impor.maks_kb'),
            ],
        ], [
            'berkas.required' => 'Pilih berkas yang akan diimpor.',
            'berkas.mimes' => 'Berkas harus berformat .xlsx, .xls, atau .csv.',
            'berkas.max' => 'Ukuran berkas melebihi batas '
                .round(config('impor.maks_kb') / 1024).' MB. Pecah berkas menjadi beberapa bagian.',
        ], ['berkas' => 'berkas impor']);

        $this->bersihkanBerkasKedaluwarsa();

        // Berkas disimpan sementara agar langkah konfirmasi tidak menuntut
        // admin mengunggah ulang berkas yang sama.
        $token = Str::random(40);
        $nama = $token.'.'.$request->file('berkas')->getClientOriginalExtension();
        $request->file('berkas')->storeAs(self::FOLDER_SEMENTARA, $nama);

        try {
            $analisis = $this->analisis($nama);
        } catch (ImporDitolak $e) {
            Storage::delete(self::FOLDER_SEMENTARA.'/'.$nama);

            return back()->withErrors(['berkas' => $e->getMessage()]);
        }

        $request->session()->put(self::KUNCI_SESI, $nama);

        return view('admin.penduduk.impor-pratinjau', [
            'analisis' => $analisis,
            'namaBerkas' => $request->file('berkas')->getClientOriginalName(),
            'token' => $token,
        ]);
    }

    /**
     * Langkah 3 — menyimpan, mengikuti keputusan admin atas data ganda.
     */
    public function proses(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'ganda' => ['array'],
            'ganda.*' => ['integer'],
        ]);

        $berkas = $this->berkasSesi($request, $data['token']);

        if ($berkas === null) {
            return redirect()
                ->route('admin.penduduk.impor')
                ->withErrors(['berkas' => 'Sesi impor sudah berakhir. Silakan unggah berkasnya kembali.']);
        }

        try {
            // Dibaca ulang, bukan memakai hasil pratinjau: data warga bisa
            // berubah di antara kedua langkah bila ada admin lain yang
            // bekerja bersamaan, dan yang tersimpan harus mengikuti keadaan
            // terbaru.
            $analisis = $this->analisis($berkas);
        } catch (ImporDitolak $e) {
            $this->bersihkanSesi($request, $berkas);

            return redirect()->route('admin.penduduk.impor')->withErrors(['berkas' => $e->getMessage()]);
        }

        $hasil = (new ImporPenduduk())->simpan($analisis, $data['ganda'] ?? []);

        $this->bersihkanSesi($request, $berkas);

        return redirect()
            ->route('admin.penduduk.index')
            ->with('status', $hasil->ringkasan());
    }

    public function batal(Request $request): RedirectResponse
    {
        if ($berkas = $request->session()->get(self::KUNCI_SESI)) {
            $this->bersihkanSesi($request, $berkas);
        }

        return redirect()
            ->route('admin.penduduk.impor')
            ->with('status', 'Impor dibatalkan. Tidak ada data yang tersimpan.');
    }

    // ------------------------------------------------------------------

    private function analisis(string $namaBerkas): HasilAnalisisImpor
    {
        $baris = Excel::toArray(
            new BarisMentah(),
            Storage::path(self::FOLDER_SEMENTARA.'/'.$namaBerkas),
        )[0] ?? [];

        return (new AnalisisImpor())->jalankan($baris);
    }

    /**
     * Memastikan token yang dikirim formulir memang milik sesi ini, supaya
     * berkas impor admin lain tidak bisa diproses lewat token tebakan.
     */
    private function berkasSesi(Request $request, string $token): ?string
    {
        $berkas = $request->session()->get(self::KUNCI_SESI);

        if ($berkas === null || ! str_starts_with($berkas, $token.'.')) {
            return null;
        }

        return Storage::exists(self::FOLDER_SEMENTARA.'/'.$berkas) ? $berkas : null;
    }

    private function bersihkanSesi(Request $request, string $berkas): void
    {
        Storage::delete(self::FOLDER_SEMENTARA.'/'.$berkas);
        $request->session()->forget(self::KUNCI_SESI);
    }

    /**
     * Membuang berkas pratinjau yang ditinggalkan admin.
     *
     * Dijalankan saat ada unggahan baru, bukan lewat cron: shared hosting
     * tidak menyediakan penjadwal yang andal (PRD 8).
     */
    private function bersihkanBerkasKedaluwarsa(): void
    {
        $batas = now()->subDay()->getTimestamp();

        foreach (Storage::files(self::FOLDER_SEMENTARA) as $berkas) {
            if (Storage::lastModified($berkas) < $batas) {
                Storage::delete($berkas);
            }
        }
    }
}
