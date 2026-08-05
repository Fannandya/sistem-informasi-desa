<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    public function test_admin_dapat_login_dan_access_dashboard(): void
    {
        $response = $this->post(route('admin.login.proses'), [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('admin.dashboard');
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_last_activity_diset_saat_login(): void
    {
        $this->post(route('admin.login.proses'), [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $this->assertTrue(session()->has('last_activity'));
        $this->assertIsInt(session('last_activity'));
    }

    public function test_session_tidak_expire_dalam_30_menit(): void
    {
        $this->actingAs($this->admin, 'admin');

        // Set last activity ke 30 menit lalu (1800 detik)
        session(['last_activity' => time() - 1800]);

        $response = $this->get(route('admin.dashboard'));

        // Should still be authenticated
        $response->assertOk();
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_session_expire_setelah_60_menit(): void
    {
        $this->actingAs($this->admin, 'admin');

        // Set last activity ke 61 menit lalu (3660 detik)
        session(['last_activity' => time() - 3660]);

        $response = $this->get(route('admin.dashboard'));

        // Should redirect to login
        $response->assertRedirectToRoute('admin.login');
        $this->assertGuest('admin');
    }

    public function test_ajax_request_return_401_saat_session_expire(): void
    {
        $this->actingAs($this->admin, 'admin');

        // Set last activity ke 61 menit lalu
        session(['last_activity' => time() - 3660]);

        $response = $this->getJson(route('admin.dashboard'));

        // Should return 401
        $response->assertStatus(401)
            ->assertJson(['message' => 'Session expired']);
        $this->assertGuest('admin');
    }

    public function test_last_activity_updated_setiap_request(): void
    {
        $this->actingAs($this->admin, 'admin');

        $oldTime = time() - 1800; // 30 menit lalu
        session(['last_activity' => $oldTime]);

        $this->get(route('admin.dashboard'));

        // last_activity should be updated
        $newTime = session('last_activity');
        $this->assertGreaterThan($oldTime, $newTime);
    }

    public function test_logout_hapus_session(): void
    {
        $this->actingAs($this->admin, 'admin');

        session(['last_activity' => time()]);

        $response = $this->post(route('admin.logout'));

        $response->assertRedirectToRoute('admin.login');
        $this->assertGuest('admin');
        $this->assertFalse(session()->has('last_activity'));
    }
}
