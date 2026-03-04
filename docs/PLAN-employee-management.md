# PLAN: Employee Management Module

> **Project:** OrcaHR  
> **Type:** BACKEND + WEB (Laravel 12 + Blade + Alpine.js)  
> **Fase:** 1 — Minggu 2-3  
> **Dibuat:** 4 Maret 2026  
> **Status:** APPROVED → IMPLEMENTATION

---

## Overview

Membangun Module 1: Employee Management secara **vertical slice** — setiap fitur dikerjakan end-to-end (migration → model → service → controller → blade → policy → test) sebelum pindah ke fitur berikutnya.

Modul ini adalah **single source of truth** untuk semua data karyawan dan menjadi dependensi untuk Attendance, Leave, Payroll, ESS.

---

## Success Criteria

- [ ] Admin bisa tambah, edit, lihat karyawan lengkap dengan data terenkripsi
- [ ] Department, Position, Job Level CRUD berfungsi
- [ ] Employee number auto-generate format `RKS-YYYY-NNNN`
- [ ] NIK unik divalidasi via HMAC hash (bukan plaintext compare)
- [ ] Perubahan jabatan/dept = record baru (bukan overwrite)
- [ ] Soft delete — karyawan tidak benar-benar terhapus
- [ ] `php artisan test` lulus semua test

---

## Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Framework | Laravel 12 | Monolith, web routes |
| Views | Blade + Alpine.js | Interactivity tanpa Vue |
| Styling | Tailwind CSS v4 | Custom brand colors |
| ID | ULID (`HasUlids` trait) | Semua PK dan FK |
| Encryption | `encrypt_field()` / `hmac_hash()` | Sudah ada di EncryptionHelper |
| Auth | Blade `@can` directive | Via Spatie Permission |

---

## Urutan Implementasi (Dependency-First)

```
job_levels → departments → positions
                ↓
           employees (core)
                ↓
    employments + bank_accounts + employee_bpjs
                ↓
         employee_documents
```

---

## Task Breakdown

---

### 🟦 SLICE 1: Job Level CRUD

**INPUT:** Fresh project dengan Foundation selesai  
**OUTPUT:** CRUD Job Level berfungsi (list, create, edit, delete)  
**VERIFY:** `GET /job-levels` → list; `POST /job-levels` → create berhasil

#### Tasks

| # | Task | File | Keterangan |
|---|---|---|---|
| 1.1 | Migration `job_levels` | `database/migrations/` | `id`(ULID), `name`, `level`(int), timestamps |
| 1.2 | Model `JobLevel` | `app/Models/JobLevel.php` | HasUlids, Auditable |
| 1.3 | `JobLevelService` | `app/Services/JobLevelService.php` | create, update, delete |
| 1.4 | `JobLevelController` | `app/Http/Controllers/Settings/` | index, create, store, edit, update, destroy |
| 1.5 | Blade views | `resources/views/settings/job-levels/` | index.blade.php, form.blade.php |
| 1.6 | Route | `routes/web.php` | Resource route `/settings/job-levels` |
| 1.7 | Sidebar link | `components/layouts/app.blade.php` | Tambah di group Sistem |

**Commit:** `feat(employee): job level CRUD`

---

### 🟦 SLICE 2: Department CRUD

**INPUT:** Job levels sudah ada  
**OUTPUT:** Department CRUD + hierarki parent-child, assign department head  
**VERIFY:** Bisa buat dept dengan parent, bisa assign head

| # | Task | File | Keterangan |
|---|---|---|---|
| 2.1 | Migration `departments` | `database/migrations/` | `id`, `name`, `code`(unique), `parent_id`(FK self, nullable), `head_id`(FK employees, nullable) |
| 2.2 | Model `Department` | `app/Models/Department.php` | HasUlids, Auditable, self-referencing `parent()`, `children()` |
| 2.3 | `DepartmentService` | `app/Services/DepartmentService.php` | create, update, delete (validasi: tidak bisa hapus jika ada karyawan aktif) |
| 2.4 | `DepartmentController` | `app/Http/Controllers/Settings/` | Resource |
| 2.5 | Blade views | `resources/views/settings/departments/` | index, form (dropdown parent + head) |
| 2.6 | Route + Sidebar | `routes/web.php` | `/settings/departments` |

**Commit:** `feat(employee): department CRUD`

---

### 🟦 SLICE 3: Position CRUD

**INPUT:** Job levels + departments ada  
**OUTPUT:** Position CRUD dengan relasi ke dept + job level  
**VERIFY:** Buat position terikat dept + job level, tampil di list

| # | Task | File | Keterangan |
|---|---|---|---|
| 3.1 | Migration `positions` | `database/migrations/` | `id`, `name`, `department_id`(FK), `job_level_id`(FK) |
| 3.2 | Model `Position` | `app/Models/Position.php` | HasUlids, Auditable, relasi dept + job level |
| 3.3 | `PositionService` | `app/Services/PositionService.php` | create, update, delete |
| 3.4 | `PositionController` | `app/Http/Controllers/Settings/` | Resource |
| 3.5 | Blade views | `resources/views/settings/positions/` | index, form |

**Commit:** `feat(employee): position CRUD`

---

### 🟦 SLICE 4: Employee Create

**INPUT:** Org structure (dept, position, job level) siap  
**OUTPUT:** Form tambah karyawan lengkap dengan enkripsi aktif  
**VERIFY:** Buat karyawan → data tersimpan terenkripsi di DB; NIK duplikat ditolak

#### Migrations (paralel bisa)

| Migration | Tabel | Field Utama |
|---|---|---|
| 4.1a | `employees` | ULID, `user_id`(nullable FK), `employee_number`(unique), `full_name`, `email`, NIK dual-column (`nik_encrypted`, `nik_hash`), NPWP dual-column, `birth_date`, enums (gender, marital, blood, religion), `address_encrypted`, `photo`, `deleted_at` |
| 4.1b | `employments` | ULID, `employee_id`(FK), `department_id`, `position_id`, `job_level_id`, `employment_status`(enum), `join_date`, `end_date`(nullable), `effective_from`, `effective_to`(nullable) |
| 4.1c | `bank_accounts` | ULID, `employee_id`(FK), 4 kolom encrypted |
| 4.1d | `employee_bpjs` | ULID, `employee_id`(FK), `bpjs_kes_encrypted`, `bpjs_tk_encrypted`, `bpjs_class`(enum) |

#### Models

| # | Task | File |
|---|---|---|
| 4.2 | Model `Employee` | `app/Models/Employee.php` — HasUlids, SoftDeletes, Auditable, **Encryptable trait** |
| 4.3 | **Encryptable trait** | `app/Traits/Encryptable.php` — auto-encrypt di `setAttribute`, auto-decrypt di `getAttribute` |
| 4.4 | Model `Employment` | `app/Models/Employment.php` |
| 4.5 | Model `BankAccount` | `app/Models/BankAccount.php` — Encryptable |
| 4.6 | Model `EmployeeBpjs` | `app/Models/EmployeeBpjs.php` — Encryptable |

#### Service + Controller

| # | Task | File |
|---|---|---|
| 4.7 | `EmployeeService` | `app/Services/EmployeeService.php` — `create()`, `update()`, `generateEmployeeNumber()`, NIK unique check via hmac_hash |
| 4.8 | `StoreEmployeeRequest` | `app/Http/Requests/StoreEmployeeRequest.php` |
| 4.9 | `UpdateEmployeeRequest` | `app/Http/Requests/UpdateEmployeeRequest.php` |
| 4.10 | `EmployeeController` | `app/Http/Controllers/Employee/EmployeeController.php` |
| 4.11 | `EmployeePolicy` | `app/Policies/EmployeePolicy.php` — view-employees, manage-employees, view-sensitive-data |

#### Views

| # | View | Keterangan |
|---|---|---|
| 4.12 | `employees/create.blade.php` | Tab/section: Info Pribadi, Info Pekerjaan, BPJS & Bank |
| 4.13 | `employees/index.blade.php` | DataTable dengan filter (dept, status) |
| 4.14 | `employees/show.blade.php` | Detail view, sensitive data RBAC-protected |
| 4.15 | `employees/edit.blade.php` | Form edit |

**Commit:** `feat(employee): CRUD with encryption`

---

### 🟦 SLICE 5: Employment History

**INPUT:** Employee CRUD selesai  
**OUTPUT:** Bisa tambah riwayat jabatan baru (effective-dated), list history  
**VERIFY:** Ubah jabatan → record lama `effective_to` di-set, record baru dibuat

| # | Task | File |
|---|---|---|
| 5.1 | Method `EmployeeService.changePosition()` | — update effective_to lama, insert baru |
| 5.2 | `EmployeeHistoryController` | `app/Http/Controllers/Employee/` |
| 5.3 | View history | `employees/partials/employment-history.blade.php` |

**Commit:** `feat(employee): employment history effective-dated`

---

### 🟦 SLICE 6: Employee Documents

**INPUT:** Employee model siap  
**OUTPUT:** Upload, list, download dokumen karyawan  
**VERIFY:** Upload PDF → tersimpan di `storage/app/private`; download via signed URL

| # | Task | File |
|---|---|---|
| 6.1 | Migration `employee_documents` | `id`, `employee_id`, `type`(enum), `file_path`, `expires_at`(nullable), timestamps |
| 6.2 | Model `EmployeeDocument` | HasUlids, Auditable |
| 6.3 | `DocumentService` | upload (private disk), delete, generateSignedUrl |
| 6.4 | `EmployeeDocumentController` | store, destroy, download |
| 6.5 | View partial | `employees/partials/documents.blade.php` |

**Commit:** `feat(employee): document upload + signed URL`

---

### 🟦 SLICE 7: Tests

**INPUT:** Semua slices di atas selesai  
**OUTPUT:** Test suite lulus  
**VERIFY:** `php artisan test --filter EmployeeTest` → semua pass

| # | Test | File | Covers |
|---|---|---|---|
| 7.1 | EncryptionHelperTest | `tests/Unit/EncryptionHelperTest.php` | encrypt→decrypt roundtrip, hmac deterministic |
| 7.2 | EmployeeServiceTest | `tests/Unit/EmployeeServiceTest.php` | NIK uniqueness, employee number generation |
| 7.3 | EmployeeCrudTest | `tests/Feature/Employee/EmployeeCrudTest.php` | Create/read/update/delete HTTP |
| 7.4 | EmployeeRbacTest | `tests/Feature/Employee/EmployeeRbacTest.php` | HR Admin bisa CRUD, Employee tidak bisa |

**Commit:** `test(employee): unit + feature tests`

---

## File Structure (Target)

```
app/
├── Http/Controllers/
│   ├── Employee/
│   │   ├── EmployeeController.php
│   │   ├── EmployeeHistoryController.php
│   │   └── EmployeeDocumentController.php
│   └── Settings/
│       ├── DepartmentController.php
│       ├── PositionController.php
│       └── JobLevelController.php
├── Models/
│   ├── Department.php
│   ├── Employee.php
│   ├── Employment.php
│   ├── BankAccount.php
│   ├── EmployeeBpjs.php
│   ├── EmployeeDocument.php
│   ├── JobLevel.php
│   └── Position.php
├── Services/
│   ├── DepartmentService.php
│   ├── EmployeeService.php
│   ├── JobLevelService.php
│   ├── PositionService.php
│   └── DocumentService.php
├── Http/Requests/
│   ├── StoreEmployeeRequest.php
│   └── UpdateEmployeeRequest.php
├── Policies/
│   └── EmployeePolicy.php
└── Traits/
    └── Encryptable.php   ← baru

resources/views/
├── employees/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   ├── edit.blade.php
│   └── partials/
│       ├── employment-history.blade.php
│       └── documents.blade.php
└── settings/
    ├── departments/
    │   ├── index.blade.php
    │   └── form.blade.php
    ├── positions/
    │   ├── index.blade.php
    │   └── form.blade.php
    └── job-levels/
        ├── index.blade.php
        └── form.blade.php

database/migrations/
├── ..._create_job_levels_table.php
├── ..._create_departments_table.php
├── ..._create_positions_table.php
├── ..._create_employees_table.php
├── ..._create_employments_table.php
├── ..._create_bank_accounts_table.php
├── ..._create_employee_bpjs_table.php
└── ..._create_employee_documents_table.php

tests/
├── Unit/
│   ├── EncryptionHelperTest.php
│   └── EmployeeServiceTest.php
└── Feature/Employee/
    ├── EmployeeCrudTest.php
    └── EmployeeRbacTest.php
```

---

## Business Rules (Penting)

| Rule | Implementasi |
|---|---|
| Employee number: `RKS-YYYY-NNNN` | `EmployeeService::generateEmployeeNumber()` — query MAX, increment |
| NIK unik | Validasi via `hmac_hash($nik)`, cek di kolom `nik_hash` |
| Ubah jabatan = record baru | `Employment::create()` dengan `effective_to` lama di-set |
| Data sensitif hanya role tertentu | Policy + `@can('view-sensitive-data')` di Blade |
| Soft delete only | `SoftDeletes` + `withTrashed()` scope jika perlu |

---

## Verification Plan

### Automated Tests

```bash
# Jalankan semua test
php artisan test

# Filter per group
php artisan test --filter EncryptionHelperTest
php artisan test --filter EmployeeServiceTest
php artisan test --filter EmployeeCrudTest
php artisan test --filter EmployeeRbacTest
```

### Manual Verification

1. Login sebagai `admin@orcahr.local` / `password`
2. Settings → Job Levels → Tambah level baru → Tampil di list ✅
3. Settings → Departments → Tambah dept dengan parent → Hierarki benar ✅
4. Settings → Positions → Tambah position → Relasi dept + level benar ✅
5. Karyawan → Tambah Karyawan → Isi NIK `3201...` → Tersimpan
6. Buka DB: `SELECT nik_encrypted, nik_hash FROM employees` → nik_hash terisi, nik_encrypted bukan plaintext ✅
7. Coba tambah karyawan dengan NIK sama → Validasi error muncul ✅
8. Ubah jabatan karyawan → Cek `employments` table → 2 record (lama + baru) ✅
9. Login sebagai role `employee` → Pastikan menu CRUD tidak muncul ✅

---

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| Form panjang → UX buruk | Pakai tab/section Alpine.js (bukan multi-step page) |
| Enkripsi lambat pada bulk | Oke untuk MVP, akan dioptimasi jika butuh |
| Department delete dengan karyawan aktif | Hard block di DepartmentService — throw exception |
| head_id FK ke employees yang belum ada | Nullable FK, input optional |
