# 🗄️ Database Schema — OrcaHR

> ERD dan detail tabel untuk seluruh modul.
> Database: **MySQL 8**
> Referensi: [Module Specifications](file:///z:/project/docs/MODULE_SPECIFICATIONS.md) · [Security Blueprint](file:///z:/project/docs/SECURITY_BLUEPRINT.md)

---

## Konvensi

| Konvensi | Detail |
|---|---|
| Naming | snake_case, plural untuk tabel |
| Primary Key | `id` (ULID, `char(26)` via `HasUlids` trait) |
| Foreign Key | `{table_singular}_id` (ULID, `char(26)`) |
| Timestamps | `created_at`, `updated_at` pada semua tabel |
| Soft Delete | `deleted_at` pada tabel yang butuh (employees, dll) |
| Encrypted | 🔒 Kolom terenkripsi (AES-256) |
| Hashed | #️⃣ Kolom HMAC hash (untuk search) |
| Effective Date | ⚡ Kolom effective_from / effective_to |

---

## ERD Overview (Core HR)

```mermaid
erDiagram
    users ||--o| employees : "has profile"
    employees ||--|{ employments : "has history"
    employees ||--|{ bank_accounts : "has"
    employees ||--o| employee_bpjs : "has"
    employees ||--|{ employee_documents : "has"
    departments ||--|{ positions : "has"
    departments ||--o{ departments : "parent"
    job_levels ||--|{ positions : "has"
    departments ||--|{ employments : "belongs to"
    positions ||--|{ employments : "belongs to"

    users {
        ulid id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        string remember_token
    }

    employees {
        ulid id PK
        ulid user_id FK
        string employee_number UK
        string full_name
        string email
        string personal_email_encrypted
        string phone_encrypted
        string nik_encrypted
        string nik_hash
        string npwp_encrypted
        string npwp_hash
        date birth_date
        string birth_place_encrypted
        enum gender
        enum marital_status
        enum blood_type
        enum religion
        string address_encrypted
        string photo
        timestamp deleted_at
    }

    departments {
        ulid id PK
        string name
        string code UK
        ulid parent_id FK
        ulid head_id FK
    }

    positions {
        ulid id PK
        string name
        ulid department_id FK
        ulid job_level_id FK
    }

    job_levels {
        ulid id PK
        string name
        int level
    }

    employments {
        ulid id PK
        ulid employee_id FK
        ulid department_id FK
        ulid position_id FK
        ulid job_level_id FK
        enum employment_status
        date join_date
        date end_date
        date effective_from
        date effective_to
    }

    bank_accounts {
        ulid id PK
        ulid employee_id FK
        string bank_name_encrypted
        string branch_encrypted
        string account_number_encrypted
        string account_holder_encrypted
    }

    employee_bpjs {
        ulid id PK
        ulid employee_id FK
        string bpjs_kes_encrypted
        string bpjs_tk_encrypted
        enum bpjs_class
    }

    employee_documents {
        ulid id PK
        ulid employee_id FK
        enum type
        string file_path
        date expires_at
    }
```

---

## ERD: Attendance & Leave

```mermaid
erDiagram
    shifts ||--|{ schedule_assignments : "used in"
    employees ||--|{ schedule_assignments : "has"
    employees ||--|{ clock_logs : "records"
    employees ||--|{ daily_attendances : "has"
    employees ||--|{ attendance_corrections : "requests"
    employees ||--|{ overtime_requests : "requests"
    leave_types ||--|{ leave_balances : "has"
    employees ||--|{ leave_balances : "has"
    employees ||--|{ leave_requests : "submits"
    leave_types ||--|{ leave_requests : "of type"

    shifts {
        ulid id PK
        string name
        string code UK
        time clock_in
        time clock_out
        time break_start
        time break_end
        boolean is_flexible
        int late_tolerance_minutes
        int early_leave_tolerance_minutes
    }

    schedule_assignments {
        ulid id PK
        ulid employee_id FK
        ulid shift_id FK
        date effective_from
        date effective_to
        enum type
    }

    clock_logs {
        ulid id PK
        ulid employee_id FK
        datetime timestamp
        enum type
        enum source
        string ip_address
        json location
        string photo
        boolean is_manual
        text manual_reason
    }

    daily_attendances {
        ulid id PK
        ulid employee_id FK
        date date
        ulid schedule_id FK
        datetime clock_in
        datetime clock_out
        enum status
        int late_minutes
        int early_leave_minutes
        int overtime_minutes
        decimal work_hours
        json source_log_ids
        datetime calculated_at
        string calculation_trigger
    }

    attendance_corrections {
        ulid id PK
        ulid employee_id FK
        date date
        enum type
        datetime corrected_time
        text reason
        string attachment
        enum status
        ulid approved_by FK
        datetime approved_at
    }

    overtime_requests {
        ulid id PK
        ulid employee_id FK
        date date
        time planned_start
        time planned_end
        int actual_minutes
        text reason
        enum status
        ulid approved_by FK
        datetime approved_at
    }

    leave_types {
        ulid id PK
        string name
        string code UK
        int default_quota
        boolean is_paid
        boolean is_carry_forward
        int max_carry_forward
        boolean requires_attachment
        int min_days_advance
        int max_consecutive_days
    }

    leave_balances {
        ulid id PK
        ulid employee_id FK
        ulid leave_type_id FK
        int year
        int total_quota
        int used
        int pending
    }

    leave_requests {
        ulid id PK
        ulid employee_id FK
        ulid leave_type_id FK
        date start_date
        date end_date
        int total_days
        text reason
        string attachment
        enum status
        ulid approved_by FK
        datetime approved_at
        text rejection_reason
    }

    holidays {
        ulid id PK
        date date UK
        string name
        boolean is_national
        int year
    }
```

---

## ERD: Payroll Engine

```mermaid
erDiagram
    payroll_components ||--|{ schema_components : "used in"
    payroll_schemas ||--|{ schema_components : "has"
    payroll_schemas ||--|{ employee_payroll_assignments : "assigned to"
    employees ||--|{ employee_payroll_assignments : "has"
    payroll_components ||--|{ employee_component_overrides : "overridden"
    employees ||--|{ employee_component_overrides : "has"
    payroll_periods ||--|{ payroll_runs : "has"
    employees ||--|{ payroll_runs : "has"
    payroll_runs ||--|{ payroll_run_details : "has"
    payroll_components ||--|{ payroll_run_details : "of"
    employees ||--|{ payroll_adjustments : "has"

    payroll_components {
        ulid id PK
        string name
        string code UK
        enum type
        enum category
        boolean is_taxable
        boolean is_active
    }

    payroll_schemas {
        ulid id PK
        string name
        string code UK
        text description
        boolean is_active
    }

    schema_components {
        ulid id PK
        ulid schema_id FK
        ulid component_id FK
        int sort_order
        enum formula_type
        text formula
        decimal default_value
    }

    employee_payroll_assignments {
        ulid id PK
        ulid employee_id FK
        ulid schema_id FK
        decimal base_salary
        date effective_from
        date effective_to
    }

    employee_component_overrides {
        ulid id PK
        ulid employee_id FK
        ulid component_id FK
        decimal custom_value
        date effective_from
        date effective_to
    }

    payroll_periods {
        ulid id PK
        int month
        int year
        enum status
        datetime locked_at
        ulid locked_by FK
    }

    payroll_runs {
        ulid id PK
        ulid period_id FK
        ulid employee_id FK
        ulid schema_id FK
        decimal gross_salary
        decimal total_deductions
        decimal net_salary
        decimal tax_amount
        enum status
    }

    payroll_run_details {
        ulid id PK
        ulid payroll_run_id FK
        ulid component_id FK
        enum component_type
        decimal amount
        text formula_used
        text notes
    }

    payroll_adjustments {
        ulid id PK
        ulid employee_id FK
        ulid target_period_id FK
        ulid applied_period_id FK
        ulid component_id FK
        decimal amount
        text reason
        ulid approved_by FK
    }
```

---

## ERD: Recruitment & Onboarding

```mermaid
erDiagram
    manpower_requests ||--|{ job_postings : "creates"
    job_postings ||--|{ applicants : "receives"
    applicants ||--|{ interviews : "has"
    employees ||--|{ onboarding_checklists : "assigned"
    onboarding_templates ||--|{ onboarding_checklists : "from"

    manpower_requests {
        ulid id PK
        ulid department_id FK
        ulid position_id FK
        ulid requested_by FK
        int quantity
        enum employment_type
        text reason
        date expected_join_date
        enum status
        ulid approved_by FK
        datetime approved_at
    }

    job_postings {
        ulid id PK
        ulid manpower_request_id FK
        string title
        ulid department_id FK
        ulid position_id FK
        text description
        text requirements
        enum employment_type
        string location
        decimal salary_range_min
        decimal salary_range_max
        enum status
        datetime published_at
        datetime closed_at
    }

    applicants {
        ulid id PK
        ulid job_posting_id FK
        string full_name
        string email
        string phone_encrypted
        string resume
        text cover_letter
        enum status
        text notes
    }

    interviews {
        ulid id PK
        ulid applicant_id FK
        ulid interviewer_id FK
        datetime scheduled_at
        enum type
        string location
        text feedback
        int rating
        enum status
    }

    onboarding_templates {
        ulid id PK
        string name
        ulid position_id FK
        json items
        boolean is_active
    }

    onboarding_checklists {
        ulid id PK
        ulid employee_id FK
        ulid template_id FK
        string item_name
        boolean is_completed
        datetime completed_at
        ulid completed_by FK
        date due_date
    }
```

---

## ERD: Project Management

```mermaid
erDiagram
    projects ||--|{ board_columns : "has"
    projects ||--|{ project_members : "has"
    projects ||--|{ milestones : "has"
    board_columns ||--|{ tasks : "contains"
    tasks ||--|{ task_comments : "has"
    tasks ||--|{ task_attachments : "has"
    tasks ||--|{ task_activities : "logs"
    tasks ||--o{ tasks : "subtasks"
    milestones ||--o{ tasks : "groups"

    projects {
        ulid id PK
        string name
        string code UK
        text description
        ulid owner_id FK
        enum visibility
        ulid department_id FK
        enum status
        date start_date
        date target_date
    }

    project_members {
        ulid id PK
        ulid project_id FK
        ulid employee_id FK
        enum role
    }

    board_columns {
        ulid id PK
        ulid project_id FK
        string name
        int sort_order
        string color
        boolean is_done_column
    }

    milestones {
        ulid id PK
        ulid project_id FK
        string name
        text description
        date start_date
        date end_date
        enum status
    }

    tasks {
        ulid id PK
        ulid project_id FK
        ulid column_id FK
        string title
        text description
        enum type
        enum priority
        ulid assignee_id FK
        ulid reporter_id FK
        ulid parent_task_id FK
        ulid milestone_id FK
        date due_date
        decimal estimated_hours
        json labels
        int sort_order
        datetime completed_at
    }

    task_comments {
        ulid id PK
        ulid task_id FK
        ulid author_id FK
        text content
    }

    task_attachments {
        ulid id PK
        ulid task_id FK
        string file_path
        string file_name
        int file_size
        ulid uploaded_by FK
    }

    task_activities {
        ulid id PK
        ulid task_id FK
        ulid actor_id FK
        string action
        string old_value
        string new_value
    }
```

---

## ERD: System (Audit, Announcement, Settings)

```mermaid
erDiagram
    audit_logs {
        ulid id PK
        string auditable_type
        ulid auditable_id
        enum action
        ulid actor_id FK
        datetime timestamp
        json old_values
        json new_values
        text reason
        string ip_address
        string user_agent
    }

    announcements {
        ulid id PK
        string title
        text content
        enum type
        enum target
        json target_ids
        datetime published_at
        datetime expires_at
        boolean is_pinned
        ulid created_by FK
    }

    settings {
        ulid id PK
        string group
        string key UK
        text value
        string type
    }

    notifications {
        ulid id PK
        string type
        ulid notifiable_id
        string notifiable_type
        json data
        datetime read_at
    }
```

---

## Index Strategy

### High-Priority Indexes

| Tabel | Index | Tipe | Alasan |
|---|---|---|---|
| `employees` | `nik_hash` | UNIQUE | Cek duplikat NIK |
| `employees` | `npwp_hash` | INDEX | Matching NPWP |
| `employees` | `employee_number` | UNIQUE | Lookup cepat |
| `employments` | `(employee_id, effective_from, effective_to)` | COMPOSITE | Query effective-dated |
| `clock_logs` | `(employee_id, timestamp)` | COMPOSITE | Query per hari |
| `daily_attendances` | `(employee_id, date)` | UNIQUE | 1 record per karyawan per hari |
| `schedule_assignments` | `(employee_id, effective_from, effective_to)` | COMPOSITE | Query shift berlaku |
| `leave_balances` | `(employee_id, leave_type_id, year)` | UNIQUE | 1 balance per tipe per tahun |
| `payroll_runs` | `(period_id, employee_id)` | UNIQUE | 1 run per karyawan per period |
| `payroll_periods` | `(month, year)` | UNIQUE | 1 period per bulan |
| `tasks` | `(project_id, column_id, sort_order)` | COMPOSITE | Kanban ordering |
| `audit_logs` | `(auditable_type, auditable_id)` | COMPOSITE | Query per record |
| `audit_logs` | `(actor_id, timestamp)` | COMPOSITE | Query per user |

### Soft-Delete Tables

Tabel yang menggunakan `deleted_at`:
- `employees`
- `users`
- `departments`
- `positions`

---

## Migration Order

> Urutan migration berdasarkan foreign key dependency.

```
01. users
02. settings
03. departments
04. job_levels
05. positions
06. employees
07. employments
08. bank_accounts
09. employee_bpjs
10. employee_documents
─── Attendance ───
11. shifts
12. schedule_assignments
13. clock_logs
14. daily_attendances
15. attendance_corrections
16. overtime_requests
─── Leave ───
17. leave_types
18. leave_balances
19. leave_requests
20. holidays
─── Payroll ───
21. payroll_components
22. payroll_schemas
23. schema_components
24. employee_payroll_assignments
25. employee_component_overrides
26. payroll_periods
27. payroll_runs
28. payroll_run_details
29. payroll_adjustments
─── Recruitment ───
30. manpower_requests
31. job_postings
32. applicants
33. interviews
34. onboarding_templates
35. onboarding_checklists
─── Project Management ───
36. projects
37. project_members
38. board_columns
39. milestones
40. tasks
41. task_comments
42. task_attachments
43. task_activities
─── System ───
44. announcements
45. audit_logs
46. notifications
```

**Total: 46 tabel**

---

## Encrypted Columns Summary

| Tabel | Kolom | Encrypted | HMAC Hash |
|---|---|---|---|
| `employees` | `nik` | ✅ `nik_encrypted` | ✅ `nik_hash` |
| `employees` | `npwp` | ✅ `npwp_encrypted` | ✅ `npwp_hash` |
| `employees` | `personal_email` | ✅ `personal_email_encrypted` | ❌ |
| `employees` | `phone` | ✅ `phone_encrypted` | ❌ |
| `employees` | `birth_place` | ✅ `birth_place_encrypted` | ❌ |
| `employees` | `address` | ✅ `address_encrypted` | ❌ |
| `bank_accounts` | `bank_name` | ✅ `bank_name_encrypted` | ❌ |
| `bank_accounts` | `branch` | ✅ `branch_encrypted` | ❌ |
| `bank_accounts` | `account_number` | ✅ `account_number_encrypted` | ❌ |
| `bank_accounts` | `account_holder` | ✅ `account_holder_encrypted` | ❌ |
| `employee_bpjs` | `bpjs_kesehatan` | ✅ `bpjs_kes_encrypted` | ❌ |
| `employee_bpjs` | `bpjs_ketenagakerjaan` | ✅ `bpjs_tk_encrypted` | ❌ |
| `applicants` | `phone` | ✅ `phone_encrypted` | ❌ |

---

*Dibuat: 4 Maret 2026*
*Total tabel: 46*
*Database: MySQL 8 • Single Company*
