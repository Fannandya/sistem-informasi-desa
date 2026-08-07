<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsureAdminSessionIsFresh;
use App\Http\Middleware\EnsureAdminSessionNotExpired;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\TambahHeaderKeamanan;
use App\Http\Middleware\VerifyAdminIsSuper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tamu.admin' => RedirectIfAdminAuthenticated::class,
            'sesi.segar' => EnsureAdminSessionIsFresh::class,
            'admin.aktif' => EnsureAdminIsActive::class,
            'tanpa.cache' => PreventBackHistory::class,
            'admin.super' => VerifyAdminIsSuper::class,
        ]);

        // Tamu yang membuka halaman admin diarahkan ke login admin,
        // bukan ke rute "login" bawaan Laravel yang tidak ada di sini.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        // Berlaku untuk seluruh halaman, publik maupun admin.
        $middleware->append(TambahHeaderKeamanan::class);

        // Add session timeout check for admin routes
        $middleware->append(EnsureAdminSessionNotExpired::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
