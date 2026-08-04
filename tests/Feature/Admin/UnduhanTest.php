<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Unduhan berkas di panel admin.
 *
 * Middleware PreventBackHistory pernah memakai withHeaders(), metode yang
 * hanya ada pada Illuminate\Http\Response. Akibatnya setiap unduhan berkas —
 * yang mengembalikan StreamedResponse — gagal dengan error 500. Bug ini baru
 * ketahuan saat fitur impor dibuat, padahal seluruh fitur ekspor (fitur 07)
 * juga berupa unduhan.
 */
class UnduhanTest extends TestCase
{
    use RefreshDatabase;

    public function test_unduhan_streamed_tidak_gagal_di_middleware_admin(): void
    {
        Route::middleware(['auth:admin', 'tanpa.cache'])
            ->get('/admin/_uji-unduhan', fn () => response()->streamDownload(
                fn () => print('isi berkas uji'),
                'uji.csv',
            ));

        $admin = Admin::create(['nama' => 'A', 'email' => 'a@desa.test', 'password' => 'rahasia123']);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/_uji-unduhan')
            ->assertOk();

        $this->assertSame('isi berkas uji', $response->streamedContent());

        // Header anti-cache tetap harus terpasang pada unduhan.
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
