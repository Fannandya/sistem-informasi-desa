# TODO: Pekerjaan (Template Locked) — Tahap 2 Fitur 17

**Status:** Ready to Start  
**Estimated Time:** ~1 jam  
**Scope:** Protect default pekerjaan, allow admin to add custom pekerjaan  
**Expected Outcome:** 462+ tests passing, 1 new commit

---

## 📋 Checklist

### Priority 1: Add `is_template` Column (15 min)

**Migration:** `database/migrations/2026_08_05_000228_add_is_template_to_ref_pekerjaan.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_pekerjaan', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('nama');
        });

        // Mark default pekerjaan as template
        DB::update("UPDATE ref_pekerjaan SET is_template = 1 WHERE id <= 10");
    }

    public function down(): void
    {
        Schema::table('ref_pekerjaan', function (Blueprint $table) {
            $table->dropColumn('is_template');
        });
    }
};
```

**After migration:**
```bash
php artisan migrate
# Verify: SELECT id, nama, is_template FROM ref_pekerjaan;
```

---

### Priority 2: Update RefPekerjaanController (20 min)

**File:** `app/Http/Controllers/Admin/RefPekerjaanController.php`

**Modify destroy() method:**
```php
public function destroy(RefPekerjaan $refPekerjaan)
{
    // ✅ NEW: Protect template pekerjaan
    if ($refPekerjaan->is_template) {
        return redirect(route('admin.pekerjaan.index'))
            ->withErrors(['nama' => 'Pekerjaan template tidak dapat dihapus.']);
    }

    // ❌ OLD check (jika ada):
    // if ($refPekerjaan->penduduk()->exists()) { ... }
    // ✅ REPLACE dengan:
    if ($refPekerjaan->penduduk()->exists()) {
        return redirect(route('admin.pekerjaan.index'))
            ->withErrors(['nama' => 'Pekerjaan masih dipakai. Hapus warga terlebih dahulu.']);
    }

    $refPekerjaan->delete();

    return redirect(route('admin.pekerjaan.index'))
        ->with('status', 'Pekerjaan dihapus.');
}
```

---

### Priority 3: Update Admin View (15 min)

**File:** `resources/views/admin/pekerjaan/index.blade.php`

**Find table row with delete button, modify:**
```blade
<td class="px-4 py-3 text-right">
    @if ($item->is_template)
        {{-- Template pekerjaan: disable delete --}}
        <span class="text-xs text-on-surface-variant font-medium">Template (tidak bisa dihapus)</span>
    @else
        {{-- Custom pekerjaan: allow delete --}}
        <form method="POST" action="{{ route('admin.pekerjaan.destroy', $item) }}"
            class="inline"
            data-konfirmasi
            data-judul="Hapus pekerjaan?"
            data-pesan="Pekerjaan akan dihapus dari sistem."
            data-tombol="Hapus"
            data-bahaya>
            @csrf
            @method('DELETE')
            <button type="submit" class="text-error hover:text-error/80 font-medium">
                Hapus
            </button>
        </form>
    @endif
</td>
```

---

### Priority 4: Create Tests (15 min)

**File:** `tests/Feature/Konten/RefPekerjaanTemplateTest.php`

```php
<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\RefPekerjaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefPekerjaanTemplateTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    public function test_template_pekerjaan_tidak_bisa_dihapus(): void
    {
        $template = RefPekerjaan::factory()->create(['is_template' => true]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $template));

        $response->assertSessionHasErrors(['nama']);
        $this->assertDatabaseHas('ref_pekerjaan', ['id' => $template->id]);
    }

    public function test_custom_pekerjaan_bisa_dihapus(): void
    {
        $custom = RefPekerjaan::factory()->create(['is_template' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $custom));

        $response->assertRedirectToRoute('admin.pekerjaan.index');
        $this->assertDatabaseMissing('ref_pekerjaan', ['id' => $custom->id]);
    }

    public function test_pekerjaan_dipakai_tidak_bisa_dihapus(): void
    {
        $pekerjaan = RefPekerjaan::factory()->create(['is_template' => false]);
        \App\Models\Penduduk::factory()->create(['pekerjaan_id' => $pekerjaan->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $pekerjaan));

        $response->assertSessionHasErrors(['nama']);
        $this->assertDatabaseHas('ref_pekerjaan', ['id' => $pekerjaan->id]);
    }

    public function test_template_pekerjaan_ditampilkan_di_dropdown(): void
    {
        RefPekerjaan::factory()->create(['nama' => 'Petani', 'is_template' => true]);
        RefPekerjaan::factory()->create(['nama' => 'Custom Job', 'is_template' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.create'));

        $response->assertOk()
            ->assertSee('Petani')
            ->assertSee('Custom Job');
    }
}
```

---

### Priority 5: Update RefPekerjaan Model (5 min)

**File:** `app/Models/RefPekerjaan.php`

**Add if not present:**
```php
protected $fillable = ['nama', 'is_template'];

protected $casts = [
    'is_template' => 'boolean',
];

public function scopeTemplate($query)
{
    return $query->where('is_template', true);
}

public function scopeCustom($query)
{
    return $query->where('is_template', false);
}
```

---

### Priority 6: Update Seeder (5 min)

**File:** `database/seeders/RefPekerjaanSeeder.php`

**Add `is_template` ke create:**
```php
foreach ($pekerjaan as $index => $nama) {
    RefPekerjaan::create([
        'nama' => $nama,
        'is_template' => true,  // ← ADD THIS
    ]);
}
```

---

### Priority 7: Update Factory (5 min)

**File:** `database/factories/RefPekerjaanFactory.php` (jika ada, atau buat baru)

```php
<?php

namespace Database\Factories;

use App\Models\RefPekerjaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefPekerjaanFactory extends Factory
{
    protected $model = RefPekerjaan::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->jobTitle(),
            'is_template' => fake()->boolean(20), // 20% chance template
        ];
    }

    public function template(): self
    {
        return $this->state(['is_template' => true]);
    }

    public function custom(): self
    {
        return $this->state(['is_template' => false]);
    }
}
```

---

## 🧪 Testing

```bash
# Run new tests
php artisan test tests/Feature/Konten/RefPekerjaanTemplateTest.php

# Run all tests
php artisan test 2>&1 | tail -5
# Target: 462+ tests passing (same as Tahap 1)
```

---

## 📝 Commit Message

```
Fitur 17 (2/3) — Pekerjaan: Template Locked + Custom Admin

- Add is_template column ke ref_pekerjaan table
- Mark default pekerjaan sebagai template (tidak bisa dihapus)
- Admin bisa add custom pekerjaan & delete hanya custom
- Update controller destroy() untuk protect template
- Update admin view: disable delete button untuk template
- Add RefPekerjaanTemplateTest (4 tests)
- Update RefPekerjaan model dengan scopes & casts
- Update seeder & factory untuk is_template

Status: 462+ tests passing, 1 new migration, 1 new test file
```

---

## 📊 Metrics

| Task | Time | Status |
|------|------|--------|
| Priority 1 (Migration) | 15 min | ⏱️ |
| Priority 2 (Controller) | 20 min | ⏱️ |
| Priority 3 (View) | 15 min | ⏱️ |
| Priority 4 (Tests) | 15 min | ⏱️ |
| Priority 5-7 (Model/Seeder/Factory) | 15 min | ⏱️ |
| **TOTAL** | **~80 min** | ⏱️ |

---

## ✅ Final Checklist

- [ ] Migration created & run
- [ ] Controller destroy() updated
- [ ] Admin view delete button modified
- [ ] RefPekerjaanTemplateTest created (4 tests)
- [ ] RefPekerjaan model updated
- [ ] Seeder updated
- [ ] Factory created/updated
- [ ] All tests passing (462+)
- [ ] CLAUDE.md updated (if routes changed)
- [ ] Commit: "Fitur 17 (2/3) — Pekerjaan: Template Locked..."
- [ ] Push to main
