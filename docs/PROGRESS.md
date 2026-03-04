# 📊 PROGRESS.md — OrcaHR

> Log progress development. Update setiap akhir sesi.
> AI agent: baca file ini untuk tahu **dimana kita berhenti**.

---

## Status Overview

| Item | Status |
|---|---|
| **Fase saat ini** | ✅ Fase 1 SELESAI — Fase 2 Attendance (Next) |
| **Minggu ke** | 3 |
| **Last session** | 4 Maret 2026 |
| **Next action** | Fase 2: Attendance Module (Shift Management) |
| **Git HEAD** | `v1.0.0` — master |

---

## Dokumentasi (Pre-Development)

- [x] PROJECT_INTENT.md
- [x] PROJECT_BLUEPRINT.md (7 fase, 32 minggu)
- [x] ARCHITECTURE_PRINCIPLES.md (6 prinsip)
- [x] SECURITY_BLUEPRINT.md (enkripsi, RBAC, ISO)
- [x] MODULE_SPECIFICATIONS.md (10 modul)
- [x] DATABASE_SCHEMA.md (46 tabel, ERD)
- [x] API_SPECIFICATIONS.md (142 endpoints, referensi Vue)
- [x] FLOWCHARTS.md (9 business flows)
- [x] DEV_STANDARDS.md (Blade, Service Layer, Git + **UI Patterns: Drawer + SweetAlert2**)
- [x] TESTING_STRATEGY.md (test priority, examples)
- [x] GIT_WORKFLOW.md (branch, commit, versioning, rollback)
- [x] CODEBASE.md (AI context file)
- [x] PROGRESS.md (this file)

---

## Fase 1: Foundation + Employee Management (Minggu 1-4)

### Minggu 1: Foundation ✅

**Hari 1-2: Project Setup**
- [x] Laravel 12 fresh install + Breeze + Spatie Permission
- [x] Breeze: keep auth controllers + routes, views custom sendiri
- [x] Spatie Permission: 5 roles (super-admin, hr-admin, payroll-admin, dept-head, employee)
- [x] Base layout: sidebar dark, custom Blade components (x-layouts.app, x-layouts.auth)

**Hari 3-4: Security Foundation**
- [x] Encryption helpers (`encrypt_field`, `decrypt_field`, `hmac_hash`) — AES-256-CBC + HMAC-SHA256
- [x] Audit log system (Auditable trait — auto-log created/updated/deleted, mask encrypted)
- [x] AuditLog model (HasUlids, immutable, polymorphic)

**Hari 5: Hardening**
- [x] RBAC middleware + Blade directives (`@can`, `@role`) via Spatie
- [x] Custom login view (glassmorphism), dashboard view (stats cards)
- [x] Tailwind v4 setup fix, Vite build clean
- [x] ✅ COMMIT: `feat(foundation): auth + rbac + encryption + base UI`
- [x] ✅ PUSH: `github.com/ifocabot/orcahr` (branch: master)

---

### Minggu 2: Employee Management — Organisation ✅

**Settings CRUD (Drawer UI Pattern)**
- [x] Migration: `job_levels`, `departments`, `positions`
- [x] Models: JobLevel, Department (self-ref parent), Position — HasUlids + Auditable
- [x] Services: JobLevelService, DepartmentService, PositionService (with guards)
- [x] Controllers: Settings/{JobLevel,Department,Position}Controller
- [x] Views: index (drawer-based CRUD, no /create /edit pages)
- [x] ✅ COMMIT: `feat(settings): job-level + department + position CRUD`

**UI Pattern: Drawer + SweetAlert2**
- [x] `x-drawer` reusable Blade component (ESC, backdrop, 5 sizes)
- [x] `window.toast()` — SweetAlert2 toast helper
- [x] `window.confirmDelete()` — SweetAlert2 confirm modal
- [x] Install SweetAlert2 via npm
- [x] Routes: `->except(['create','edit'])` untuk settings resources
- [x] ✅ COMMIT: `feat(ui): drawer + sweetalert2 pattern for settings CRUD`

---

### Minggu 2-3: Employee Core (In Progress)

**Employee Create (vertical slice) ✅**
- [x] Migrations: `employees` (NIK dual-column: encrypted + HMAC hash), `employments` (effective-dated), `bank_accounts`, `employee_bpjs`
- [x] `Encryptable` trait — auto-encrypt/decrypt getAttribute/setAttribute
- [x] Models: Employee (SoftDeletes, currentEmployment), Employment, BankAccount, EmployeeBpjs
- [x] EmployeeService: `generateEmployeeNumber()` (RKS-YYYY-NNNN), `isNikTaken()` (HMAC, no decrypt), `create()` DB::transaction, `update()` effective-dated history
- [x] EmployeeController: split validation (personal/employment/bank/bpjs), NIK uniqueness
- [x] Views: `employees/index.blade.php` + `employees/create.blade.php` (4 tabs: Info Pribadi, Info Pekerjaan, Rekening Bank, BPJS)
- [x] Permissions: `create/edit/delete-employees`, `view-employment-history`, `manage-bank-accounts`, `manage-bpjs`
- [x] RolePermissionSeeder: diupdate dengan granular employee permissions
- [x] ✅ COMMIT: `feat(employee): Employee CRUD - migrations, models, service, controller, views`

**Employee Show + Edit (Slice 5) ✅**
- [x] `employees/show.blade.php` — detail view (data pribadi, employment, bank, BPJS, dokumen)
- [x] `employees/edit.blade.php` — edit form (tabs, sama dengan create)
- [x] ✅ COMMIT: `feat(employee): show + edit views`

**Employee Documents (Slice 6) ✅**
- [x] Migration: `employee_documents` (ULID PK, type enum, original_name, file_path, expires_at, notes)
- [x] Model `EmployeeDocument` (HasUlids, Auditable, typeLabel, isExpired, isExpiringSoon)
- [x] `DocumentService` (upload private disk, streamDownload)
- [x] `EmployeeDocumentController` (store, download, destroy)
- [x] View partial `employees/partials/documents.blade.php` (tabel, drawer upload, expired warning, SweetAlert delete)
- [x] Route nested docs: POST/GET/DELETE `/employees/{employee}/documents`
- [x] ✅ COMMIT: `feat(employee): document upload + download`

**Policies + Testing (Slice 7) ✅**
- [x] `EmployeePolicy` (viewAny, view, create, update, delete, viewSensitiveData)
- [x] `EncryptionHelperTest` — 7 unit tests (roundtrip, HMAC, null)
- [x] `EmployeeCrudTest` — 9 feature tests (CRUD + NIK unique)
- [x] `EmployeeRbacTest` — 9 feature tests (guest, hr-admin, payroll, dept-head)
- [x] ✅ 25/25 tests passed (46 assertions)
- [x] ✅ COMMIT: `feat: EmployeePolicy + Feature Tests (Fase 1 complete)`

---

### Minggu 4: Hardening + Demo ✅

- [x] Seeder: JobLevel, Department, Position, demo employee (Andi Wijaya)
- [x] Bug fixes: RolePermissionSeeder (manage-employees → granular), Controller.php (AuthorizesRequests)
- [x] ✅ Tag: `v1.0.0` — Fase 1 selesai!

**🎯 Milestone:** Admin bisa manage data karyawan lengkap + policy + tests. **Demo-able! ✅**

---

## Fase 2: Attendance + Leave (Minggu 5-8)

> Detail breakdown saat mulai fase ini.

- [ ] Shift management
- [ ] Clock in/out
- [ ] Daily attendance engine (recalculation)
- [ ] Attendance correction + approval
- [ ] Overtime request + approval
- [ ] Leave types + policy
- [ ] Leave balance
- [ ] Leave request + approval
- [ ] Holiday calendar

---

## Fase 3-7

> Detail breakdown dibuat per fase saat mulai.

---

## Decisions Log

| Tanggal | Keputusan | Alasan |
|---|---|---|
| 4 Mar 2026 | Vue → Blade + Alpine | Belajar Vue setelah produk jadi, bukan sambil build |
| 4 Mar 2026 | Hapus modul Procurement | Tidak dibutuhkan untuk HRIS ini |
| 4 Mar 2026 | Service Layer pattern | Investasi untuk migrasi API + Vue nanti |
| 4 Mar 2026 | Laravel 12 (bukan 11) | Latest version, PHP 8.2+ |
| 4 Mar 2026 | ULID (bukan auto-increment) | Non-guessable IDs, sortable, secure |
| 4 Mar 2026 | Breeze auth (logic only) | Auth controllers dari Breeze, views custom sendiri |
| 4 Mar 2026 | Vertical Slice workflow | Fitur lengkap end-to-end, bukan layer-by-layer |
| 4 Mar 2026 | Spatie migration: char(26) untuk model_id | ULID bukan bigint, pivot table harus char(26) |
| 4 Mar 2026 | Tailwind v4 via @tailwindcss/vite | Hapus dari postcss.config.js, pakai Vite plugin |
| 4 Mar 2026 | Blade layout di components/layouts/ | x-layouts.app butuh resources/views/components/layouts/ |
| 4 Mar 2026 | QUEUE_CONNECTION=sync (local) | Redis nanti di production — zero code change needed |
| 4 Mar 2026 | Drawer + SweetAlert2 pattern | Semua CRUD forms via drawer (bukan /create page), delete via SweetAlert2 |
| 4 Mar 2026 | Encryptable trait (dual-column: encrypted + hash) | NIK uniqueness check via HMAC tanpa decrypt semua data |

---

## Known Issues / Backlog

| # | Issue | Priority | Status |
|---|---|---|---|
| 1 | `employees/show.blade.php` belum dibuat | High | ✅ Done (Session #5) |
| 2 | `employees/edit.blade.php` belum dibuat | High | ✅ Done (Session #5) |
| 3 | EmployeePolicy belum ada | Medium | ✅ Done (Session #6) |
| 4 | Employee index: pagination belum ada (semua dimuat sekaligus) | Low | Backlog — Fase 2 nanti |
| 5 | `RolePermissionSeeder`: `manage-employees` permission invalid | High | ✅ Fixed (Session #6) |
| 6 | `Controller.php` tidak punya `AuthorizesRequests` trait | High | ✅ Fixed (Session #6) |

---

## Session Log

### Session #5 — 4 Maret 2026
**Goal:** Employee Show + Edit views + Seeders demo data
**Hasil:**
- `employees/show.blade.php`: detail view (info pribadi, data sensitif RBAC-gated, riwayat jabatan, bank, BPJS)
- `employees/edit.blade.php`: edit form 2-tab (Info Pribadi, Info Pekerjaan)
- Seeders: `JobLevelSeeder`, `DepartmentSeeder`, `PositionSeeder`, `EmployeeSeeder` (demo: Andi Wijaya)
- Test manual: show + edit page verified via browser ✅

### Session #6 — 4 Maret 2026
**Goal:** EmployeePolicy + Feature Tests + Employee Documents + v1.0.0
**Hasil:**
- `EmployeePolicy`: 6 methods (viewAny, view, create, update, delete, viewSensitiveData), super-admin bypass via `before()`
- Fix `Controller.php`: tambah `AuthorizesRequests` trait
- Fix `RolePermissionSeeder`: hapus `manage-employees` (invalid), ganti dengan granular permissions
- `EmployeeController`: authorize() explicit per method (create, index, show, edit, destroy)
- `.env.testing`: SQLite in-memory untuk test isolation
- `EncryptionHelperTest`: 7 unit tests — **7/7 ✅**
- `EmployeeCrudTest`: 9 feature tests — **9/9 ✅**
- `EmployeeRbacTest`: 9 feature tests — **9/9 ✅**
- Employee Documents (Slice 6): migration + model + DocumentService + controller + view partial
- `git tag v1.0.0` — Fase 1 complete! 🎉

---

*Update file ini setiap akhir sesi kerja.*
