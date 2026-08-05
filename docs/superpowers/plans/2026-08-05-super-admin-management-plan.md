# Super Admin & Multi-Level Account Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement multi-level admin system where Super Admin can create/manage other admins, while regular admins can only manage content.

**Architecture:** Add 4 columns to `admins` table (is_super_admin, is_active, created_by, updated_by). Middleware guards admin management routes. Model hooks auto-track audit trail. Separate controller + views for super admin to manage other admins.

**Tech Stack:** Laravel 12, Blade templates, Tailwind CSS, SQLite (test) / MySQL (prod)

## Global Constraints
- Code language: Indonesian (method names, variables, comments)
- Middleware pattern: follow existing `admin.super` alias registration in bootstrap/app.php
- Validation messages: use `lang/id/validation.php` for consistency
- View components: reuse existing `<x-admin.*>` components (field, select, simpan, dialog)
- Test database: SQLite :memory: (no real DB changes)
- Existing admin (admin@desa.test) must auto-promote to super admin via migration
- No breaking changes to existing routes/guards
- Hard delete admin records (not soft delete, use is_active flag instead)
- Session timeout: stays 1 hour (fitur 17 existing)

---

## File Structure

### Database
- `database/migrations/2026_08_05_000302_add_admin_management_columns_to_admins_table.php` (CREATE)

### Models
- `app/Models/Admin.php` (MODIFY: add relationships, scopes, methods, model hooks)

### Middleware & Requests
- `app/Http/Middleware/VerifyAdminIsSuper.php` (CREATE)
- `app/Http/Requests/Admin/AdminRequest.php` (CREATE)
- `app/Http/Requests/Admin/ResetSandiAdminRequest.php` (CREATE)

### Controllers
- `app/Http/Controllers/Admin/AdminController.php` (CREATE)

### Routes
- `routes/web.php` (MODIFY: add admin management route group)
- `bootstrap/app.php` (MODIFY: register middleware alias)

### Views
- `resources/views/admin/admin/index.blade.php` (CREATE)
- `resources/views/admin/admin/create.blade.php` (CREATE)
- `resources/views/admin/admin/edit.blade.php` (CREATE)

### Tests
- `tests/Feature/Admin/AdminManagementTest.php` (CREATE)

### Documentation
- `CLAUDE.md` (MODIFY: add new routes/features)

---

# Implementation Tasks

## Phase 1: Database & Model

### Task 1: Create Migration

**Files:**
- Create: `database/migrations/2026_08_05_000302_add_admin_management_columns_to_admins_table.php`
- Modify: `app/Models/Admin.php` (model hooks only, in Task 2)

**Interfaces:**
- Consumes: Existing `admins` table with `id, nama, email, password, last_login_at, remember_token, created_at, updated_at`
- Produces: 4 new columns on `admins`: `is_super_admin` (boolean), `is_active` (boolean), `created_by` (FK), `updated_by` (FK); index on `(is_super_admin, is_active)`

**Steps:**

- [ ] **Step 1: Create migration file**

Create `database/migrations/2026_08_05_000302_add_admin_management_columns_to_admins_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('password');
            $table->boolean('is_active')->default(true)->after('is_super_admin');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            $table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('admins')->nullOnDelete();
            
            $table->index(['is_super_admin', 'is_active']);
        });

        // Set all existing admins as super admin
        DB::table('admins')->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['is_super_admin', 'is_active']);
            $table->dropColumn(['is_super_admin', 'is_active', 'created_by', 'updated_by']);
        });
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: Migration completes without error. Check database: `admins` table now has 4 new columns, all existing admins have `is_super_admin = true`.

Verify in database:
```bash
php artisan tinker
>>> Admin::first()
# Should show: is_super_admin: true, is_active: true, created_by: null, updated_by: null
```

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_08_05_000302_add_admin_management_columns_to_admins_table.php
git commit -m "Database: Tambah kolom admin management ke tabel admins"
```

---

### Task 2: Update Admin Model

**Files:**
- Modify: `app/Models/Admin.php`

**Interfaces:**
- Consumes: Admin model with columns: id, nama, email, password, is_super_admin, is_active, created_by, updated_by, last_login_at, remember_token, created_at, updated_at
- Produces: Model methods/relationships:
  - `kreatorAdmin()` — belongsTo relationship
  - `penyuntingAdmin()` — belongsTo relationship
  - `scopeSuperAdmin($query)` — scope
  - `scopeAktif($query)` — scope
  - `isSuperAdmin(): bool` — method
  - `isAktif(): bool` — method
  - Creating hook (auto-set created_by)
  - Updating hook (auto-set updated_by)

**Steps:**

- [ ] **Step 1: Read current Admin model**

Read: `app/Models/Admin.php` (to see structure and where to add code)

- [ ] **Step 2: Add relationships**

After line with last existing method, add:

```php
public function kreatorAdmin() {
    return $this->belongsTo(Admin::class, 'created_by');
}

public function penyuntingAdmin() {
    return $this->belongsTo(Admin::class, 'updated_by');
}
```

- [ ] **Step 3: Add scopes**

Add after relationships:

```php
public function scopeSuperAdmin($query) {
    return $query->where('is_super_admin', true);
}

public function scopeAktif($query) {
    return $query->where('is_active', true);
}
```

- [ ] **Step 4: Add methods**

Add after scopes:

```php
public function isSuperAdmin(): bool {
    return $this->is_super_admin;
}

public function isAktif(): bool {
    return $this->is_active;
}
```

- [ ] **Step 5: Add model hooks for auto-audit**

Add inside the class (before closing brace):

```php
protected static function creating(Admin $admin): void {
    if (auth()->check() && auth()->guard() === 'admin') {
        $admin->created_by = auth('admin')->id();
    }
}

protected static function updating(Admin $admin): void {
    if (auth()->check() && auth()->guard() === 'admin') {
        $admin->updated_by = auth('admin')->id();
    }
}
```

- [ ] **Step 6: Test model in tinker**

```bash
php artisan tinker
>>> $admin = Admin::first()
>>> $admin->isSuperAdmin()
# Should output: true
>>> $admin->isAktif()
# Should output: true
>>> Admin::superAdmin()->count()
# Should output: 1 (at least)
>>> Admin::aktif()->count()
# Should output: 1
```

- [ ] **Step 7: Commit**

```bash
git add app/Models/Admin.php
git commit -m "Model: Tambah relationships, scopes, methods, dan model hooks ke Admin"
```

---

## Phase 2: Middleware, Requests, & Bootstrap

### Task 3: Create VerifyAdminIsSuper Middleware

**Files:**
- Create: `app/Http/Middleware/VerifyAdminIsSuper.php`
- Modify: `bootstrap/app.php`

**Interfaces:**
- Consumes: Authenticated admin user in `auth('admin')` guard
- Produces: Middleware that returns 403 if admin is not super admin; otherwise passes request through

**Steps:**

- [ ] **Step 1: Create middleware file**

Create `app/Http/Middleware/VerifyAdminIsSuper.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAdminIsSuper {
    public function handle(Request $request, Closure $next) {
        if (!auth('admin')->check() || !auth('admin')->user()->is_super_admin) {
            abort(403, 'Hanya super admin yang bisa akses halaman ini.');
        }
        return $next($request);
    }
}
```

- [ ] **Step 2: Register middleware in bootstrap/app.php**

Read: `bootstrap/app.php` (find `withMiddleware` callback)

In the `withMiddleware` callback (around line with other middleware aliases), add:

```php
$middleware->alias('admin.super', \App\Http\Middleware\VerifyAdminIsSuper::class);
```

Example context (find this block and add to it):
```php
->withMiddleware(function (Middleware $middleware) {
    // existing middleware here
    $middleware->alias('admin.super', \App\Http\Middleware\VerifyAdminIsSuper::class);
})
```

- [ ] **Step 3: Test middleware**

```bash
php artisan route:list --grep="admin.super"
# Should show routes that use admin.super middleware (we'll add them in Task 5)
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Middleware/VerifyAdminIsSuper.php bootstrap/app.php
git commit -m "Middleware: Tambah VerifyAdminIsSuper untuk proteksi route kelola admin"
```

---

### Task 4: Create Request Validation Classes

**Files:**
- Create: `app/Http/Requests/Admin/AdminRequest.php`
- Create: `app/Http/Requests/Admin/ResetSandiAdminRequest.php`

**Interfaces:**
- Consumes: Form data (nama, email, password, is_super_admin) for create/edit
- Produces: Validated data; returns error messages in Indonesian

**Steps:**

- [ ] **Step 1: Create AdminRequest**

Create `app/Http/Requests/Admin/AdminRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->check() && auth('admin')->user()->is_super_admin;
    }

    public function rules(): array {
        $adminId = $this->admin?->id;
        return [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:admins,email,' . $adminId,
            'password' => 'required_if:method,POST|nullable|min:8',
            'is_super_admin' => 'boolean',
        ];
    }

    public function messages(): array {
        return [
            'nama.required' => 'Nama admin wajib diisi.',
            'nama.max' => 'Nama admin maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required_if' => 'Sandi wajib diisi saat membuat admin baru.',
            'password.min' => 'Sandi minimal 8 karakter.',
        ];
    }
}
```

- [ ] **Step 2: Create ResetSandiAdminRequest**

Create `app/Http/Requests/Admin/ResetSandiAdminRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResetSandiAdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->check() && auth('admin')->user()->is_super_admin;
    }

    public function rules(): array {
        return [
            'password' => 'required|min:8',
        ];
    }

    public function messages(): array {
        return [
            'password.required' => 'Sandi wajib diisi.',
            'password.min' => 'Sandi minimal 8 karakter.',
        ];
    }
}
```

- [ ] **Step 3: Test validation**

```bash
php artisan tinker
>>> $request = new \App\Http\Requests\Admin\AdminRequest()
>>> $request->setUserResolver(fn() => Admin::superAdmin()->first())
>>> $request->rules()
# Should output array of rules
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Requests/Admin/AdminRequest.php app/Http/Requests/Admin/ResetSandiAdminRequest.php
git commit -m "Requests: Tambah form validation untuk admin management"
```

---

## Phase 3: Controller

### Task 5: Create AdminController

**Files:**
- Create: `app/Http/Controllers/Admin/AdminController.php`

**Interfaces:**
- Consumes: Validated requests (AdminRequest, ResetSandiAdminRequest), Admin model with relationships/scopes/methods from Task 2
- Produces: Controller with methods: index, create, store, edit, update, destroy, aktifkan, nonaktifkan, resetSandi

**Steps:**

- [ ] **Step 1: Create controller file**

Create `app/Http/Controllers/Admin/AdminController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Requests\Admin\ResetSandiAdminRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {
    public function index() {
        $admins = Admin::with('kreatorAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.admin.index', compact('admins'));
    }

    public function create() {
        return view('admin.admin.create');
    }

    public function store(AdminRequest $request) {
        Admin::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => $request->boolean('is_super_admin'),
            'is_active' => true,
        ]);
        
        session()->flash('status', 'Admin berhasil ditambahkan');
        return redirect()->route('admin.admin.index');
    }

    public function edit(Admin $admin) {
        return view('admin.admin.edit', compact('admin'));
    }

    public function update(Admin $admin, AdminRequest $request) {
        $admin->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);
        
        session()->flash('status', 'Admin berhasil diperbarui');
        return redirect()->route('admin.admin.index');
    }

    public function destroy(Admin $admin) {
        $admin->delete();
        session()->flash('status', 'Admin berhasil dihapus');
        return redirect()->route('admin.admin.index');
    }

    public function aktifkan(Admin $admin) {
        $admin->update(['is_active' => true]);
        session()->flash('status', 'Admin diaktifkan');
        return redirect()->route('admin.admin.index');
    }

    public function nonaktifkan(Admin $admin) {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Tidak bisa nonaktifkan diri sendiri');
        }
        $admin->update(['is_active' => false]);
        session()->flash('status', 'Admin dinonaktifkan');
        return redirect()->route('admin.admin.index');
    }

    public function resetSandi(Admin $admin, ResetSandiAdminRequest $request) {
        $admin->update(['password' => Hash::make($request->password)]);
        
        session()->flash('status', 'Sandi admin berhasil direset');
        return redirect()->route('admin.admin.index');
    }
}
```

- [ ] **Step 2: Test controller exists**

```bash
php artisan tinker
>>> class_exists('App\Http\Controllers\Admin\AdminController')
# Should output: true
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/AdminController.php
git commit -m "Controller: Tambah AdminController untuk kelola admin"
```

---

## Phase 4: Routes

### Task 6: Add Admin Management Routes

**Files:**
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: AdminController methods (index, create, store, edit, update, destroy, aktifkan, nonaktifkan, resetSandi)
- Produces: 9 routes under `prefix admin`, `middleware admin.super`, names like `admin.admin.index`, `admin.admin.create`, etc.

**Steps:**

- [ ] **Step 1: Read routes/web.php**

Read entire file to find the auth middleware block where admin routes are registered.

- [ ] **Step 2: Add admin management route group**

Find the line with `middleware('auth:admin')` and the admin route group. Inside that group (before closing), add these routes:

```php
Route::middleware('admin.super')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('admin', [AdminController::class, 'index'])->name('admin.index');
        Route::get('admin/tambah', [AdminController::class, 'create'])->name('admin.create');
        Route::post('admin', [AdminController::class, 'store'])->name('admin.store');
        Route::get('admin/{admin}/ubah', [AdminController::class, 'edit'])->name('admin.edit');
        Route::put('admin/{admin}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('admin/{admin}', [AdminController::class, 'destroy'])->name('admin.destroy');
        Route::post('admin/{admin}/aktifkan', [AdminController::class, 'aktifkan'])->name('admin.aktifkan');
        Route::post('admin/{admin}/nonaktifkan', [AdminController::class, 'nonaktifkan'])->name('admin.nonaktifkan');
        Route::post('admin/{admin}/reset-sandi', [AdminController::class, 'resetSandi'])->name('admin.reset-sandi');
    });
});
```

**Important:** Make sure to add `use App\Http\Controllers\Admin\AdminController;` at the top of the file with other controller imports.

- [ ] **Step 3: Verify routes**

```bash
php artisan route:list --grep="admin.admin"
# Should show 9 routes:
# - admin.admin.index (GET)
# - admin.admin.create (GET)
# - admin.admin.store (POST)
# - admin.admin.edit (GET)
# - admin.admin.update (PUT)
# - admin.admin.destroy (DELETE)
# - admin.admin.aktifkan (POST)
# - admin.admin.nonaktifkan (POST)
# - admin.admin.reset-sandi (POST)
```

- [ ] **Step 4: Commit**

```bash
git add routes/web.php
git commit -m "Routes: Tambah admin management routes dengan middleware admin.super"
```

---

## Phase 5: Views

### Task 7: Create Admin List View (index)

**Files:**
- Create: `resources/views/admin/admin/index.blade.php`

**Interfaces:**
- Consumes: `$admins` collection (paginated, with kreatorAdmin relationship loaded), route names (admin.admin.create, admin.admin.edit, etc.)
- Produces: HTML table view with admin list, buttons for edit/delete/aktifkan/nonaktifkan/reset-sandi

**Steps:**

- [ ] **Step 1: Create directory if not exists**

```bash
mkdir -p resources/views/admin/admin
```

- [ ] **Step 2: Create index.blade.php**

Create `resources/views/admin/admin/index.blade.php`:

```blade
@extends('admin.layouts.app')

@section('konten')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-on-surface">Kelola Admin</h1>
        @if(auth('admin')->user()->is_super_admin)
            <a href="{{ route('admin.admin.create') }}" class="bg-primary text-on-primary px-4 py-2 rounded-lg hover:bg-primary/90">
                + Tambah Admin
            </a>
        @endif
    </div>

    @if($admins->isEmpty())
        <div class="text-center py-12">
            <p class="text-on-surface/60">Belum ada admin terdaftar.</p>
        </div>
    @else
        <div class="overflow-x-auto bg-surface rounded-lg border border-outline">
            <table class="w-full text-sm">
                <thead class="bg-surface-container border-b border-outline">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Nama</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Email</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Type</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Last Login</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Dibuat Oleh</th>
                        <th class="text-center px-4 py-3 font-semibold text-on-surface">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                        <tr class="border-b border-outline hover:bg-surface-container/50">
                            <td class="px-4 py-3">{{ $admin->nama }}</td>
                            <td class="px-4 py-3">{{ $admin->email }}</td>
                            <td class="px-4 py-3">
                                @if($admin->is_super_admin)
                                    <span class="inline-block px-2 py-1 bg-primary/20 text-primary rounded text-xs font-semibold">Super Admin</span>
                                @else
                                    <span class="inline-block px-2 py-1 bg-secondary/20 text-secondary rounded text-xs font-semibold">Admin</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($admin->is_active)
                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="inline-block px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-on-surface/60">
                                @if($admin->last_login_at)
                                    {{ $admin->last_login_at->diffForHumans() }}
                                @else
                                    Belum pernah login
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $admin->kreatorAdmin?->nama ?? 'System' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    @if(auth('admin')->user()->is_super_admin)
                                        <a href="{{ route('admin.admin.edit', $admin) }}" class="text-primary hover:underline" title="Edit">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        
                                        @if($admin->is_active)
                                            <form method="POST" action="{{ route('admin.admin.nonaktifkan', $admin) }}" class="inline" 
                                                  data-konfirmasi data-judul="Nonaktifkan Admin?" 
                                                  data-pesan="Admin {{ $admin->nama }} tidak akan bisa login."
                                                  data-tombol="Nonaktifkan" data-jenis="bahaya">
                                                @csrf
                                                <button type="submit" class="text-error hover:underline" title="Nonaktifkan">
                                                    <span class="material-symbols-outlined text-lg">block</span>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.admin.aktifkan', $admin) }}" class="inline"
                                                  data-konfirmasi data-judul="Aktifkan Admin?" 
                                                  data-pesan="Admin {{ $admin->nama }} akan bisa login kembali."
                                                  data-tombol="Aktifkan" data-jenis="sukses">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:underline" title="Aktifkan">
                                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('admin.admin.reset-sandi', $admin) }}" class="inline"
                                              data-konfirmasi data-judul="Reset Sandi?" 
                                              data-pesan="Anda akan diminta input sandi baru untuk {{ $admin->nama }}."
                                              data-tombol="Lanjutkan" data-jenis="tanya">
                                            @csrf
                                            <button type="submit" class="text-secondary hover:underline" title="Reset Sandi">
                                                <span class="material-symbols-outlined text-lg">key</span>
                                            </button>
                                        </form>
                                        
                                        @if($admin->id !== auth('admin')->id())
                                            <form method="POST" action="{{ route('admin.admin.destroy', $admin) }}" class="inline"
                                                  data-konfirmasi data-judul="Hapus Admin?" 
                                                  data-pesan="Admin {{ $admin->nama }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan."
                                                  data-tombol="Hapus" data-bahaya>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error hover:underline" title="Hapus">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $admins->links() }}
        </div>
    @endif
</div>
@endsection
```

- [ ] **Step 3: Test view renders (no errors)**

Visit: `http://localhost:8000/admin/admin` (after implementing routes/controller)

Expected: Table displays with admin list (or "Belum ada admin" if only 1 admin exists)

- [ ] **Step 4: Commit**

```bash
git add resources/views/admin/admin/index.blade.php
git commit -m "Views: Tambah halaman daftar admin (index)"
```

---

### Task 8: Create Admin Create View

**Files:**
- Create: `resources/views/admin/admin/create.blade.php`

**Interfaces:**
- Consumes: Route `admin.admin.store`, component `<x-admin.field>`, `<x-admin.select>`, `<x-admin.simpan>`
- Produces: HTML form for creating new admin

**Steps:**

- [ ] **Step 1: Create create.blade.php**

Create `resources/views/admin/admin/create.blade.php`:

```blade
@extends('admin.layouts.app')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-on-surface">Tambah Admin Baru</h1>
        <p class="text-on-surface/60 mt-1">Buat akun admin baru untuk mengelola konten desa</p>
    </div>

    <form method="POST" action="{{ route('admin.admin.store') }}" class="space-y-6">
        @csrf

        <x-admin.field
            label="Nama Admin"
            name="nama"
            type="text"
            placeholder="Contoh: Budi Santoso"
            required
            :value="old('nama')"
            :error="$errors->first('nama')"
        />

        <x-admin.field
            label="Email"
            name="email"
            type="email"
            placeholder="contoh@desa.test"
            required
            :value="old('email')"
            :error="$errors->first('email')"
        />

        <x-admin.field
            label="Sandi"
            name="password"
            type="password"
            placeholder="Minimal 8 karakter"
            required
            :error="$errors->first('password')"
        />

        <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_super_admin" value="1" 
                       {{ old('is_super_admin') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-outline text-primary">
                <span class="text-on-surface font-medium">Jadikan Super Admin</span>
            </label>
            <p class="text-sm text-on-surface/60 ml-8">
                Super admin bisa membuat dan mengelola admin lain, plus akses ke semua konten.
            </p>
        </div>

        <div class="flex gap-3 pt-4">
            <x-admin.simpan>Simpan Admin</x-admin.simpan>
            <a href="{{ route('admin.admin.index') }}" class="px-4 py-2 text-on-surface hover:bg-surface-container rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
```

- [ ] **Step 2: Test view renders**

Visit: `http://localhost:8000/admin/admin/tambah`

Expected: Form displays with fields for nama, email, password, checkbox for is_super_admin

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/admin/create.blade.php
git commit -m "Views: Tambah halaman buat admin baru (create)"
```

---

### Task 9: Create Admin Edit View

**Files:**
- Create: `resources/views/admin/admin/edit.blade.php`

**Interfaces:**
- Consumes: `$admin` model instance, route `admin.admin.update`, components `<x-admin.field>`, `<x-admin.simpan>`
- Produces: HTML form for editing admin (nama, is_super_admin; email read-only)

**Steps:**

- [ ] **Step 1: Create edit.blade.php**

Create `resources/views/admin/admin/edit.blade.php`:

```blade
@extends('admin.layouts.app')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-on-surface">Edit Admin</h1>
        <p class="text-on-surface/60 mt-1">Ubah informasi admin {{ $admin->nama }}</p>
    </div>

    <form method="POST" action="{{ route('admin.admin.update', $admin) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.field
            label="Nama Admin"
            name="nama"
            type="text"
            placeholder="Contoh: Budi Santoso"
            required
            :value="old('nama', $admin->nama)"
            :error="$errors->first('nama')"
        />

        <div class="space-y-2">
            <label class="block text-sm font-medium text-on-surface">Email (tidak bisa diubah)</label>
            <input type="email" value="{{ $admin->email }}" disabled
                   class="w-full px-3 py-2 bg-surface-container border border-outline rounded-lg text-on-surface/50">
            <p class="text-xs text-on-surface/60">Email bersifat permanen dan tidak bisa diubah untuk keamanan</p>
        </div>

        <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_super_admin" value="1" 
                       {{ old('is_super_admin', $admin->is_super_admin) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-outline text-primary">
                <span class="text-on-surface font-medium">Super Admin</span>
            </label>
            <p class="text-sm text-on-surface/60 ml-8">
                Berikan akses untuk membuat dan mengelola admin lain
            </p>
        </div>

        <div class="flex gap-3 pt-4">
            <x-admin.simpan>Simpan Perubahan</x-admin.simpan>
            <a href="{{ route('admin.admin.index') }}" class="px-4 py-2 text-on-surface hover:bg-surface-container rounded-lg">
                Batal
            </a>
        </div>
    </form>

    <div class="bg-surface-container border border-outline rounded-lg p-4 mt-8">
        <h3 class="font-semibold text-on-surface mb-2">Informasi Lainnya</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-on-surface/60">Status</dt>
                <dd class="font-medium">
                    @if($admin->is_active)
                        <span class="text-green-600">Aktif</span>
                    @else
                        <span class="text-red-600">Nonaktif</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Dibuat</dt>
                <dd class="font-medium">{{ $admin->created_at->format('d M Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Dibuat Oleh</dt>
                <dd class="font-medium">{{ $admin->kreatorAdmin?->nama ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Terakhir Login</dt>
                <dd class="font-medium">
                    @if($admin->last_login_at)
                        {{ $admin->last_login_at->format('d M Y H:i') }}
                    @else
                        Belum pernah login
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Test view renders**

Visit: `http://localhost:8000/admin/admin/{admin-id}/ubah` (use a real admin ID)

Expected: Form displays with nama editable, email disabled/read-only, checkbox for is_super_admin, info section at bottom

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/admin/edit.blade.php
git commit -m "Views: Tambah halaman edit admin (edit)"
```

---

## Phase 6: Testing

### Task 10: Write Feature Tests

**Files:**
- Create: `tests/Feature/Admin/AdminManagementTest.php`

**Interfaces:**
- Consumes: Admin model, routes, controller, middleware, requests from previous tasks
- Produces: 12+ passing feature tests covering all functionality

See plan file for complete test code (Task 10 full details).

---

## Phase 7: Documentation

### Task 11: Update CLAUDE.md & Final Cleanup

Update CLAUDE.md dengan:
- Routes baru (9 routes admin management)
- Middleware baru (admin.super)
- Migration baru (kolom admins)
- Update test count

---

# Summary

**Total: 11 Tasks across 7 Phases**

| Phase | Tasks | Time | Output |
|---|---|---|---|
| 1: Database & Model | 2 | 1 hr | Migration, Admin model with relationships/scopes/hooks |
| 2: Middleware & Requests | 2 | 1 hr | VerifyAdminIsSuper middleware, 2 request classes, bootstrap registration |
| 3: Controller | 1 | 1 hr | AdminController with 7 methods |
| 4: Routes | 1 | 0.5 hr | 9 routes under `admin.super` middleware |
| 5: Views | 3 | 1.5 hr | index, create, edit views with Blade + Tailwind |
| 6: Testing | 1 | 1 hr | 16 feature tests covering all functionality |
| 7: Docs | 1 | 0.5 hr | CLAUDE.md updated with new routes & fitur |
| **Total** | **11** | **~6 hr** | **Fully functional multi-level admin system** |

---

**Execution approach: Subagent-Driven (Main Claude controls each task dispatch)**
