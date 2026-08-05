<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Batas percobaan login sebelum diblokir sementara (fitur 01).
     */
    private const MAKS_PERCOBAAN = 5;

    private const DETIK_BLOKIR = 60;

    public function tampilkan(): View
    {
        return view('admin.auth.login');
    }

    public function masuk(LoginRequest $request): RedirectResponse
    {
        $kunci = $request->kunciPembatas();

        $this->pastikanBelumDiblokir($kunci);

        $kredensial = $request->only('email', 'password');
        $ingatSaya = $request->boolean('remember');

        if (! Auth::guard('admin')->attempt($kredensial, $ingatSaya)) {
            RateLimiter::hit($kunci, self::DETIK_BLOKIR);

            // Pesan sengaja dibuat sama untuk email salah maupun kata sandi
            // salah, agar tidak membocorkan email mana yang terdaftar.
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($kunci);

        $request->session()->regenerate();
        session(['last_activity' => time()]);

        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('status', 'Anda telah keluar dari panel admin.');
    }

    private function pastikanBelumDiblokir(string $kunci): void
    {
        if (! RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => sprintf(
                'Terlalu banyak percobaan masuk. Coba lagi dalam %d detik.',
                RateLimiter::availableIn($kunci),
            ),
        ]);
    }
}
