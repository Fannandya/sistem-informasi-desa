<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EksporPenduduk;
use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use App\Models\PengaturanDesa;
use App\Queries\PencarianPenduduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as FormatExcel;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EksporPendudukController extends Controller
{
    /**
     * Di atas ambang ini, berkas .xlsx menghabiskan memori dan waktu eksekusi
     * shared hosting (PRD 8). CSV ditulis mengalir sehingga tetap sanggup.
     */
    private const AMBANG_CSV = 10000;

    public function __invoke(Request $request): BinaryFileResponse|RedirectResponse
    {
        $format = $request->input('format') === 'csv' ? 'csv' : 'xlsx';
        $arsip = $request->boolean('arsip');

        $pencarian = PencarianPenduduk::dariRequest($request);
        $ekspor = new EksporPenduduk($pencarian, $arsip);

        $jumlah = $ekspor->query()->toBase()->getCountForPagination();

        if ($jumlah === 0) {
            return back()->with('galat', $arsip
                ? 'Tidak ada data arsip yang cocok dengan saringan, jadi tidak ada yang diunduh.'
                : 'Tidak ada data warga yang cocok dengan saringan, jadi tidak ada yang diunduh.');
        }

        if ($format === 'xlsx' && $jumlah > self::AMBANG_CSV) {
            return back()->with('galat', sprintf(
                'Data yang dipilih berjumlah %s baris, terlalu besar untuk format .xlsx pada server ini. '
                .'Pilih format .csv — isinya sama persis dan tetap bisa dibuka di Excel.',
                number_format($jumlah, 0, ',', '.'),
            ));
        }

        return Excel::download(
            $ekspor,
            $this->namaBerkas($pencarian, $arsip, $format),
            $format === 'csv' ? FormatExcel::CSV : FormatExcel::XLSX,
        );
    }

    /**
     * Nama berkas mencerminkan saringan yang dipakai, sehingga beberapa hasil
     * ekspor tidak tertukar di folder unduhan admin.
     */
    private function namaBerkas(PencarianPenduduk $pencarian, bool $arsip, string $format): string
    {
        $bagian = [Str::slug(PengaturanDesa::ambil()->nama_desa ?: 'desa')];

        $bagian[] = $arsip ? 'arsip-warga' : 'data-warga';

        // Nama dusun sudah memuat kata "Dusun", jadi tidak perlu diawali nama
        // kunci lagi — hasilnya akan terbaca "dusun-dusun-krajan".
        $tanpaPrefiks = ['dusun', 'kelompok_umur', 'alasan_keluar'];

        foreach (['dusun', 'rw', 'rt', 'jenis_kelamin', 'kelompok_umur', 'alasan_keluar'] as $kunci) {
            if (blank($pencarian->nilai($kunci))) {
                continue;
            }

            $bagian[] = in_array($kunci, $tanpaPrefiks, true)
                ? Str::slug($pencarian->nilai($kunci))
                : Str::slug($kunci.'-'.$pencarian->nilai($kunci));
        }

        if (filled($pencarian->nilai('q'))) {
            $bagian[] = 'cari-'.Str::slug($pencarian->nilai('q'));
        }

        $bagian[] = now()->format('Y-m-d');

        return implode('-', $bagian).'.'.$format;
    }
}
