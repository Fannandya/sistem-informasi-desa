# Design Spec: Super Admin & Multi-Level Account Management

**Date:** 2026-08-05  
**Author:** Claude  
**Status:** Draft (awaiting user review)  
**Scenario:** B (Standar/Recommended)  
**Estimated Time:** 5-6 hours, 2-3 commits

---

## 1. Overview

Implementasi sistem multi-level admin management yang memungkinkan **Super Admin** untuk membuat, mengedit, menghapus, dan memanage akun admin lain, sementara **Admin Biasa** hanya bisa mengakses konten desa (tidak bisa manage akun).

### Current State
- Sistem admin sederhana: 1 akun (admin@desa.test) bisa login & manage konten
- Tidak ada diferensiasi role atau permission granular
- Tidak ada audit trail untuk admin management

### Target State
- Super Admin: manage admin + manage konten
- Admin Biasa: manage konten saja
- Audit trail otomatis (created_by, updated_by)
- Nonaktifkan akun tanpa hard delete
- Reset password by super admin

---

## 2. Requirements

### Functional Requirements

#### 2.1 Admin Management (Super Admin Only)
- **List Admins**: View daftar semua admin dengan status, last login, dibuat oleh
- **Create Admin**: Form input nama, email, sandi; checkbox "Jadikan Super Admin"
  - Super admin set password langsung (tidak ada link aktivasi email)
  - Auto-set `is_active = true`
  - Auto-track `created_by = auth('admin')->id()`
- **Edit Admin**: Update nama, email, status super admin
  - Email read-only (aman dari duplikat)
  - Auto-track `updated_by`
- **Delete Admin**: Hard delete (tidak soft delete)
- **Reset Password**: Super admin input password baru untuk admin lain
- **Toggle Active**: Nonaktifkan/aktifkan akun
  - Admin tidak bisa nonaktifkan diri sendiri (validasi di controller)

#### 2.2 Permission & Access Control
- Middleware `admin.super` untuk melindungi route admin management
- Admin biasa mendapat 403 Forbidden jika coba akses `/admin/admin`
- Session timeout tetap 1 jam (fitur 17 existing)

#### 2.3 Audit Trail
- `created_by`: auto-track siapa (super admin) yang buat akun
- `updated_by`: auto-track siapa yang edit akun
- Existing admin dipromosi jadi super admin (via migration)
- History accessible via model relationship: `$admin->kreatorAdmin->nama`

#### 2.4 Existing Data Migration
- Admin existing (`admin@desa.test`) auto-set `is_super_admin = true`
- All kolom `is_active = true`
- `created_by` dan `updated_by` tetap `null` (data history tidak tersedia)

### Non-Functional Requirements
- Follow existing code conventions (bahasa Indonesia, naming, structure)
- No breaking changes ke existing routes/middleware
- Testing: feature test untuk semua use case
- Hanya Skenario B (tidak link aktivasi email, tidak role granular multiple)

---

## 3. Database Schema

### 3.1 Migration File
**Filename:** `database/migrations/2026_08_05_000302_add_admin_management_columns_to_admins_table.php`

### 3.2 New Columns di `admins` Table

| Kolom | Type | Default | Notes |
|---|---|---|---|
| `is_super_admin` | BOOLEAN | false | Diferensiasi super admin vs admin biasa |
| `is_active` | BOOLEAN | true | Status akun (nonaktif = tidak bisa login) |
| `created_by` | BIGINT UNSIGNED NULLABLE | null | FK admins.id, nullOnDelete |
| `updated_by` | BIGINT UNSIGNED NULLABLE | null | FK admins.id, nullOnDelete |

### 3.3 Index
```sql
INDEX (is_super_admin, is_active)
```

### 3.4 Foreign Keys
```php
$table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
$table->foreign('updated_by')->references('id')->on('admins')->nullOnDelete();
```

### 3.5 Migration Logic (Up)
```php
// Add columns
Schema::table('admins', function (Blueprint $table) {
    $table->boolean('is_super_admin')->default(false)->after('password');
    $table->boolean('is_active')->default(true)->after('is_super_admin');
    $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
    
    $table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
    $table->foreign('updated_by')->references('id')->on('admins')->nullOnDelete();
    
    $table->index(['is_super_admin', 'is_active']);
});

// Set existing admin as super admin
DB::table('admins')->update(['is_super_admin' => true]);
```

---

## 4. Application Layer

### 4.1 Model: `Admin` (`app/Models/Admin.php`)

**New Relationships:**
```php
public function kreatorAdmin() {
    return $this->belongsTo(Admin::class, 'created_by');
}

public function penyuntingAdmin() {
    return $this->belongsTo(Admin::class, 'updated_by');
}
```

**New Scopes:**
```php
public function scopeSuperAdmin($query) {
    return $query->where('is_super_admin', true);
}

public function scopeAktif($query) {
    return $query->where('is_active', true);
}
```

**New Methods:**
```php
public function isSuperAdmin(): bool {
    return $this->is_super_admin;
}

public function isAktif(): bool {
    return $this->is_active;
}
```

**Model Hooks (Automatic Audit):**
```php
protected static function creating(Admin $admin) {
    // Auto-set created_by saat create
    if (auth()->check() && auth()->guard('admin')) {
        $admin->created_by = auth('admin')->id();
    }
}

protected static function updating(Admin $admin) {
    // Auto-set updated_by saat update
    if (auth()->check() && auth()->guard('admin')) {
        $admin->updated_by = auth('admin')->id();
    }
}
```

### 4.2 Middleware: `VerifyAdminIsSuper`

**File:** `app/Http/Middleware/VerifyAdminIsSuper.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAdminIsSuper {
    public function handle(Request $request, Closure $next) {
        if (!auth('admin')->check() || !auth('admin')->user()->is_super_admin) {
            return abort(403, 'Hanya super admin yang bisa akses halaman ini.');
        }
        return $next($request);
    }
}
```

**Registration** (di `bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias('admin.super', \App\Http\Middleware\VerifyAdminIsSuper::class);
})
```

### 4.3 Requests (Validation)

**File:** `app/Http/Requests/Admin/AdminRequest.php`

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->user()->is_super_admin;
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
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Sandi minimal 8 karakter.',
        ];
    }
}
```

**File:** `app/Http/Requests/Admin/ResetSandiAdminRequest.php`

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResetSandiAdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->user()->is_super_admin;
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

### 4.4 Controller: `AdminController`

**File:** `app/Http/Controllers/Admin/AdminController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

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

---

## 5. Routes

**File:** `routes/web.php` (di dalam middleware `auth:admin`)

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

---

## 6. Views

### 6.1 List Admin: `resources/views/admin/admin/index.blade.php`

Menampilkan:
- Tabel admin dengan kolom: Nama, Email, Type (Super Admin / Admin), Status, Last Login, Dibuat Oleh, Aksi
- Tombol "Tambah Admin Baru"
- Filter dropdown: Semua / Super Admin only / Admin Biasa
- Pagination (15 per page)
- Action buttons per row: Edit, Delete, Aktifkan/Nonaktifkan, Reset Sandi
- Konfirmasi dialog untuk delete & nonaktifkan

### 6.2 Create Admin: `resources/views/admin/admin/create.blade.php`

Form:
- Nama (required)
- Email (required, unique)
- Sandi (required, min 8)
- Checkbox "Jadikan Super Admin" (optional, default unchecked)
- Tombol Simpan & Batal

### 6.3 Edit Admin: `resources/views/admin/admin/edit.blade.php`

Form:
- Nama (editable)
- Email (read-only)
- Checkbox "Jadikan Super Admin" (editable)
- Tombol Simpan & Batal

### 6.4 Reset Password Dialog

Reuse `<x-admin.pusat-dialog />` dengan:
- Modal form reset sandi
- Input: password baru
- Tombol: Reset & Batal
- Triggered dari button di list admin

---

## 7. Testing

**File:** `tests/Feature/Admin/AdminManagementTest.php`

Test cases:
1. **Access Control**
   - Super admin bisa akses halaman `/admin/admin`
   - Admin biasa mendapat 403 saat akses `/admin/admin`

2. **Create Admin**
   - Super admin bisa buat admin baru dengan password
   - Email harus unique
   - Password minimal 8 karakter
   - `created_by` otomatis ter-set

3. **Edit Admin**
   - Super admin bisa ubah nama & status
   - Email read-only (tidak bisa diedit)
   - `updated_by` otomatis ter-set

4. **Reset Password**
   - Super admin bisa reset sandi admin lain
   - Password minimal 8 karakter
   - Admin bisa login dengan password baru

5. **Toggle Active**
   - Super admin bisa nonaktifkan admin
   - Admin nonaktif tidak bisa login
   - Super admin bisa aktifkan kembali
   - Admin tidak bisa nonaktifkan diri sendiri (validasi server)

6. **Delete Admin**
   - Super admin bisa hapus admin
   - Admin terhapus tidak ada di daftar

7. **Audit Trail**
   - `created_by` tercatat
   - `updated_by` tercatat saat edit
   - Bisa query admin via `Admin::find($id)->kreatorAdmin`

---

## 8. Compatibility & Migration

### 8.1 Backward Compatibility
- Existing routes/controllers tidak berubah
- Guard `admin` tetap `admin`
- Middleware session timeout tetap 1 jam

### 8.2 Migration Path
1. Run migration → add columns, set existing admin as super admin
2. Deploy controller + views + routes
3. Test: super admin akses halaman kelola admin
4. Test: admin biasa tidak bisa akses

### 8.3 Data Integrity
- `created_by`/`updated_by` hanya otomatis di akses yang datang dari authenticated session
- Foreign key: `nullOnDelete` (jika super admin dihapus, created_by tetap terbaca)

---

## 9. Implementation Phases

### Phase 1: Database & Model (1 hour)
- [ ] Create migration
- [ ] Update Admin model (relationships, scopes, methods, hooks)

### Phase 2: Controller & Middleware (1.5 hours)
- [ ] Create AdminController
- [ ] Create AdminRequest & ResetSandiAdminRequest
- [ ] Create VerifyAdminIsSuper middleware
- [ ] Register middleware di bootstrap/app.php
- [ ] Add routes

### Phase 3: Views (1.5 hours)
- [ ] Create index.blade.php (list admin)
- [ ] Create create.blade.php (form buat admin)
- [ ] Create edit.blade.php (form edit admin)
- [ ] Add reset password dialog component

### Phase 4: Testing (1 hour)
- [ ] Write feature tests
- [ ] Run test suite
- [ ] Fix bugs if any

### Phase 5: Documentation & Cleanup (0.5 hours)
- [ ] Update CLAUDE.md dengan fitur baru
- [ ] Commit changes

**Total: 5-6 hours**

---

## 10. Acceptance Criteria

✅ Super admin bisa membuat admin baru dengan password  
✅ Super admin bisa edit admin (nama, status super admin)  
✅ Super admin bisa delete admin  
✅ Super admin bisa reset sandi admin lain  
✅ Super admin bisa nonaktifkan/aktifkan admin  
✅ Admin biasa tidak bisa akses halaman kelola admin (403)  
✅ Admin tidak bisa nonaktifkan diri sendiri  
✅ Audit trail: created_by & updated_by otomatis ter-track  
✅ Existing admin (admin@desa.test) promoted to super admin  
✅ All feature tests pass  
✅ CLAUDE.md diupdate dengan fitur baru  

---

## 11. Known Limitations & Future Enhancements

**Saat ini (Skenario B):**
- Tidak ada link aktivasi email (super admin set password langsung)
- Tidak ada tabel audit_logs terpisah (hanya created_by/updated_by di admins)
- Email read-only di edit (aman tapi tidak fleksibel)

**Untuk di-future (Skenario C+):**
- Email activation link (super admin buat akun → kirim link → admin set password)
- Detailed audit logs (track setiap action: login, password reset, etc)
- Multiple roles (admin konten, admin data penduduk, etc)
- Role-based permission system
- Admin activity dashboard

---

## 12. References

- CLAUDE.md: Admin authentication, middleware patterns, route structure
- Existing Controllers: BeritaController, PengurusController (pattern reference)
- Feature 17: Session timeout (1 jam inaktif) — tetap berlaku
- Fitur 01: Existing admin auth guard setup

---

**End of Design Spec**
