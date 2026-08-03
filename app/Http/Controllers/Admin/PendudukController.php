<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArsipkanPendudukRequest;
use App\Http\Requests\Admin\PendudukRequest;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use App\Queries\PencarianPenduduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendudukController extends Controller
{
    private const PER_HALAMAN = 50;

    public function index(Request $request): View
    {
        $pencarian = PencarianPenduduk::dariRequest($request);

        $daftar = $pencarian
            ->terapkan(Penduduk::query()->aktif()->with(['wilayah', 'pekerjaan']))
            ->orderBy('nama_lengkap')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('admin.penduduk.index', [
            'daftar' => $daftar,
            'pencarian' => $pencarian,
        ] + $this->pilihanFilter());
    }

    public function create(): View
    {
        return view('admin.penduduk.form', [
            'penduduk' => new Penduduk(),
            'wilayahList' => Wilayah::urut()->get(),
            'pekerjaanList' => RefPekerjaan::orderBy('nama')->get(),
        ]);
    }

    public function store(PendudukRequest $request): RedirectResponse
    {
        $penduduk = Penduduk::create($request->validated());

        return redirect()
            ->route('admin.penduduk.index')
            ->with('status', "Data {$penduduk->nama_lengkap} berhasil ditambahkan.");
    }

    public function edit(Penduduk $penduduk): View
    {
        // Data terarsip tidak boleh disunting selama masih berstatus arsip.
        abort_if($penduduk->terarsip(), 403, 'Data yang diarsipkan tidak dapat diubah. Pulihkan lebih dulu.');

        return view('admin.penduduk.form', [
            'penduduk' => $penduduk,
            'wilayahList' => Wilayah::urut()->get(),
            'pekerjaanList' => RefPekerjaan::orderBy('nama')->get(),
        ]);
    }

    public function update(PendudukRequest $request, Penduduk $penduduk): RedirectResponse
    {
        abort_if($penduduk->terarsip(), 403);

        $penduduk->update($request->validated());

        return redirect()
            ->route('admin.penduduk.index')
            ->with('status', "Data {$penduduk->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Mengarsipkan warga beserta sebabnya. Bukan penghapusan permanen —
     * datanya tetap tersimpan dan bisa dipulihkan (PRD 6.16).
     */
    public function arsipkan(ArsipkanPendudukRequest $request, Penduduk $penduduk): RedirectResponse
    {
        $penduduk->fill($request->validated())->save();
        $penduduk->delete();

        return redirect()
            ->route('admin.penduduk.index')
            ->with('status', "Data {$penduduk->nama_lengkap} dipindahkan ke arsip.");
    }

    /**
     * @return array{wilayahList: mixed, pekerjaanList: mixed, dusunList: mixed}
     */
    private function pilihanFilter(): array
    {
        return [
            'wilayahList' => Wilayah::urut()->get(),
            'pekerjaanList' => RefPekerjaan::orderBy('nama')->get(),
            'dusunList' => Wilayah::query()->distinct()->orderBy('dusun')->pluck('dusun'),
        ];
    }
}
