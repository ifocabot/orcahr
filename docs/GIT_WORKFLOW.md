# 🔀 Git Workflow & Versioning — OrcaHR

> Aturan Git untuk solo developer. Simpel tapi disiplin.
> Tujuan: histori bersih, rollback mudah, deploy aman.

---

## Branch Strategy

```
main        ← Production-ready, only merge dari develop
│
└── develop ← Integration branch, daily work
    │
    ├── feature/employee-crud     ← Fitur baru
    ├── feature/payroll-engine    ← Fitur baru
    ├── fix/leave-balance-calc    ← Bug fix
    └── refactor/encryption-trait ← Refactoring
```

### Rules

| Rule | Penjelasan |
|---|---|
| **Jangan langsung push ke `main`** | Selalu lewat `develop` dulu |
| **1 branch = 1 fitur/fix** | Jangan campur banyak fitur di 1 branch |
| **Merge ke `develop` sesering mungkin** | Jangan biarkan branch terlalu lama terpisah |
| **Merge `develop` → `main` per fase** | Setelah fase selesai + tested |
| **Tag release di `main`** | `v1.0.0`, `v1.1.0`, dll |

---

## Branch Naming

```
{type}/{scope-singkat}
```

| Type | Kapan | Contoh |
|---|---|---|
| `feature/` | Fitur baru | `feature/employee-crud` |
| `fix/` | Bug fix | `fix/nik-duplicate-check` |
| `refactor/` | Refactoring (behavior tetap) | `refactor/attendance-service` |
| `docs/` | Dokumentasi saja | `docs/api-specs` |
| `hotfix/` | Fix urgent di production | `hotfix/payroll-calc-error` |

---

## Commit Convention

### Format

```
type(scope): pesan singkat

[body opsional]
```

### Types

| Type | Penggunaan | Contoh |
|---|---|---|
| `feat` | Fitur baru | `feat(employee): add create form with encryption` |
| `fix` | Bug fix | `fix(attendance): recalc not triggered on approval` |
| `refactor` | Refactoring | `refactor(payroll): extract formula to service` |
| `test` | Tambah/ubah test | `test(payroll): add calculation unit tests` |
| `docs` | Dokumentasi | `docs: update PROGRESS.md` |
| `chore` | Maintenance | `chore: update laravel to 11.5` |
| `style` | Formatting | `style: run pint` |
| `perf` | Performance | `perf(attendance): add index on daily_attendances` |

### Scope (Gunakan Nama Modul)

```
employee, attendance, leave, payroll, ess, recruitment, project, auth, rbac
```

### Aturan Commit Message

| ✅ Benar | ❌ Salah |
|---|---|
| `feat(employee): add NIK encryption with dual-column` | `update employee` |
| `fix(leave): balance not returned on rejection` | `fix bug` |
| `test(payroll): add formula engine unit tests` | `add tests` |
| `refactor(auth): extract middleware to policy` | `refactoring` |

> **Commit message = jurnal kerja.** Kalau 3 bulan lagi baca `git log`, harus bisa paham apa yang terjadi.

---

## Workflow Harian

```
┌─────────────────────────────────────────────────┐
│  1. git checkout develop                        │
│  2. git pull                                    │
│  3. git checkout -b feature/employee-crud       │
│  4. ... coding ...                              │
│  5. git add -A                                  │
│  6. git commit -m "feat(employee): add form"    │
│  7. ... coding lagi ...                         │
│  8. git commit -m "feat(employee): add service" │
│  9. git checkout develop                        │
│ 10. git merge feature/employee-crud             │
│ 11. git push                                    │
│ 12. git branch -d feature/employee-crud         │
└─────────────────────────────────────────────────┘
```

### Kapan Commit?

| Trigger | Commit |
|---|---|
| Selesai 1 unit kerja kecil | ✅ `feat(employee): add migration` |
| Selesai 1 fitur lengkap | ✅ `feat(employee): complete CRUD with tests` |
| Mau istirahat / berhenti kerja | ✅ Commit WIP: `chore(employee): WIP create form` |
| Fix 1 bug | ✅ `fix(leave): balance not deducted` |
| **JANGAN:** Commit semua perubahan 1 hari sekaligus | ❌ |

> **Prinsip:** Commit kecil, sering, dan deskriptif. Bukan commit raksasa di akhir hari.

---

## Tagging & Releases

### Format

```
v{fase}.{increment}.{patch}
```

| Tag | Artinya | Kapan |
|---|---|---|
| `v1.0.0` | Fase 1 selesai (Foundation + Employee) | Akhir Minggu 4 |
| `v2.0.0` | Fase 2 selesai (Attendance + Leave) | Akhir Minggu 8 |
| `v3.0.0` | Fase 3 selesai (Payroll Engine) | Akhir Minggu 14 |
| `v1.1.0` | Minor improvement di Fase 1 | Kapanpun |
| `v1.0.1` | Hotfix di Fase 1 | Kapanpun |

### Cara Tag

```bash
# Setelah merge develop → main
git checkout main
git merge develop
git tag -a v1.0.0 -m "Fase 1: Foundation + Employee Management"
git push origin main --tags
```

---

## .gitignore (Laravel)

```gitignore
# Laravel
/vendor/
/node_modules/
/.env
/storage/*.key
/public/hot
/public/storage
/storage/app/public/

# IDE
/.idea/
/.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Build
/public/build/

# Testing
.phpunit.result.cache
/coverage/

# Logs
/storage/logs/*.log
```

> ⚠️ **JANGAN PERNAH commit `.env`** — berisi credentials, encryption keys, database passwords.

---

## Proteksi Environment Secrets

| File | Git Status | Isi |
|---|---|---|
| `.env` | **gitignore** ❌ | DB credentials, `ENCRYPTION_KEY`, `HMAC_KEY` |
| `.env.example` | **committed** ✅ | Template tanpa nilai rahasia |
| `.env.testing` | **gitignore** ❌ | Config untuk `php artisan test` |

### .env.example (Template)

```env
APP_NAME=OrcaHR
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orcahr
DB_USERNAME=root
DB_PASSWORD=

ENCRYPTION_KEY=          # Generate: php artisan tinker → Str::random(32)
HMAC_KEY=                # Generate: php artisan tinker → Str::random(32)

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

---

## Emergency: Rollback

### Rollback Commit Terakhir

```bash
# Undo commit terakhir (keep changes)
git reset --soft HEAD~1

# Undo commit terakhir (discard changes)
git reset --hard HEAD~1
```

### Rollback ke Tag/Version

```bash
# Lihat semua tags
git tag -l

# Checkout ke versi tertentu
git checkout v1.0.0

# Rollback main ke versi tertentu
git checkout main
git reset --hard v1.0.0
git push --force-with-lease
```

### Rollback Migration (Laravel)

```bash
# Rollback 1 migration
php artisan migrate:rollback --step=1

# Rollback semua + migrate ulang
php artisan migrate:fresh --seed
```

---

## Checklist Sebelum Merge ke `develop`

- [ ] `php artisan test` → semua pass
- [ ] Tidak ada `dd()`, `dump()`, `var_dump()` di code
- [ ] Commit messages deskriptif
- [ ] Tidak ada credentials di code (pakai `.env`)
- [ ] Migration bisa di-rollback (`down()` method ada)

## Checklist Sebelum Merge ke `main`

- [ ] Semua item di checklist `develop` ✅
- [ ] Semua fitur fase ini sudah tested
- [ ] `PROGRESS.md` sudah updated
- [ ] Tag version sudah disiapkan

---

*Dibuat: 4 Maret 2026*
