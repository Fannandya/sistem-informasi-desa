<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WilayahController extends Controller
{
    public function index(): View
    {
        return view('admin.wilayah.index', [
            'daftar' => Wilayah::urut()->withCount(['penduduk' => fn ($q) => $q->withTrashed()])->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        return $this->simpan(new Wilayah(), $data, 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, Wilayah $wilayah): RedirectResponse
    {
        $data = $this->validasi($request, $wilayah);

        return $this->simpan($wilayah, $data, 'Wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah): RedirectResponse
    {
        // Menghapus wilayah yang masih dipakai akan memutus data warga dari
        // sebaran wilayahnya, dan statistik per dusun jadi tidak lengkap.
        if ($wilayah->sedangDipakai()) {
            return back()->with('galat', sprintf(
                'Wilayah %s tidak dapat dihapus karena masih dipakai oleh %d data warga. '
                .'Pindahkan warga tersebut ke wilayah lain lebih dulu.',
                $wilayah->label(),
                $wilayah->jumlahPemakai(),
            ));
        }

        $label = $wilayah->label();
        $wilayah->delete();

        return back()->with('status', "Wilayah {$label} dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request, ?Wilayah $kecuali = null): array
    {
        $data = $request->validate([
            'dusun' => ['required', 'string', 'max:60'],
            'rw' => ['required', 'string', 'max:5'],
            'rt' => ['required', 'string', 'max:5'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [], [
            'dusun' => 'nama dusun',
        ]);

        $data['dusun'] = trim($data['dusun']);
        $data['rw'] = Wilayah::normalkanNomor($data['rw']);
        $data['rt'] = Wilayah::normalkanNomor($data['rt']);
        $data['urutan'] = $data['urutan'] ?? 0;

        if ($data['rw'] === '' || $data['rt'] === '') {
            throw ValidationException::withMessages([
                'rt' => 'Nomor RT dan RW harus berisi angka.',
            ]);
        }

        return $data;
    }

    private function simpan(Wilayah $wilayah, array $data, string $pesan): RedirectResponse
    {
        $bentrok = Wilayah::where('dusun', $data['dusun'])
            ->where('rw', $data['rw'])
            ->where('rt', $data['rt'])
            ->when($wilayah->exists, fn ($q) => $q->whereKeyNot($wilayah->getKey()))
            ->exists();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'rt' => "Wilayah {$data['dusun']} RT {$data['rt']}/RW {$data['rw']} sudah terdaftar.",
            ]);
        }

        $wilayah->fill($data)->save();

        return back()->with('status', $pesan);
    }
}
