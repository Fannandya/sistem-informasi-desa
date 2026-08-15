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

        // Tidak ada opsi "ingat saya": remember-token Laravel berlaku ~5
        // tahun dan berjalan lepas dari EnsureAdminSessionNotExpired, jadi
        // kalau diaktifkan, timeout sesi yang wajib menurut PRD 8 tidak akan
        // pernah berlaku lagi untuk perangkat yang mencentangnya.
        if (! Auth::guard('admin')->attempt($kredensial)) {
            RateLimiter::hit($kunci, self::DETIK_BLOKIR);

            // Pesan sengaja dibuat sama untuk email salah maupun kata sandi
            // salah, agar tidak membocorkan email mana yang terdaftar.
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        // Kredensial benar, tetapi aksesnya sudah dicabut. Dikeluarkan lagi
        // sebelum sesi sempat membawa identitas admin. Tanpa ini, tombol
        // "Nonaktifkan" pada kelola admin hanya menandai kolom di basis data
        // tanpa benar-benar menutup pintu masuk.
        if (! Auth::guard('admin')->user()->is_active) {
            Auth::guard('admin')->logout();

            throw ValidationException::withMessages([
                'email' => 'Akun ini sedang dinonaktifkan. Hubungi admin utama desa.',
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
