<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ImporDitolak;
use App\Http\Controllers\Controller;
use App\Imports\BarisMentah;
use App\Imports\ImporPenduduk;
use App\Imports\KolomImporPenduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImporPendudukController extends Controller
{
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
            [
                'Siti Nurhayati',
                '1985-04-17',
                'P',
                $wilayah?->dusun ?? 'Dusun Krajan',
                $wilayah?->rw ?? '001',
                $wilayah?->rt ?? '001',
                $pekerjaan?->nama ?? 'Petani',
                'SMA',
            ],
            [
                'Budi Santoso',
                '17/04/1985',
                'L',
                $wilayah?->dusun ?? 'Dusun Krajan',
                $wilayah?->rw ?? '001',
                $wilayah?->rt ?? '001',
                '',
                'S1',
            ],
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

    public function proses(Request $request): RedirectResponse
    {
        $request->validate([
            'berkas' => [
                'required',
                'file',
                'mimes:'.implode(',', config('impor.ekstensi')),
                'max:'.config('impor.maks_kb'),
            ],
            'lewati_duplikat' => ['nullable', 'boolean'],
        ], [
            'berkas.required' => 'Pilih berkas yang akan diimpor.',
            'berkas.mimes' => 'Berkas harus berformat .xlsx, .xls, atau .csv.',
            'berkas.max' => 'Ukuran berkas melebihi batas '
                .round(config('impor.maks_kb') / 1024).' MB. Pecah berkas menjadi beberapa bagian.',
        ], ['berkas' => 'berkas impor']);

        try {
            $baris = Excel::toArray(new BarisMentah(), $request->file('berkas'))[0] ?? [];

            $hasil = (new ImporPenduduk(
                lewatiDuplikat: $request->boolean('lewati_duplikat', true),
            ))->jalankan($baris);

        } catch (ImporDitolak $e) {
            return back()->withErrors(['berkas' => $e->getMessage()]);
        }

        return back()
            ->with('status', $hasil->ringkasan())
            ->with('hasilImpor', $hasil);
    }
}
