<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselImage;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\Request;

class CarouselImageController extends Controller
{
    use Concerns\MengelolaGambar;

    public function index()
    {
        $gambar = CarouselImage::urut()->get();

        return view('admin.carousel.index', compact('gambar'));
    }

    public function create()
    {
        return view('admin.carousel.create');
    }

    public function store(Request $request)
    {
        $validasi = $request->validate([
            'gambar' => 'required|image|max:4096',
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $unggahan = app(LayananUnggahGambar::class);
        $path = $unggahan->simpan($request->file('gambar'), 'carousel');

        CarouselImage::create([
            'path' => $path,
            'judul' => $validasi['judul'],
            'deskripsi' => $validasi['deskripsi'],
            'urutan' => CarouselImage::max('urutan') + 1 ?? 0,
            'admin_id' => auth('admin')->id(),
        ]);

        return redirect(route('admin.carousel.index'))
            ->with('status', 'Gambar carousel ditambahkan.');
    }

    public function edit(CarouselImage $carouselImage)
    {
        return view('admin.carousel.edit', compact('carouselImage'));
    }

    public function update(Request $request, CarouselImage $carouselImage)
    {
        $validasi = $request->validate([
            'gambar' => 'nullable|image|max:4096',
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'tampil' => 'nullable|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            $unggahan = app(LayananUnggahGambar::class);
            $path = $unggahan->ganti($carouselImage->path, $request->file('gambar'), 'carousel');
            $carouselImage->update(['path' => $path]);
        }

        $carouselImage->update([
            'judul' => $validasi['judul'],
            'deskripsi' => $validasi['deskripsi'],
            'tampil' => $validasi['tampil'] ?? $carouselImage->tampil,
        ]);

        return redirect(route('admin.carousel.index'))
            ->with('status', 'Gambar carousel diperbarui.');
    }

    public function destroy(CarouselImage $carouselImage)
    {
        $carouselImage->delete();

        return redirect(route('admin.carousel.index'))
            ->with('status', 'Gambar carousel dihapus.');
    }

    public function geser(Request $request, CarouselImage $carouselImage)
    {
        $arah = $request->input('arah'); // 'atas' atau 'bawah'
        $lain = null;

        if ($arah === 'atas') {
            $lain = CarouselImage::where('urutan', '<', $carouselImage->urutan)
                ->orderByDesc('urutan')
                ->first();
        } else {
            $lain = CarouselImage::where('urutan', '>', $carouselImage->urutan)
                ->orderBy('urutan')
                ->first();
        }

        if ($lain) {
            [$carouselImage->urutan, $lain->urutan] = [$lain->urutan, $carouselImage->urutan];
            $carouselImage->save();
            $lain->save();
        }

        return redirect(route('admin.carousel.index'))
            ->with('status', 'Urutan gambar diperbarui.');
    }
}
