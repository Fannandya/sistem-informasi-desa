<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function tampilkan(): View
    {
        return view('admin.profil.kata-sandi');
    }

    public function perbarui(UpdatePasswordRequest $request): RedirectResponse
    {
        $sandiBaru = $request->string('kata_sandi_baru')->value();

        $request->user('admin')
            ->forceFill(['password' => $sandiBaru])
            ->save();

        // Fitur 01: setelah kata sandi diganti, sesi di perangkat lain berakhir.
        // Butuh middleware AuthenticateSession aktif pada grup rute admin.
        Auth::guard('admin')->logoutOtherDevices($sandiBaru);

        return back()->with('status', 'Kata sandi berhasil diperbarui.');
    }
}
