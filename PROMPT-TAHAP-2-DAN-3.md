# 🚀 PROMPT untuk Tahap 2 & 3 — Copy & Paste Langsung

---

## 📋 Context

Tahap 1 (Kategori Potensi & Fasilitas) sudah **100% COMPLETE** ✅

Sekarang lanjut Tahap 2 & 3 untuk menyelesaikan **Fitur 17 (3 tahap, 3 commits)**

---

## 🎯 Tahap 2: Pekerjaan (Template Locked) — ~80 min

**File:** `docs/TODO-PEKERJAAN-TEMPLATE.md` ← Baca ini dulu!

**Task:** Admin bisa add custom pekerjaan, tapi pekerjaan default template tidak bisa dihapus

**Priority 1-7:**
1. Add `is_template` column migration
2. Update controller destroy() untuk protect template
3. Update admin view (disable delete untuk template)
4. Create tests (RefPekerjaanTemplateTest)
5. Update RefPekerjaan model
6. Update seeder is_template
7. Create/update factory

**Testing:**
```bash
php artisan test tests/Feature/Konten/RefPekerjaanTemplateTest.php
php artisan test 2>&1 | tail -5  # Target: 462+ tests passing
```

**Commit:**
```
Fitur 17 (2/3) — Pekerjaan: Template Locked + Custom Admin
```

---

## 🎯 Tahap 3: Session Timeout 1 Jam — ~80 min

**File:** `docs/TODO-SESSION-TIMEOUT.md` ← Baca ini dulu!

**Task:** Auto logout admin setelah 60 menit inaktif, AJAX return 401

**Priority 1-7:**
1. Update config/session.php lifetime = 60 menit
2. Create EnsureAdminSessionNotExpired middleware
3. Register middleware di bootstrap/app.php
4. Update LoginController masuk() (set last_activity)
5. Create tests (SessionTimeoutTest)
6. Update admin login view dengan pesan error
7. Update CLAUDE.md dokumentasi

**Testing:**
```bash
php artisan test tests/Feature/Admin/SessionTimeoutTest.php
php artisan test 2>&1 | tail -5  # Target: 462+ tests passing
```

**Commit:**
```
Fitur 17 (3/3) — Session Timeout: 1 Jam Auto Logout + AJAX 401
```

---

## 📋 Full Workflow

### Session Setup (Do This First)
```bash
# Verify Tahap 1 is complete
php artisan test 2>&1 | tail -5
# Should show: 462+ tests passing

# Check latest commit
git log --oneline -1
# Should show: "Fitur 17 (1/3) — Kategori Potensi & Fasilitas: Complete"
```

### Tahap 2 Workflow (80 min)
```bash
# 1. Read docs/TODO-PEKERJAAN-TEMPLATE.md (5 min)

# 2. Execute Priority 1-7 (~60 min)

# 3. Test after each priority
php artisan test --filter=RefPekerjaanTemplateTest

# 4. Verify all tests passing
php artisan test 2>&1 | tail -5

# 5. Commit
git add -A
git commit -m "Fitur 17 (2/3) — Pekerjaan: Template Locked + Custom Admin"
```

### Tahap 3 Workflow (80 min)
```bash
# 1. Read docs/TODO-SESSION-TIMEOUT.md (5 min)

# 2. Execute Priority 1-7 (~60 min)

# 3. Test after each priority
php artisan test --filter=SessionTimeoutTest

# 4. Verify all tests passing
php artisan test 2>&1 | tail -5

# 5. Commit
git add -A
git commit -m "Fitur 17 (3/3) — Session Timeout: 1 Jam Auto Logout + AJAX 401"
```

### Final Verification
```bash
# All 3 tahaps complete
php artisan test 2>&1 | tail -5
# Target: 462+ tests passing

# Check all 3 commits
git log --oneline -3
# Should show:
# Commit 3: Fitur 17 (3/3) — Session Timeout...
# Commit 2: Fitur 17 (2/3) — Pekerjaan...
# Commit 1: Fitur 17 (1/3) — Kategori... (atau earlier)
```

---

## 📚 Reference Files

| File | Purpose |
|------|---------|
| `docs/TODO-PEKERJAAN-TEMPLATE.md` | Tahap 2 — Exact checklist + code snippets |
| `docs/TODO-SESSION-TIMEOUT.md` | Tahap 3 — Exact checklist + code snippets |
| `docs/TODO-KATEGORI.md` | Tahap 1 (reference, already complete) |

---

## ⚠️ Key Points

✅ Read TODO file FIRST untuk setiap tahap (jangan skip!)  
✅ Follow Priority 1-7 exactly (urutan penting)  
✅ Test after each priority  
✅ One commit per tahap (2 new commits total)  
✅ Target: 462+ tests passing after each tahap  
✅ CLAUDE.md updated untuk new rules/middleware  

❌ Don't skip testing  
❌ Don't combine tahaps (satu commit per tahap)  
❌ Don't refactor outside of scope  

---

## 🎯 Expected Outcome

**Tahap 2 Complete:**
- ✅ Default pekerjaan protected (is_template = true)
- ✅ Custom pekerjaan deletable
- ✅ Admin can add custom pekerjaan
- ✅ Tests passing (462+)
- ✅ 1 new migration, 1 new test file
- ✅ 1 commit

**Tahap 3 Complete:**
- ✅ Session timeout 60 menit
- ✅ Auto logout inactive admin
- ✅ AJAX return 401 on timeout
- ✅ Tests passing (462+)
- ✅ 1 new middleware, 1 new test file
- ✅ 1 commit

**Fitur 17 Complete:**
- ✅ 3 commits total (kategori, pekerjaan, session timeout)
- ✅ 462+ tests passing
- ✅ CLAUDE.md updated
- ✅ Ready to merge main

---

## ⏱️ Timeline

| Phase | Time | Total |
|-------|------|-------|
| Tahap 2 Setup | 5 min | 5 min |
| Tahap 2 Implementation | 60 min | 65 min |
| Tahap 2 Testing | 15 min | 80 min |
| Tahap 3 Setup | 5 min | 85 min |
| Tahap 3 Implementation | 60 min | 145 min |
| Tahap 3 Testing | 15 min | 160 min |
| **TOTAL** | | **~160 min** (2.5 hours) |

---

## 🚀 Let's Go!

Start dengan Tahap 2 sekarang juga!

1. Read `docs/TODO-PEKERJAAN-TEMPLATE.md`
2. Follow Priority 1-7
3. Test & commit
4. Lanjut Tahap 3
5. 🎉 Fitur 17 Complete!
