# TODO: Kategori Potensi & Fasilitas — Completion Checklist

**Status Commit:** `1ff33fc` (Fitur 16 — Carousel) + Tahap 1 Kategori (WIP)  
**Progress:** 80% — Struktur DB & Model selesai, tinggal update Controllers & Views  
**Test Status:** 20 test gagal (file yang query kolom `kategori` lama)

---

## 🔴 URGENT: Files yang WAJIB di-Update agar Tests Lulus

### 1️⃣ Controllers (Publik & Admin)

#### `app/Http/Controllers/Publik/PotensiController.php`
**Baris 29** — HAPUS query enum kategori:
```php
// ❌ OLD (baris 29):
$kategori = PotensiDesa::query()->tampil()->pluck('total', 'kategori')
    ->mapWithKeys(fn($total, $kat) => [KategoriPotensi::from($kat)->label() => $total])
    ->sortByDesc(fn($v) => $v)
    ->take(6);

// ✅ NEW:
$kategori = RefKategoriPotensi::urut()
    ->withCount(['potensi' => fn($q) => $q->tampil()])
    ->get()
    ->pluck('potensi_count', 'nama')
    ->sortByDesc(fn($v) => $v)
    ->take(6);
```

**Baris ~22-27** — Update view pass:
```php
// Tambah import:
use App\Models\RefKategoriPotensi;

// Dalam return:
'kategoriFilter' => RefKategoriPotensi::urut()->get(), // untuk form filter
```

---

#### `app/Http/Controllers/Admin/PotensiController.php`
**store() & update() methods:**
```php
// Tambah ke validasi:
$validasi = $request->validate([
    'nama' => 'required|...',
    'kategori_potensi_id' => 'required|exists:ref_kategori_potensi,id', // ← ADD THIS
    'deskripsi_singkat' => '...',
    // ...
]);

// Di PotensiDesa::create/update:
'kategori_potensi_id' => $validasi['kategori_potensi_id'], // ← CHANGE FROM: 'kategori' => ...
```

---

#### `app/Http/Controllers/Publik/FasilitasController.php` (Invokable)
**Update untuk grouping by kategori:**
```php
// Tambah import:
use App\Models\RefKategoriFasilitas;

public function __invoke(): View
{
    // ✅ NEW: Group by kategori dinamis
    $byKategori = FasilitasUmum::tampil()
        ->with('kategori')
        ->get()
        ->groupBy(fn($f) => $f->kategori?->nama ?? 'Lainnya');

    return view('publik.fasilitas', [
        'byKategori' => $byKategori,
        'desa' => $desa, // existing
    ]);
}
```

---

#### `app/Http/Controllers/Admin/FasilitasController.php`
**store() & update() methods — sama pattern seperti Potensi:**
```php
'kategori_fasilitas_id' => 'required|exists:ref_kategori_fasilitas,id', // ← ADD
// dan:
'kategori_fasilitas_id' => $validasi['kategori_fasilitas_id'], // ← CHANGE
```

---

### 2️⃣ Admin Form Views (Dropdown Dinamis)

#### `resources/views/admin/potensi/form.blade.php`
**Cari baris dengan enum kategori, ganti dengan:**
```blade
<x-admin.select 
    name="kategori_potensi_id" 
    label="Kategori Potensi" 
    required
    :value="$potensi->kategori_potensi_id"
    :pilihan="App\Models\RefKategoriPotensi::urut()->pluck('nama', 'id')" />
```

---

#### `resources/views/admin/fasilitas/form.blade.php`
**Sama pattern:**
```blade
<x-admin.select 
    name="kategori_fasilitas_id" 
    label="Kategori Fasilitas" 
    required
    :value="$fasilitas->kategori_fasilitas_id"
    :pilihan="App\Models\RefKategoriFasilitas::urut()->pluck('nama', 'id')" />
```

---

### 3️⃣ Public Views (Tampilkan Nama Kategori)

#### `resources/views/publik/potensi/index.blade.php`
**Di template yang menampilkan kategori filter:**
```blade
@forelse ($kategoriFilter as $kat)
    <a href="?kategori={{ $kat->id }}" ...>
        {{ $kat->nama }}
    </a>
@endforelse
```

---

#### `resources/views/publik/fasilitas.blade.php`
**Group layout:**
```blade
@forelse ($byKategori as $kategori => $items)
    <h2>{{ $kategori }}</h2>
    <div class="grid...">
        @foreach ($items as $fasilitas)
            <div>{{ $fasilitas->nama }}</div>
        @endforeach
    </div>
@endforelse
```

---

### 4️⃣ Update Tests

**Delete atau update tests yang query `kategori` lama:**
- `tests/Feature/Konten/PotensiTest.php` — 6 tests gagal
- `tests/Feature/Konten/FasilitasPrestasiTest.php` — 4 tests gagal
- `tests/Feature/Publik/HalamanPublikTest.php` — 4 tests gagal

**Buat tests baru untuk kategori CRUD:**
- `tests/Feature/Konten/RefKategoriPotensiTest.php`
- `tests/Feature/Konten/RefKategoriFasilitasTest.php`

---

## 🟢 Cleanup (Setelah Semua Tests Lulus)

```bash
# Hapus enum yang sudah tidak dipakai:
rm app/Enums/KategoriPotensi.php
rm app/Enums/KategoriFasilitas.php

# Cari & hapus import yang masih mengacu ke enum:
grep -r "KategoriPotensi\|KategoriFasilitas" app/ tests/

# Update CLAUDE.md:
# - Hapus KategoriPotensi & KategoriFasilitas dari app/Enums/ (§7)
# - Tambah RefKategoriPotensi & RefKategoriFasilitas di Models (§7)
# - Dokumentasi routes kategori (§4.3)
```

---

## 📋 Checklist untuk Session Berikutnya

```
TAHAP 1 COMPLETION CHECKLIST:

CONTROLLERS:
- [ ] Publik\PotensiController — hapus pluck kategori, gunakan RefKategoriPotensi
- [ ] Publik\FasilitasController — update untuk grouping & relationship
- [ ] Admin\PotensiController — tambah kategori_potensi_id validasi & store
- [ ] Admin\FasilitasController — tambah kategori_fasilitas_id validasi & store

VIEWS:
- [ ] admin/potensi/form — dropdown kategori dinamis
- [ ] admin/fasilitas/form — dropdown kategori dinamis
- [ ] publik/potensi/index — tampilkan kategori nama & filter
- [ ] publik/fasilitas — group by kategori nama

TESTS:
- [ ] Perbaiki PotensiTest (6 tests)
- [ ] Perbaiki FasilitasPrestasiTest (4 tests)
- [ ] Perbaiki HalamanPublikTest (4 tests)
- [ ] Buat RefKategoriPotensiTest (CRUD + geser)
- [ ] Buat RefKategoriFasilitasTest (CRUD + geser)
- [ ] php artisan test — target: 463+ passing

CLEANUP:
- [ ] Hapus KategoriPotensi enum
- [ ] Hapus KategoriFasilitas enum
- [ ] Update CLAUDE.md
- [ ] Remove grep traces

FINAL:
- [ ] Jalankan: php artisan test 2>&1 | tail -5
- [ ] Commit: "Fitur 17 (1/3) — Kategori Potensi & Fasilitas: Complete"
- [ ] Push ke main
```

---

## 🚀 Quick Start (Session Berikutnya)

1. **Read this file** ← Kamu di sini
2. **Check current state:**
   ```bash
   git log --oneline -3
   php artisan test --filter=PotensiTest 2>&1 | head -20
   ```
3. **Follow checklist dari atas ke bawah**
4. **After each file update, run test:**
   ```bash
   php artisan test --filter=PotensiTest
   ```
5. **Commit when all tests pass**

---

## 📌 Key Points

- ✅ Database migrations & seeders sudah selesai
- ✅ Models & controllers untuk **manage kategori** sudah selesai
- ❌ Controllers untuk **gunakan kategori** masih perlu update
- ❌ Views perlu bind ke kategori dinamis
- ❌ Tests perlu update untuk kategori dinamis

**File sudah punya:** 
- `RefKategoriPotensi` model ✅
- `RefKategoriFasilitas` model ✅
- Routes untuk manage kategori ✅
- Admin views untuk CRUD kategori ✅

**File yang perlu update:**
- Potensi & Fasilitas controllers
- Potensi & Fasilitas forms
- Potensi & Fasilitas displays
- Existing tests

---

**Estimated time untuk completion:** 1-2 jam (2-3 files per jam)  
**Next stage after this:** Tahap 2 — Pekerjaan (Template Locked)
