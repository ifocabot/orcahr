# 📊 PROGRESS.md — OrcaHR

> Log progress development. Update setiap akhir sesi.
> AI agent: baca file ini untuk tahu **dimana kita berhenti**.

---

## Status Overview

| Item | Status |
|---|---|
| **Fase saat ini** | Fase 1 — Employee Management |
| **Minggu ke** | 2 |
| **Last session** | 4 Maret 2026 |
| **Next action** | Employee Show + Edit (Slice 5) |
| **Git HEAD** | `3a5121b` — master |

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

**Employee Show + Edit (next)**
- [ ] `employees/show.blade.php` — detail view (data pribadi, employment, bank, BPJS)
- [ ] `employees/edit.blade.php` — edit form (tabs, sama dengan create)
- [ ] ✅ COMMIT: `feat(employee): show + edit views`

**Employee Documents (Minggu 3)**
- [ ] Migration: `employee_documents`
- [ ] Document upload (encrypted storage + signed URL)
- [ ] ✅ COMMIT: `feat(employee): document upload`

**Employment History View (Minggu 3)**
- [ ] Employment history view (effective-dated timeline)
- [ ] ✅ COMMIT: `feat(employee): employment history view`

**Policies + Testing (Minggu 3)**
- [ ] EmployeePolicy (authorization per role)
- [ ] Feature tests: Employee CRUD (5 tests)
- [ ] Feature tests: RBAC permissions (5 tests)
- [ ] ✅ COMMIT: `test(employee): CRUD + RBAC tests`

---

### Minggu 4: Hardening + Demo

- [ ] Bug fixes dari testing
- [ ] UI polish (responsive, loading states)
- [ ] Seeder: demo data (departments, positions, employees)
- [ ] Code review + refactor (Pint formatting)
- [ ] ✅ Tag: `v1.0.0` — Fase 1 selesai

**🎯 Milestone:** Admin bisa manage data karyawan lengkap. Demo-able.

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
| 1 | `employees/show.blade.php` belum dibuat | High | Pending |
| 2 | `employees/edit.blade.php` belum dibuat | High | Pending |
| 3 | EmployeePolicy belum ada | Medium | Pending |
| 4 | Employee index: pagination belum ada (semua dimuat sekaligus) | Low | Pending |

---

## Session Log

### Session #1 — 4 Maret 2026
**Goal:** Lengkapi semua dokumentasi project
**Hasil:**
- Dibuat 12 dokumen lengkap (10 technical docs + CODEBASE.md + PROGRESS.md)
- Revisi: hapus Procurement, confirm single-company, switch Vue → Blade
- Cross-check alignment semua dokumen (3 inkonsistensi ditemukan & fixed)

### Session #2 — 4 Maret 2026
**Goal:** Fase 1 Foundation — setup Laravel, auth, RBAC, encryption, base UI
**Hasil:**
- Install Breeze + Spatie Permission, konfigurasi .env (ENCRYPTION_KEY, HMAC_KEY, sync queue)
- EncryptionHelper: `encrypt_field()`, `decrypt_field()`, `hmac_hash()`
- Auditable trait + AuditLog model (immutable, ULID)
- RolePermissionSeeder: 5 roles + 20 permissions, 1 super admin seeded
- Custom layouts: dark sidebar RBAC-aware, glassmorphism login, dashboard dengan stats cards
- Fix Tailwind v4 (vite.config.js + postcss.config.js) + Blade component path
- Git init + push ke github.com/ifocabot/orcahr (master branch)

### Session #3 — 4 Maret 2026
**Goal:** Settings CRUD (Job Level, Department, Position) + UX Pattern Drawer + SweetAlert2
**Hasil:**
- JobLevel, Department (self-ref), Position — migrations + models + services + controllers + views
- Drawer-based CRUD (no /create /edit pages separate)
- `x-drawer` Blade component (reusable, 5 sizes, ESC + backdrop click close)
- SweetAlert2: `window.toast()` + `window.confirmDelete()`
- Routes: `->except(['create','edit'])` untuk settings resources

### Session #4 — 4 Maret 2026
**Goal:** Employee Core — Migration, Model, Service, Controller, Views (Create + Index)
**Hasil:**
- 4 migrations: employees (NIK dual-column encrypted+HMAC), employments (effective-dated), bank_accounts, employee_bpjs
- Encryptable trait: auto-encrypt/decrypt, HMAC hash support
- EmployeeService: `generateEmployeeNumber()` (RKS-YYYY-NNNN), `isNikTaken()` (no decrypt), DB::transaction create/update
- Employee create form: 4 tabs (Info Pribadi, Info Pekerjaan, Rekening Bank, BPJS)
- Permissions: create/edit/delete-employees, view-employment-history, manage-bank-accounts/bpjs
- Verified: tombol Tambah Karyawan muncul, tabs berfungsi ✅

**Next:** `employees/show.blade.php` + `employees/edit.blade.php`

---

*Update file ini setiap akhir sesi kerja.*

> Log progress development. Update setiap akhir sesi.
> AI agent: baca file ini untuk tahu **dimana kita berhenti**.

---

## Status Overview

| Item | Status |
|---|---|
| **Fase saat ini** | Fase 1 — Employee Management |
| **Minggu ke** | 2 |
| **Last session** | 4 Maret 2026 |
| **Next action** | Department CRUD (vertical slice pertama) |

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
- [x] DEV_STANDARDS.md (Blade, Service Layer, Git)
- [x] TESTING_STRATEGY.md (test priority, examples)
- [x] GIT_WORKFLOW.md (branch, commit, versioning, rollback)
- [x] CODEBASE.md (AI context file)
- [x] PROGRESS.md (this file)

---

## Fase 1: Foundation + Employee Management (Minggu 1-4)

### Minggu 1: Foundation

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

### Minggu 2: Employee Management — Organisation

**Hari 1: Department (vertical slice)**
- [ ] Migration: `departments`
- [ ] Model + self-referencing relationship (parent)
- [ ] DepartmentService → Controller → Blade (CRUD)
- [ ] ✅ COMMIT: `feat(employee): department CRUD`

**Hari 2: Position + Job Level (vertical slice)**
- [ ] Migration: `positions`, `job_levels`
- [ ] Models + relationships
- [ ] Service → Controller → Blade (CRUD)
- [ ] ✅ COMMIT: `feat(employee): position + job level CRUD`

**Hari 3-4: Employee Create (vertical slice)**
- [ ] Migration: `employees`, `employments`, `bank_accounts`, `employee_bpjs`
- [ ] Employee model + Encryptable trait
- [ ] EmployeeService.create() (with encryption)
- [ ] StoreEmployeeRequest (validation)
- [ ] Employee create form (Blade, multi-step)
- [ ] ✅ COMMIT: `feat(employee): create with encryption`

**Hari 5: Employee List + Detail**
- [ ] Employee list (DataTable component + Alpine)
- [ ] Employee detail view (decrypt for authorized roles)
- [ ] ✅ COMMIT: `feat(employee): list + detail view`

### Minggu 3: Employee Management — Complete

**Hari 1-2: Employee Edit + Documents**
- [ ] Employee edit form
- [ ] Migration: `employee_documents`
- [ ] Document upload (encrypted storage + signed URL)
- [ ] ✅ COMMIT: `feat(employee): edit + document upload`

**Hari 3: Employment History**
- [ ] Employment history view (effective-dated)
- [ ] Add new employment record
- [ ] ✅ COMMIT: `feat(employee): employment history`

**Hari 4-5: Policies + Testing**
- [ ] EmployeePolicy (authorization per role)
- [ ] Feature tests: Employee CRUD (5 tests)
- [ ] Feature tests: RBAC permissions (5 tests)
- [ ] ✅ COMMIT: `test(employee): CRUD + RBAC tests`

### Minggu 4: Hardening + Demo

- [ ] Bug fixes dari testing
- [ ] UI polish (responsive, flash messages, loading states)
- [ ] Seeder: demo data (departments, positions, employees)
- [ ] Code review + refactor (Pint formatting)
- [ ] ✅ Tag: `v1.0.0` — Fase 1 selesai

**🎯 Milestone:** Admin bisa manage data karyawan lengkap. Demo-able.

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

> Keputusan penting yang diambil selama development.

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

---

## Known Issues / Backlog

> Bug, technical debt, atau ide yang belum dikerjakan.

| # | Issue | Priority |  Status |
|---|---|---|---|
| — | Belum ada | — | — |

---

## Session Log

> Ringkasan per sesi kerja dengan AI agent.

### Session #1 — 4 Maret 2026
**Goal:** Lengkapi semua dokumentasi project
**Hasil:**
- Dibuat 12 dokumen lengkap (10 technical docs + CODEBASE.md + PROGRESS.md)
- Revisi: hapus Procurement, confirm single-company, switch Vue → Blade
- Cross-check alignment semua dokumen (3 inkonsistensi ditemukan & fixed)

### Session #2 — 4 Maret 2026
**Goal:** Fase 1 Foundation — setup Laravel, auth, RBAC, encryption, base UI
**Hasil:**
- Install Breeze + Spatie Permission, konfigurasi .env (ENCRYPTION_KEY, HMAC_KEY, sync queue)
- EncryptionHelper: `encrypt_field()`, `decrypt_field()`, `hmac_hash()`
- Auditable trait + AuditLog model (immutable, ULID)
- RolePermissionSeeder: 5 roles + 20 permissions, 1 super admin seeded
- Custom layouts: dark sidebar RBAC-aware, glassmorphism login, dashboard dengan stats cards
- Fix Tailwind v4 (vite.config.js + postcss.config.js) + Blade component path
- Git init + push ke github.com/ifocabot/orcahr (master branch)

**Issues ditemukan & solved:**
- Spatie migration bigint vs ULID → fix ke char(26)
- Tailwind v4 dual-config conflict → hapus dari postcss, pakai @tailwindcss/vite
- Blade `<x-layouts.auth>` path salah → pindah ke `resources/views/components/layouts/`

**Next:** Department CRUD (vertical slice pertama Minggu 2)

---

*Update file ini setiap akhir sesi kerja.*
