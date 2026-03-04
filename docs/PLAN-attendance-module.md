# PLAN: Attendance & Leave Module

> **Project:** OrcaHR
> **Type:** BACKEND + WEB (Laravel 12 + Blade + Alpine.js)
> **Fase:** 2 — Minggu 5–8
> **Dibuat:** 4 Maret 2026
> **Status:** DRAFT → Review → Implementation

---

## Overview

Membangun Module 2: Attendance & Leave secara **vertical slice** — atas dasar Employee Management yang sudah selesai (Fase 1 / v1.0.0).

Pendekatan: **bottom-up** — Shift → Schedule → Clock In/Out → Daily Engine → Corrections → Overtime → Leave.

---

## Dependency Map

```
employees (Fase 1 ✅)
    ↓
shifts → schedule_assignments
    ↓
clock_logs → daily_attendances
    ↓
attendance_corrections
overtime_requests
    ↓
leave_types → leave_balances → leave_requests
    ↓
holidays (support table)
```

---

## Success Criteria

- [ ] Admin bisa buat shift (WFO, WFH, flex) dan assign ke karyawan
- [ ] Karyawan bisa clock in/out, sistem catat timestamp + IP
- [ ] Daily attendance engine kalkulasi status (hadir, terlambat, absen, dll)
- [ ] Karyawan bisa ajukan koreksi absensi → approval flow
- [ ] Karyawan bisa ajukan overtime → approval flow
- [ ] Admin bisa buat tipe cuti + set saldo awal
- [ ] Karyawan bisa ajukan cuti → approval flow
- [ ] Holiday calendar — hari libur tidak dihitung absen
- [ ] `php artisan test` lulus semua test

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Views | Blade + Alpine.js |
| Styling | Tailwind CSS v4 |
| ID | ULID (HasUlids) |
| Jobs | Queue (sync local, Redis prod) |
| Auth | Spatie Permission (`@can`) |

---

## Urutan Implementasi (Dependency-First)

---

### 🟦 SLICE 1: Shift Management (Admin CRUD)

**INPUT:** Employee data siap
**OUTPUT:** Admin bisa CRUD shift (nama, jam, toleransi)
**VERIFY:** `GET /settings/shifts` → list shifts; `POST` → create berhasil

| # | Task | File | Keterangan |
|---|---|---|---|
| 1.1 | Migration `shifts` | `database/migrations/` | `id`(ULID), `name`, `code`(UK), `clock_in`(time), `clock_out`(time), `break_start`(time nullable), `break_end`(time nullable), `is_flexible`(bool), `late_tolerance_minutes`(int, default 15), `early_leave_tolerance_minutes`(int, default 15) |
| 1.2 | Model `Shift` | `app/Models/Shift.php` | HasUlids, Auditable |
| 1.3 | `ShiftService` | `app/Services/ShiftService.php` | create, update, delete (guard: tidak bisa hapus jika ada schedule aktif) |
| 1.4 | `ShiftController` | `app/Http/Controllers/Settings/ShiftController.php` | index, store, update, destroy (drawer pattern, no /create /edit) |
| 1.5 | View | `resources/views/settings/shifts/index.blade.php` | Tabel + drawer form |
| 1.6 | Route | `routes/web.php` | `Route::resource('shifts')->except(['create','edit'])` |
| 1.7 | Permission | `RolePermissionSeeder` | Tambah `manage-shifts` |

**Commit:** `feat(attendance): shift management CRUD`

---

### 🟦 SLICE 2: Schedule Assignment

**INPUT:** Shifts sudah ada
**OUTPUT:** Admin bisa assign shift ke karyawan dengan tanggal efektif
**VERIFY:** Assign shift ke Andi Wijaya → `GET /employees/{id}` tampil shift aktif

| # | Task | File | Keterangan |
|---|---|---|---|
| 2.1 | Migration `schedule_assignments` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `shift_id`(FK), `effective_from`(date), `effective_to`(date nullable), `type`(enum: individual/department) |
| 2.2 | Model `ScheduleAssignment` | `app/Models/ScheduleAssignment.php` | HasUlids, Auditable, relasi ke Employee + Shift |
| 2.3 | Model update `Employee` | `app/Models/Employee.php` | `currentSchedule()` HasOne (where effective_to null) |
| 2.4 | `ScheduleService` | `app/Services/ScheduleService.php` | assign(), extendUntil(), getCurrentShift() |
| 2.5 | `ScheduleController` | `app/Http/Controllers/Attendance/ScheduleController.php` | store, destroy |
| 2.6 | View partial | `resources/views/employees/partials/schedule.blade.php` | Di employee show page: info shift aktif + form assign |

**Commit:** `feat(attendance): schedule assignment`

---

### 🟦 SLICE 3: Clock In/Out

**INPUT:** Schedule assignment aktif
**OUTPUT:** Karyawan bisa clock in/out via web
**VERIFY:** Clock in → `clock_logs` terisi dengan timestamp + IP; clock out → `daily_attendances` ter-kalkulasi

| # | Task | File | Keterangan |
|---|---|---|---|
| 3.1 | Migration `clock_logs` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `timestamp`(datetime), `type`(enum: clock_in/clock_out), `source`(enum: web/mobile/manual), `ip_address`, `location`(json nullable), `photo`(string nullable), `is_manual`(bool), `manual_reason`(text nullable) |
| 3.2 | Model `ClockLog` | `app/Models/ClockLog.php` | HasUlids; cast: timestamp → datetime |
| 3.3 | `ClockService` | `app/Services/ClockService.php` | `clockIn()`, `clockOut()`, `getActiveLogToday()`, `hasCheckedInToday()` |
| 3.4 | `ClockController` | `app/Http/Controllers/Attendance/ClockController.php` | index (halaman absensi), store (clock in/out action) |
| 3.5 | View | `resources/views/attendance/clock.blade.php` | Tombol Clock In / Clock Out + status hari ini |
| 3.6 | Route | `routes/web.php` | `GET /attendance` (dashboard absensi), `POST /attendance/clock` |

**Commit:** `feat(attendance): clock in/out`

---

### 🟦 SLICE 4: Daily Attendance Engine

**INPUT:** Clock logs terisi
**OUTPUT:** `daily_attendances` ter-hitung otomatis per hari
**VERIFY:** Clock in pukul 09:15 (shift WFO: 09:00) → `late_minutes = 15`, `status = late`

| # | Task | File | Keterangan |
|---|---|---|---|
| 4.1 | Migration `daily_attendances` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `date`(date), `schedule_id`(FK nullable), `clock_in`(datetime nullable), `clock_out`(datetime nullable), `status`(enum: present/absent/late/early_leave/leave/holiday), `late_minutes`(int), `early_leave_minutes`(int), `overtime_minutes`(int), `work_hours`(decimal 8,2), `source_log_ids`(json), `calculated_at`(datetime), `calculation_trigger`(string) |
| 4.2 | Model `DailyAttendance` | `app/Models/DailyAttendance.php` | HasUlids, Auditable; status enum casts |
| 4.3 | `AttendanceEngine` | `app/Services/AttendanceEngine.php` | `calculate(Employee $emp, Carbon $date)` — core calculation logic |
| 4.4 | Auto-trigger | `ClockService::clockOut()` → panggil `AttendanceEngine::calculate()` |
| 4.5 | `AttendanceController` | `app/Http/Controllers/Attendance/AttendanceController.php` | index (rekap bulanan), show (detail per karyawan) |
| 4.6 | View | `resources/views/attendance/index.blade.php` | Rekap tabel bulanan per karyawan |

**AttendanceEngine Logic:**
```
status = present   → clock_in ada, tidak terlambat
status = late      → clock_in > shift.clock_in + late_tolerance
status = early_leave → clock_out < shift.clock_out - early_leave_tolerance
status = absent    → tidak ada clock_in (dan bukan hari libur / cuti)
status = holiday   → tanggal ada di holidays table
status = leave     → ada leave_request approved untuk tanggal ini
```

**Commit:** `feat(attendance): daily attendance engine`

---

### 🟦 SLICE 5: Attendance Correction

**INPUT:** Daily attendances sudah ada
**OUTPUT:** Karyawan bisa ajukan koreksi → dept head / HR approve
**VERIFY:** Submit koreksi clock_in → status `pending` → approved → daily attendance ter-recalculate

| # | Task | File | Keterangan |
|---|---|---|---|
| 5.1 | Migration `attendance_corrections` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `date`(date), `type`(enum: clock_in/clock_out/full_day), `corrected_time`(datetime nullable), `reason`(text), `attachment`(string nullable), `status`(enum: pending/approved/rejected), `approved_by`(FK nullable), `approved_at`(datetime nullable) |
| 5.2 | Model `AttendanceCorrection` | `app/Models/AttendanceCorrection.php` | HasUlids, Auditable |
| 5.3 | `CorrectionService` | `app/Services/CorrectionService.php` | submit(), approve(), reject() → trigger recalculate |
| 5.4 | `CorrectionController` | `app/Http/Controllers/Attendance/CorrectionController.php` | index, store, approve, reject |
| 5.5 | Views | `resources/views/attendance/corrections/` | index (daftar koreksi), form (drawer submit) |

**Commit:** `feat(attendance): attendance correction + approval`

---

### 🟦 SLICE 6: Overtime Request

**INPUT:** Attendance sudah ada
**OUTPUT:** Karyawan bisa request lembur → approval → overtime_minutes masuk rekap
**VERIFY:** Submit overtime → approved → `daily_attendances.overtime_minutes` ter-update

| # | Task | File | Keterangan |
|---|---|---|---|
| 6.1 | Migration `overtime_requests` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `date`(date), `planned_start`(time), `planned_end`(time), `actual_minutes`(int nullable), `reason`(text), `status`(enum: pending/approved/rejected), `approved_by`(FK nullable), `approved_at`(datetime nullable) |
| 6.2 | Model `OvertimeRequest` | `app/Models/OvertimeRequest.php` | HasUlids, Auditable |
| 6.3 | `OvertimeService` | `app/Services/OvertimeService.php` | submit(), approve(), reject() |
| 6.4 | `OvertimeController` | `app/Http/Controllers/Attendance/OvertimeController.php` | index, store, approve, reject |
| 6.5 | Views | `resources/views/attendance/overtime/` | index + drawer form |

**Commit:** `feat(attendance): overtime request + approval`

---

### 🟦 SLICE 7: Leave Types + Leave Balance

**INPUT:** Employee data
**OUTPUT:** Admin bisa CRUD tipe cuti; saldo cuti per karyawan per tahun
**VERIFY:** Buat tipe cuti "Cuti Tahunan" (12 hari) → generate saldo untuk semua karyawan aktif

| # | Task | File | Keterangan |
|---|---|---|---|
| 7.1 | Migration `leave_types` | `database/migrations/` | `id`(ULID), `name`, `code`(UK), `default_quota`(int), `is_paid`(bool), `is_carry_forward`(bool), `max_carry_forward`(int), `requires_attachment`(bool), `min_days_advance`(int), `max_consecutive_days`(int) |
| 7.2 | Migration `leave_balances` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `leave_type_id`(FK), `year`(int), `total_quota`(int), `used`(int), `pending`(int) |
| 7.3 | Models: `LeaveType`, `LeaveBalance` | `app/Models/` | HasUlids, Auditable |
| 7.4 | `LeaveTypeService` | `app/Services/LeaveTypeService.php` | CRUD + `generateBalancesForAllEmployees()` |
| 7.5 | `LeaveTypeController` | `app/Http/Controllers/Leave/LeaveTypeController.php` | Resource (drawer pattern) |
| 7.6 | Views | `resources/views/settings/leave-types/index.blade.php` | CRUD drawer |

**Commit:** `feat(leave): leave types + balance initialization`

---

### 🟦 SLICE 8: Leave Request + Approval

**INPUT:** Leave types + balances ada
**OUTPUT:** Karyawan ajukan cuti → approval → saldo berkurang → daily_attendance = leave
**VERIFY:** Ajukan 2 hari cuti → approved → saldo berkurang 2 → attendance status = leave

| # | Task | File | Keterangan |
|---|---|---|---|
| 8.1 | Migration `leave_requests` | `database/migrations/` | `id`(ULID), `employee_id`(FK), `leave_type_id`(FK), `start_date`(date), `end_date`(date), `total_days`(int), `reason`(text), `attachment`(string nullable), `status`(enum: pending/approved/rejected), `approved_by`(FK nullable), `approved_at`(datetime nullable), `rejection_reason`(text nullable) |
| 8.2 | Migration `holidays` | `database/migrations/` | `id`(ULID), `date`(UK), `name`, `is_national`(bool), `year`(int) |
| 8.3 | Models: `LeaveRequest`, `Holiday` | `app/Models/` | HasUlids, Auditable |
| 8.4 | `LeaveService` | `app/Services/LeaveService.php` | submit() (validasi saldo, min_advance, attachment), approve() (update saldo + flag daily_attendance), reject() |
| 8.5 | `LeaveController` | `app/Http/Controllers/Leave/LeaveController.php` | index, store, approve, reject |
| 8.6 | `HolidayController` | `app/Http/Controllers/Settings/HolidayController.php` | CRUD kalender |
| 8.7 | Views | `resources/views/leave/` + `settings/holidays/` | index + form |

**Commit:** `feat(leave): leave request + approval + holiday calendar`

---

### 🟦 SLICE 9: Permissions + Policies + Tests

**INPUT:** Semua slices di atas selesai
**OUTPUT:** RBAC lengkap + test suite green

| # | Permission | Diberikan ke Role |
|---|---|---|
| `manage-shifts` | super-admin, hr-admin |
| `view-attendance` | semua role |
| `manage-attendance` | hr-admin |
| `approve-attendance` | hr-admin, dept-head |
| `manage-leave` | hr-admin |
| `approve-leave` | hr-admin, dept-head |
| `view-leave` | semua role |

**Tests yang dibuat:**
- `ShiftCrudTest` — CRUD shift (5 tests)
- `ClockInOutTest` — clock in/out + validasi duplikat (5 tests)
- `AttendanceEngineTest` — kalkulasi status: hadir, terlambat, absen (6 tests)
- `LeaveRequestTest` — submit, approve, saldo berkurang (5 tests)

**Commit:** `test(attendance): CRUD + engine + leave tests`

---

## File Structure (Target)

```
app/
├── Http/Controllers/
│   ├── Attendance/
│   │   ├── ClockController.php
│   │   ├── AttendanceController.php
│   │   ├── CorrectionController.php
│   │   └── OvertimeController.php
│   ├── Leave/
│   │   └── LeaveController.php
│   └── Settings/
│       ├── ShiftController.php
│       └── HolidayController.php
├── Models/
│   ├── Shift.php
│   ├── ScheduleAssignment.php
│   ├── ClockLog.php
│   ├── DailyAttendance.php
│   ├── AttendanceCorrection.php
│   ├── OvertimeRequest.php
│   ├── LeaveType.php
│   ├── LeaveBalance.php
│   ├── LeaveRequest.php
│   └── Holiday.php
└── Services/
    ├── ShiftService.php
    ├── ScheduleService.php
    ├── ClockService.php
    ├── AttendanceEngine.php     ← core logic
    ├── CorrectionService.php
    ├── OvertimeService.php
    ├── LeaveTypeService.php
    └── LeaveService.php

resources/views/
├── attendance/
│   ├── clock.blade.php          ← halaman absensi karyawan
│   ├── index.blade.php          ← rekap bulanan (admin)
│   ├── corrections/
│   │   └── index.blade.php
│   └── overtime/
│       └── index.blade.php
├── leave/
│   └── index.blade.php
└── settings/
    ├── shifts/
    │   └── index.blade.php
    └── holidays/
        └── index.blade.php
```

---

## Schedules & Timeline

| Minggu | Slices | Target |
|---|---|---|
| Minggu 5 | 1–3 | Shift CRUD, Schedule Assign, Clock In/Out |
| Minggu 6 | 4–6 | Daily Engine, Corrections, Overtime |
| Minggu 7 | 7–8 | Leave Types, Leave Requests, Holidays |
| Minggu 8 | 9 | RBAC, Policy, Tests + Demo |

---

## Business Rules (Penting)

| Rule | Implementasi |
|---|---|
| Tidak bisa clock in 2x sehari | `ClockService::hasCheckedInToday()` guard |
| Terlambat = clock_in > shift.clock_in + tolerance | `AttendanceEngine` kalkulasi |
| Cuti hitung hari kerja (exclude weekend + holiday) | `LeaveService::countWorkingDays()` |
| Saldo tidak bisa minus | `LeaveService::submit()` validasi |
| Approval cascade: dept-head → hr-admin (opsional) | single-level untuk MVP |
| Shift flexible: tidak ada late check | `if (shift.is_flexible) skip tolerance check` |

---

## Verification Plan

### Automated Tests
```bash
php artisan test --filter="ShiftCrud|ClockInOut|AttendanceEngine|LeaveRequest"
```

### Manual Verification
1. Login super-admin → Settings → Shifts → Buat shift "WFO" (09:00–17:00, toleransi 15 mnt)
2. Employee Management → Andi Wijaya → Assign shift WFO mulai hari ini
3. Login sebagai Andi → `/attendance` → Clock In (jam 09:20) → status "Terlambat 20 menit" ✅
4. Clock Out (jam 16:45) → status "Early Leave 15 mnt" ✅
5. `daily_attendances` table → row hari ini → `status = late`, `late_minutes = 20` ✅
6. Ajukan koreksi clock_in → Approved → daily_attendance ter-recalculate ✅
7. Ajukan cuti 2 hari → Balances berkurang → hari cuti: attendance status = leave ✅

---

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| AttendanceEngine lambat saat bulk recalculate | Queue job untuk recalculate massal |
| Timezone mismatch (clock_in timestamp) | Semua pakai `now()` server timezone, config APP_TIMEZONE |
| Leave bertepatan dengan holiday | `LeaveService::countWorkingDays()` exclude holidays |
| Karyawan tanpa shift di-assign | Guard di `ClockService`: return error jika tidak ada schedule aktif |
