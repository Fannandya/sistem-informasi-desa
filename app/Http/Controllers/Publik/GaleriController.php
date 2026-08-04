<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\AcaraDesa;
use Illuminate\View\View;

class GaleriController extends Controller
{
    public function index(): View
    {
        return view('publik.galeri.index', [
            'daftar' => AcaraDesa::query()
                ->terbit()
                ->withCount('foto')
                ->with('foto')
                ->terbaru()
                ->paginate(12),
        ]);
    }

    public function show(AcaraDesa $acara): View
    {
        // Draf tidak boleh terbuka meski alamatnya ditebak.
        abort_unless($acara->sudahTerbit(), 404);

        return view('publik.galeri.detail', [
            'acara' => $acara->load('foto'),
            'lainnya' => AcaraDesa::query()
                ->terbit()
                ->whereKeyNot($acara->id)
                ->with('foto')
                ->terbaru()
                ->take(3)
                ->get(),
        ]);
    }
}
