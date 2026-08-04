
---

## Copy Prompt Ini ke Chat Baru:

```
KONTEKS: Ini adalah session lanjutan dari Fitur 17 (Kategori Potensi & Fasilitas).

BACA DULU (MANDATORY):
1. docs/TODO-KATEGORI.md (5 min) — Checklist detail dengan kode snippets
2. memory/tahap-kategori-status.md (3 min) — Status & strategy

SITUASI SAAT INI:
- Commit terakhir: 1bf5463 (Fitur 17/3: Infrastructure 80% done)
- 20 tests failing (expected — controllers belum update ke kategori dinamis)
- Database & models sudah selesai, TINGGAL CONNECT KE UI

TUGAS HARI INI (TAHAP 1 COMPLETION):
Selesaikan Fitur 17 Tahap 1 dengan mengikuti TODO-KATEGORI.md Priority 1-5:

PRIORITY 1 — Controllers Update (30 min):
1. app/Http/Controllers/Publik/PotensiController.php
   → Baris 29: Hapus pluck kategori enum, gunakan RefKategoriPotensi relationship
   → EXACT code di TODO-KATEGORI.md baris ~50-60

2. app/Http/Controllers/Publik/FasilitasController.php
   → Update untuk grouping by kategori dinamis
   → EXACT code di TODO-KATEGORI.md baris ~70-90

3. app/Http/Controllers/Admin/PotensiController.php
   → store() & update(): tambah kategori_potensi_id validation
   → EXACT code di TODO-KATEGORI.md baris ~100-120

4. app/Http/Controllers/Admin/FasilitasController.php
   → store() & update(): tambah kategori_fasilitas_id validation
   → Same pattern dengan Potensi (di TODO-KATEGORI.md)

ACTION SETELAH SETIAP FILE UPDATE:
php artisan test --filter=PotensiTest
php artisan test --filter=FasilitasPrestasiTest
→ Tests should pass lebih banyak setelah Priority 1 selesai

PRIORITY 2 — Admin Forms Update (15 min):
1. resources/views/admin/potensi/form.blade.php
   → Ganti enum select dengan dropdown dinamis RefKategoriPotensi
   → EXACT code di TODO-KATEGORI.md baris ~130-140

2. resources/views/admin/fasilitas/form.blade.php
   → Same pattern untuk fasilitas

PRIORITY 3 — Public Views Update (15 min):
1. resources/views/publik/potensi/index.blade.php
   → Bind kategori filter & tampilkan kategori nama

2. resources/views/publik/fasilitas.blade.php
   → Group by kategori dinamis

PRIORITY 4 — Tests Update (20 min):
Jalankan: php artisan test 2>&1 | tail -30
→ Fix yang masih gagal sesuai error message
→ Update assertions di PotensiTest, FasilitasPrestasiTest, HalamanPublikTest
→ Target: 463+ tests passing

PRIORITY 5 — Cleanup (10 min):
1. rm app/Enums/KategoriPotensi.php
2. rm app/Enums/KategoriFasilitas.php
3. Update CLAUDE.md (§4.3 routes, §6 kategori tables, §7 models)

FINAL CHECKLIST:
- [ ] Priority 1 done (controllers) + tests ≥400 passing
- [ ] Priority 2 done (forms)
- [ ] Priority 3 done (views)
- [ ] Priority 4 done (tests) + 463+ passing
- [ ] Priority 5 done (cleanup) + CLAUDE.md updated

WHEN DONE:
1. Run: php artisan test 2>&1 | tail -5
2. Confirm: 463+ tests passing
3. Commit: "Fitur 17 (1/3) — Kategori Potensi & Fasilitas: Complete"
4. Push to main

REFERENCE FILES (DON'T SKIP):
- docs/TODO-KATEGORI.md ← Baca ini pakai detailed code snippets
- memory/tahap-kategori-status.md (di .claude/projects auto-memory) ← Priority & strategy
- Commit 1bf5463 (lihat files yang dibuat)

JANGAN KELUAR DARI PLAN:
✅ Hanya update file yang disebutkan di Priority 1-5
❌ Jangan buat file/features baru
❌ Jangan refactor yang tidak perlu
✅ Fokus fix broken tests
✅ One commit at the end setelah 463+ tests passing

ESTIMATED TIME: ~90 min total
- Controllers: 30 min
- Forms: 15 min
- Views: 15 min
- Tests: 20 min
- Cleanup: 10 min

Mulai dari Priority 1 sekarang juga!
```

## Testing Checklist untuk Session Berikutnya

Setelah setiap Priority, jalankan:

```bash
# After Priority 1 (Controllers):
php artisan test --filter=PotensiTest 2>&1 | tail -5
php artisan test --filter=FasilitasPrestasiTest 2>&1 | tail -5
# Target: ≥3-4 tests passing (dari 6+)

# After Priority 4 (Tests):
php artisan test 2>&1 | tail -5
# Target: 463+ tests, 1290+ assertions

# Before Final Commit:
php artisan test
# Target: Semua tests passing, no failures
```

---

## Quick Reference — File Locations

| File | Purpose |
|------|---------|
| `docs/TODO-KATEGORI.md` | 📋 Detailed checklist + exact code snippets |
| `PROMPT-SESSION-BERIKUTNYA.md` (file ini) | 🚀 Prompt untuk copy-paste |
| `database/migrations/2026_08_05_000227_...` | 🗄️ Database schema |
| `app/Models/RefKategoriPotensi.php` | 🏗️ Model dengan relationships |
| `app/Models/RefKategoriFasilitas.php` | 🏗️ Model dengan relationships |
| `app/Http/Controllers/Admin/RefKategoriPotensiController.php` | ✅ Already done, reference this |
| `routes/web.php` lines 215-228 | ✅ Routes already defined |

---

## Timeline Expected

| Phase | Time | Status |
|-------|------|--------|
| Priority 1 (Controllers) | 30 min | 📋 In session |
| Priority 2 (Forms) | 15 min | 📋 In session |
| Priority 3 (Views) | 15 min | 📋 In session |
| Priority 4 (Tests) | 20 min | 📋 In session |
| Priority 5 (Cleanup) | 10 min | 📋 In session |
| **TOTAL** | **~90 min** | ⏱️ One session |

---

## After This Session Completes

Next two features (also 3-commit each):
1. **Tahap 2:** Pekerjaan (Template Locked) — ~1 jam
2. **Tahap 3:** Session Timeout 1 jam — ~1 jam

Prompt untuk Tahap 2 & 3 akan dibuat setelah Tahap 1 selesai.

---

## ⚠️ Things to Remember

✅ Read `docs/TODO-KATEGORI.md` FIRST (5 min, akan save time later)
✅ Test after each Priority
✅ Use EXACT code snippets dari TODO-KATEGORI.md
❌ Don't modify anything outside Priority 1-5 scope
❌ Don't create new files/features
✅ One final commit when 463+ tests pass
