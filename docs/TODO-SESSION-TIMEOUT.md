# TODO: Session Timeout 1 Jam — Tahap 3 Fitur 17

**Status:** Ready to Start  
**Estimated Time:** ~1 jam  
**Scope:** Auto logout setelah 1 jam inaktif, AJAX session check return 401  
**Expected Outcome:** 462+ tests passing, 1 new middleware, 1 new commit

---

## 📋 Checklist

### Priority 1: Config Session Lifetime (5 min)

**File:** `config/session.php`

**Find & update:**
```php
// ❌ OLD:
'lifetime' => env('SESSION_LIFETIME', 120), // 2 hours

// ✅ NEW:
'lifetime' => env('SESSION_LIFETIME', 60), // 1 hour = 60 minutes
```

**Verify di `.env`:**
```
SESSION_LIFETIME=60
```

---

### Priority 2: Create Session Timeout Middleware (20 min)

**File:** `app/Http/Middleware/EnsureAdminSessionNotExpired.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSessionNotExpired extends Closure
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated
        if (!auth('admin')->check()) {
            return $next($request);
        }

        // Get last activity timestamp
        $lastActivity = session('last_activity');
        $timeout = config('session.lifetime') * 60; // Convert to seconds

        // Check if session has expired
        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            auth('admin')->logout();

            // If AJAX request, return 401
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired'], 401);
            }

            // Redirect to login with message
            return redirect(route('admin.login'))
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        // Update last activity
        session(['last_activity' => time()]);

        return $next($request);
    }
}
```

---

### Priority 3: Register Middleware (10 min)

**File:** `bootstrap/app.php`

**Find `middleware:` section, add:**
```php
->withMiddleware(function (Middleware $middleware) {
    // ... existing middleware ...

    // Add session timeout check for admin routes
    $middleware->append(\App\Http\Middleware\EnsureAdminSessionNotExpired::class);
})
```

---

### Priority 4: Update Admin Login Controller (10 min)

**File:** `app/Http/Controllers/Admin/Auth/LoginController.php`

**Dalam method masuk(), setelah auth()->attempt() berhasil:**
```php
public function masuk(LoginRequest $request): RedirectResponse
{
    $validasi = $request->validated();

    if (Auth::guard('admin')->attempt($validasi)) {
        session(['last_activity' => time()]); // ← ADD THIS

        return redirect(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'Kredensial tidak sesuai dengan catatan kami.',
    ]);
}
```

---

### Priority 5: Create Tests (25 min)

**File:** `tests/Feature/Admin/SessionTimeoutTest.php`

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
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
```

---

### Priority 6: Update Admin Login View (Optional) (5 min)

**File:** `resources/views/admin/auth/login.blade.php`

**Add pesan timeout (jika ada error session):**
```blade
@if (session('error'))
    <div class="mb-4 rounded-lg bg-error/10 px-4 py-3 text-sm text-error">
        {{ session('error') }}
    </div>
@endif
```

---

### Priority 7: Update CLAUDE.md (5 min)

**File:** `CLAUDE.md`

**Add ke §5 Middleware:**
```markdown
| `EnsureAdminSessionNotExpired` | Check session timeout (1 jam inaktif), auto logout, return 401 untuk AJAX |
```

**Add ke §3.10 (Aturan Wajib Baru):**
```markdown
### 3.10 Session timeout 1 jam
Admin session berakhir setelah **60 menit inaktif**. Middleware `EnsureAdminSessionNotExpired`
menangani: (1) auto logout untuk regular request → redirect ke login, (2) AJAX request → return 401.
`last_activity` session key di-update setiap request.
```

---

## 🧪 Testing

```bash
# Run new tests
php artisan test tests/Feature/Admin/SessionTimeoutTest.php

# Run all tests
php artisan test 2>&1 | tail -5
# Target: 462+ tests passing
```

---

## 📝 Commit Message

```
Fitur 17 (3/3) — Session Timeout: 1 Jam Auto Logout + AJAX 401

- Update config/session.php lifetime ke 60 menit
- Create EnsureAdminSessionNotExpired middleware
  - Track last_activity di session
  - Auto logout jika >60 menit inaktif
  - Regular request: redirect ke login dengan pesan
  - AJAX request: return 401 JSON
- Register middleware di bootstrap/app.php
- Update LoginController masuk() untuk set last_activity
- Add SessionTimeoutTest (7 tests)
- Update admin login view dengan pesan error timeout
- Update CLAUDE.md dengan dokumentasi session timeout

Status: 462+ tests passing, session timeout fully implemented
```

---

## 📊 Metrics

| Task | Time | Status |
|------|------|--------|
| Priority 1 (Config) | 5 min | ⏱️ |
| Priority 2 (Middleware) | 20 min | ⏱️ |
| Priority 3 (Register) | 10 min | ⏱️ |
| Priority 4 (Controller) | 10 min | ⏱️ |
| Priority 5 (Tests) | 25 min | ⏱️ |
| Priority 6-7 (View/Docs) | 10 min | ⏱️ |
| **TOTAL** | **~80 min** | ⏱️ |

---

## ✅ Final Checklist

- [ ] config/session.php updated (lifetime = 60)
- [ ] Middleware created (EnsureAdminSessionNotExpired)
- [ ] Middleware registered di bootstrap/app.php
- [ ] LoginController updated (set last_activity)
- [ ] SessionTimeoutTest created (7 tests)
- [ ] Admin login view updated dengan error pesan
- [ ] CLAUDE.md updated
- [ ] All tests passing (462+)
- [ ] Commit: "Fitur 17 (3/3) — Session Timeout: 1 Jam..."
- [ ] Push to main

---

## 🎯 After Completion

**Tahap 1, 2, 3 COMPLETE = Fitur 17 SELESAI! 🎉**

Semua 3 tahap akan menjadi 3 commits terpisah:
1. ✅ Commit 1bf5463: Kategori Potensi & Fasilitas Complete
2. Commit TBD: Pekerjaan Template Locked
3. Commit TBD: Session Timeout 1 Jam

Total: +3 features, +80 menit development time
