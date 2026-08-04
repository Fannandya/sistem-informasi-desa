<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTeksTampilanRequest;
use App\Models\TeksTampilan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeksTampilanController extends Controller
{
    public function tampilkan(): View
    {
        return view('admin.teks-tampilan.index', [
            'nilai' => TeksTampilan::semua(),
        ]);
    }

    public function perbarui(UpdateTeksTampilanRequest $request, string $grup): RedirectResponse
    {
        abort_unless(
            collect(config('teks-tampilan'))->pluck('grup')->unique()->contains($grup),
            404,
        );

        foreach ($request->nilaiUntukDisimpan() as $kunci => $nilai) {
            TeksTampilan::updateOrCreate(
                ['kunci' => $kunci],
                ['nilai' => $nilai, 'grup' => $grup, 'admin_id' => auth('admin')->id()],
            );
        }

        return redirect()
            ->route('admin.teks-tampilan.index', ['grup' => $grup])
            ->with('status', 'Teks tampilan berhasil disimpan.');
    }
}
