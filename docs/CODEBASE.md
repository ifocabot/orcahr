# 📘 CODEBASE.md — OrcaHR

> **File ini adalah sumber konteks utama untuk AI agent.**
> Baca file ini PERTAMA di setiap sesi baru sebelum mulai coding.
> Terakhir diperbarui: 4 Maret 2026

---

## Identitas Project

| Item | Detail |
|---|---|
| **Nama** | OrcaHR |
| **Tipe** | HRIS (Human Resource Information System) |
| **Scope** | Single company (single-tenant) |
| **Developer** | Solo developer |
| **Status** | Pre-development (dokumentasi selesai, belum coding) |

---

## Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| **Framework** | Laravel 12 (full-stack) | Monolith, bukan API-only |
| **Views** | Blade templates | Server-rendered |
| **Interactivity** | Alpine.js | Lightweight reactivity |
| **Styling** | Tailwind CSS | Utility-first |
| **Database** | MySQL 8 | 46 tabel |
| **ID Strategy** | ULID (`HasUlids` trait) | Secure, sortable, non-guessable |
| **Auth** | Laravel Breeze (logic only) | Controllers + routes from Breeze, custom views |
| **RBAC** | Spatie Permission | Role & permission management |
| **Queue** | Laravel Queue (Redis) | Untuk recalculation jobs |
| **PDF** | DomPDF | Slip gaji |
| **Export** | Laravel Excel | Laporan CSV/Excel |

---

## Arsitektur: Service Layer Pattern

```
Route → Controller (TIPIS) → Service (SEMUA LOGIC) → Model
              ↓
         Blade View

⚠️ ATURAN KRITIS:
- Controller TIDAK BOLEH punya business logic
- Semua logic di Service class (app/Services/)
- Alasan: Service reusable untuk API controller nanti (migrasi Vue)
```

### Contoh Pattern

```php
// ❌ SALAH — logic di controller
public function store(Request $request) {
    $employee = new Employee();
    $employee->nik_encrypted = encrypt($request->nik);
    $employee->save();
}

// ✅ BENAR — controller tipis, logic di service
public function store(StoreEmployeeRequest $request, EmployeeService $service) {
    $employee = $service->create($request->validated());
    return redirect()->route('employees.show', $employee)->with('success', '...');
}
```

---

## Modul (10 Total)

| # | Modul | Fase | Status |
|---|---|---|---|
| 0 | Foundation (Auth, RBAC, Audit Log) | 1 | ⬜ Belum |
| 1 | Employee Management | 1 | ⬜ Belum |
| 2 | Attendance | 2 | ⬜ Belum |
| 3 | Leave Management | 2 | ⬜ Belum |
| 4 | Payroll Engine ⭐ | 3 | ⬜ Belum |
| 5 | Employee Self-Service (ESS) | 4 | ⬜ Belum |
| 6 | Recruitment & Onboarding | 5 | ⬜ Belum |
| 7 | Project Management | 6 | ⬜ Belum |
| 8 | Announcement | 4 | ⬜ Belum |
| 9 | Reports & Analytics | 7 | ⬜ Belum |

---

## Timeline

```
Fase 1 (Minggu 1-4)    → Foundation + Employee
Fase 2 (Minggu 5-8)    → Attendance + Leave
Fase 3 (Minggu 9-14)   → Payroll Engine (CORE) ⭐
Fase 4 (Minggu 15-18)  → ESS + Announcement
Fase 5 (Minggu 19-24)  → Recruitment & Onboarding
Fase 6 (Minggu 25-28)  → Project Management
Fase 7 (Minggu 29-32)  → Polish + Deployment

Total: ~32 minggu (~8 bulan)
```

---

## Roles (5 Total)

| Role | Level | Akses Utama |
|---|---|---|
| Super Admin | 1 | Akses penuh + system settings |
| HR Admin | 2 | Kelola karyawan, cuti, attendance, recruitment |
| Payroll Admin | 2 | Kelola payroll, slip gaji |
| Department Head | 3 | Approve cuti/OT tim sendiri, manpower request |
| Employee | 4 | Self-service: profil, cuti, slip gaji sendiri |

---

## Security: Enkripsi Pattern

### Dual-Column Pattern (untuk field sensitif yang perlu di-search)

```
nik_encrypted  → AES-256 (untuk decrypt & tampilkan)
nik_hash       → HMAC-SHA256 (untuk search & cek duplikasi)
```

### Field yang Dienkripsi

- NIK (+ hash), NPWP (+ hash), KK, Passport, SIM
- BPJS Kesehatan, BPJS Ketenagakerjaan
- Rekening bank, nama bank, cabang
- Alamat, telepon, email pribadi
- Dokumen medis

### Helper Functions

```php
encrypt_field($value)   → AES-256 encrypted string
decrypt_field($value)   → original string
hmac_hash($value)       → deterministic HMAC-SHA256 hash
```

> Kunci enkripsi di `.env` (ENCRYPTION_KEY, HMAC_KEY), BUKAN APP_KEY.

---

## Naming Conventions

### PHP / Laravel

| Tipe | Convention | Contoh |
|---|---|---|
| Model | Singular PascalCase | `Employee`, `PayrollRun` |
| Controller | `{Model}Controller` | `EmployeeController` |
| Service | `{Model}Service` | `EmployeeService` |
| Request | `{Action}{Model}Request` | `StoreEmployeeRequest` |
| Job | `{Action}{Model}Job` | `RecalculateAttendanceJob` |
| Event | `{Model}{Action}Event` | `LeaveRequestApprovedEvent` |
| Policy | `{Model}Policy` | `EmployeePolicy` |

### Blade

| Tipe | Convention | Contoh |
|---|---|---|
| View file | kebab-case | `employee-form.blade.php` |
| Component | kebab-case | `data-table.blade.php` |
| Route name | dot-separated | `employees.index`, `payroll.runs.show` |

### Git

| Prefix | Contoh |
|---|---|
| `feat(scope)` | `feat(payroll): add formula engine` |
| `fix(scope)` | `fix(attendance): recalc not triggered` |
| `refactor(scope)` | `refactor(employee): extract encryption` |

---

## Key Architecture Principles

1. **Pisah Fakta, Aturan, Hasil** — Clock log = saksi, Schedule = konteks, Daily attendance = hasil
2. **Effective-Dated Everything** — Semua yang bisa berubah punya `effective_from` / `effective_to`
3. **Event-Driven Recalculation** — Clock log masuk → tandai dirty → recalc targeted
4. **Payroll Lock Boundary** — Period locked = TIDAK BOLEH edit, hanya adjustment
5. **Audit Everything** — Semua aksi tercatat: siapa, kapan, apa berubah
6. **Immutable Facts** — Clock log & payroll run TIDAK BOLEH di-UPDATE, hanya correction/adjustment

---

## Dokumentasi Lengkap

> Baca sesuai kebutuhan. JANGAN baca semua sekaligus.

| Dokumen | Baca Kapan |
|---|---|
| `docs/PROJECT_INTENT.md` | Kalau bingung *kenapa* project ini ada |
| `docs/PROJECT_BLUEPRINT.md` | Kalau butuh timeline / fase overview |
| `docs/ARCHITECTURE_PRINCIPLES.md` | Kalau ragu tentang design decision |
| `docs/SECURITY_BLUEPRINT.md` | Kalau handle data sensitif |
| `docs/MODULE_SPECIFICATIONS.md` | Kalau butuh detail entitas / aturan bisnis modul |
| `docs/DATABASE_SCHEMA.md` | Kalau butuh ERD / tabel structure |
| `docs/API_SPECIFICATIONS.md` | **Referensi FUTURE** — untuk migrasi Vue nanti |
| `docs/FLOWCHARTS.md` | Kalau butuh visualisasi alur bisnis |
| `docs/DEV_STANDARDS.md` | Kalau butuh conventions / folder structure |
| `docs/GIT_WORKFLOW.md` | Kalau butuh aturan Git, branch, commit, versioning |
| `docs/TESTING_STRATEGY.md` | Kalau mau nulis test |
| `docs/PROGRESS.md` | **Cek progress terakhir** |

---

## Anti-Hallucination Rules

> ⛔ **JANGAN lakukan ini:**

| ❌ Jangan | ✅ Sebaliknya |
|---|---|
| Jangan assume ada Vue.js | Kita pakai **Blade + Alpine** |
| Jangan assume multi-tenant | Ini **single company** |
| Jangan taruh logic di controller | Semua logic di **Service class** |
| Jangan assume ada Procurement modul | **Tidak ada procurement** |
| Jangan pakai Sanctum token auth | Kita pakai **session auth (Breeze)** |
| Jangan bikin API controller | Kita pakai **web routes + Blade** |
| Jangan skip audit log | Audit log **dari Fase 1** |
| Jangan simpan field sensitif plaintext | **Selalu encrypt** |
| Jangan edit payroll run yang sudah lock | Pakai **adjustment** |
| Jangan buat migration tanpa cek `DATABASE_SCHEMA.md` | **Selalu cross-check** |
| Jangan pakai auto-increment bigint untuk ID | Pakai **ULID** (`HasUlids` trait) |
| Jangan pakai Breeze default views | Bikin **custom Blade views** sendiri |

---

## Folder Structure (Target)

```
orcahr/
├── app/
│   ├── Http/Controllers/     → Thin controllers (per module folder)
│   ├── Models/               → Eloquent models
│   ├── Services/             → ⭐ Business logic (KUNCI ARSITEKTUR)
│   ├── Jobs/                 → Queue jobs
│   ├── Events/               → Domain events
│   ├── Listeners/            → Event listeners
│   ├── Policies/             → Authorization
│   ├── Observers/            → Audit logging
│   ├── Traits/               → Encryptable, Auditable
│   ├── Helpers/              → encrypt_field, hmac_hash
│   └── View/Components/      → Blade components
├── resources/views/          → Blade templates (per module folder)
├── database/migrations/      → Migration files
├── routes/web.php            → All web routes
├── tests/
│   ├── Unit/                 → Service tests, helper tests
│   └── Feature/              → HTTP tests, RBAC tests
└── docs/                     → Project documentation
```

---

## Jobs & Events Strategy

### Kapan Pakai Queue Job (Async)

| Job | Trigger | Alasan Async |
|---|---|---|
| `RecalculateAttendanceJob` | Clock log, approval | Bisa heavy (query schedule + logs + corrections) |
| `RunPayrollForEmployeeJob` | Payroll run | Kalkulasi per karyawan bisa lambat |
| `GeneratePayslipJob` | Payroll locked | PDF generation lambat |
| `SendNotificationJob` | Various | Email/notification tidak perlu blocking |

### Kapan JANGAN Pakai Queue (Sync Langsung)

- CRUD biasa (employee, department, position)
- Approval action (user butuh feedback instant)
- Login/logout
- Validation

### Events & Listeners

| Event | Listener(s) | Kapan |
|---|---|---|
| `ClockLogCreated` | `MarkDateDirty` → `QueueRecalcJob` | Clock in/out |
| `LeaveRequestApproved` | `DeductLeaveBalance`, `RecalcAttendance` | Approval cuti |
| `LeaveRequestRejected` | `ReturnLeaveBalance` | Rejection cuti |
| `OvertimeApproved` | `RecalcAttendance` | Approval OT |
| `CorrectionApproved` | `RecalcAttendance` | Approval koreksi |
| `PayrollLocked` | `GenerateSlips`, `NotifyEmployees` | Lock period payroll |
| `EmployeeCreated` | `AssignOnboardingChecklist` | Hire dari recruitment |

### Queue Driver

| Environment | Driver | Alasan |
|---|---|---|
| **Local** | `sync` | Langsung execute, mudah debug |
| **Production** | `redis` | Async, reliable, bisa retry |

> **Aturan:** Di `.env` local pakai `QUEUE_CONNECTION=sync`. Di production pakai `redis`.

---

## Development Workflow: Vertical Slice

> **Setiap fitur dikerjakan LENGKAP sebelum pindah ke fitur berikutnya.**
> Jangan bikin semua migration dulu, atau semua model dulu.

### Flow per Fitur (9 Step)

```
1. Migration (tabel yang dibutuhkan fitur ini SAJA)
2. Model + Relationships
3. Service class (business logic)
4. Form Request (validation)
5. Controller (thin, panggil service)
6. Blade Views (form + list + detail)
7. Policy (authorization)
8. Test (minimal happy path)
9. ✅ COMMIT → fitur ini DONE
```

### Aturan

| ✅ Benar | ❌ Salah |
|---|---|
| Selesaikan Department CRUD end-to-end, baru Employee | Bikin 46 migration sekaligus |
| Setiap commit = 1 fitur yang bisa dipakai | Commit "WIP" tanpa hasil nyata |
| Demo-able setiap akhir minggu | Berminggu-minggu tanpa output visual |

---

## Protokol Wajib untuk AI Agent

> ⚠️ **WAJIB diikuti setiap sesi.**

### Awal Sesi
1. Baca `CODEBASE.md` (file ini)
2. Baca `PROGRESS.md` → cek status terakhir, known issues, next action
3. Jika butuh detail modul → baca `MODULE_SPECIFICATIONS.md`
4. Jika butuh detail tabel → baca `DATABASE_SCHEMA.md`

### Selama Sesi
- Setiap selesai **membuat/mengubah file**, update checklist di `PROGRESS.md`
- Setiap ada **keputusan arsitektur/teknis baru**, catat di `PROGRESS.md` → Decisions Log
- Setiap menemukan **bug/issue**, catat di `PROGRESS.md` → Known Issues
- Jika membuat **migration**, cross-check dengan `DATABASE_SCHEMA.md`
- Jika membuat **route**, ikuti naming convention di file ini

### Akhir Sesi
1. Update `PROGRESS.md`:
   - Checklist: tandai `[x]` yang selesai, `[/]` yang in progress
   - Session Log: tambah entry baru dengan ringkasan
   - Known Issues: update jika ada
   - Status Overview: update fase, minggu, next action
2. Jika ada perubahan arsitektur → update `CODEBASE.md`
3. Commit message gunakan convention: `type(scope): message`

### Jangan Lupa
- **Selalu tanya user** sebelum mengubah arsitektur atau tech stack
- **Selalu jalankan test** setelah perubahan besar: `php artisan test`
- **Selalu cross-check** `DATABASE_SCHEMA.md` sebelum buat migration baru

---

*File ini harus di-update saat ada perubahan arsitektur, tech stack, atau modul.*
*Versi: 1.1 — 4 Maret 2026*
