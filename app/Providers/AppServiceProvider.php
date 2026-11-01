<?php

namespace App\Providers;

use App\Models\PengaturanDesa;
use App\Services\LayananUnggahGambar;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Stateless — cukup satu instance untuk seluruh permintaan.
        $this->app->singleton(LayananUnggahGambar::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->bagikanPengaturanDesa();
    }

    /**
     * Fitur 03 — identitas desa tersedia di seluruh tampilan sebagai $desa,
     * sehingga nama desa, logo, dan kontak tidak pernah ditulis di dalam kode
     * layout mana pun.
     */
    private function bagikanPengaturanDesa(): void
    {
        View::composer('*', function ($view): void {
            try {
                $view->with('desa', PengaturanDesa::ambil());
            } catch (Throwable) {
                // Sebelum migrasi dijalankan tabelnya belum ada. Halaman error
                // dan perintah artisan tetap harus bisa dirender tanpa ini.
                $view->with('desa', new PengaturanDesa(['nama_desa' => config('app.name')]));
            }
        });
    }
}
