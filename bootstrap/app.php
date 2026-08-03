<?php

use App\Http\Middleware\EnsureAdminSessionIsFresh;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
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
            'tanpa.cache' => PreventBackHistory::class,
        ]);

        // Tamu yang membuka halaman admin diarahkan ke login admin,
        // bukan ke rute "login" bawaan Laravel yang tidak ada di sini.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
