# 📊 PROGRESS.md — OrcaHR

> Log progress development. Update setiap akhir sesi.
> AI agent: baca file ini untuk tahu **dimana kita berhenti**.

---

## Status Overview

| Item | Status |
|---|---|
| **Fase saat ini** | Pre-development (dokumentasi) |
| **Minggu ke** | 0 |
| **Last session** | 4 Maret 2026 |
| **Next action** | Mulai Fase 1: Laravel setup + auth + RBAC |

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
- [ ] `laravel new orcahr` (Laravel 12) + Tailwind + Alpine + Vite
- [ ] Breeze install → hapus views → keep auth controllers + routes
- [ ] Spatie Permission: 5 roles, base permissions
- [ ] Base layout: sidebar, navbar (custom Blade components)

**Hari 3-4: Security Foundation**
- [ ] Encryption helpers (`encrypt_field`, `decrypt_field`, `hmac_hash`)
- [ ] Audit log system (Observer/Trait)
- [ ] Base Service class pattern
- [ ] Test: encryption roundtrip (3 tests)

**Hari 5: Hardening**
- [ ] RBAC middleware + Blade directives (`@can`, `@role`)
- [ ] Test: RBAC basic (3 tests)
- [ ] ✅ COMMIT: `feat(foundation): auth + rbac + encryption`

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

**Next:** Mulai coding Fase 1

---

*Update file ini setiap akhir sesi kerja.*
