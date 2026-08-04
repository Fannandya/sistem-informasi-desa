<?php

namespace App\Http\Controllers\Publik;

use App\Enums\TingkatPrestasi;
use App\Http\Controllers\Controller;
use App\Models\PrestasiDesa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrestasiController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tingkat = TingkatPrestasi::tryFrom((string) $request->input('tingkat'));

        return view('publik.prestasi', [
            'daftar' => PrestasiDesa::query()
                ->tampil()
                ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
                ->terbaru()
                ->get(),
            'tingkat' => $tingkat,
            'jumlahPerTingkat' => PrestasiDesa::query()
                ->tampil()
                ->whereNotNull('tingkat')
                ->selectRaw('tingkat, COUNT(*) as total')
                ->groupBy('tingkat')
                ->pluck('total', 'tingkat'),
        ]);
    }
}
