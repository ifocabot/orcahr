# 🛠️ Development Standards — OrcaHR

> Coding conventions, folder structure, dan workflow untuk solo developer.
> Stack: **Laravel 12 + Blade + Alpine.js + Tailwind CSS + MySQL 8**
> Pattern: **Service Layer** (API-ready)

---

## Project Structure

```
orcahr/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Employee/
│   │   │   ├── Attendance/
│   │   │   ├── Leave/
│   │   │   ├── Payroll/
│   │   │   ├── ESS/
│   │   │   ├── Recruitment/
│   │   │   ├── Project/
│   │   │   └── System/
│   │   ├── Middleware/
│   │   └── Requests/           # Form Requests (validation)
│   ├── Models/
│   ├── Services/               # ⭐ Business logic (reusable untuk API nanti)
│   ├── Jobs/                   # Queue jobs (recalculation, etc)
│   ├── Events/                 # Domain events
│   ├── Listeners/              # Event listeners
│   ├── Policies/               # Authorization policies
│   ├── Observers/              # Model observers (audit)
│   ├── Traits/                 # Shared traits (Encryptable, etc)
│   ├── Helpers/                # Encryption, HMAC helpers
│   └── View/
│       └── Components/         # Blade components
├── resources/
│   ├── views/
│   │   ├── layouts/            # Base layouts (app, auth, ess)
│   │   ├── components/         # Blade components (button, modal, table, etc)
│   │   ├── auth/               # Login, forgot password
│   │   ├── dashboard/          # Dashboard per role
│   │   ├── employees/          # Employee CRUD views
│   │   ├── attendance/         # Attendance views
│   │   ├── leave/              # Leave views
│   │   ├── payroll/            # Payroll views
│   │   ├── ess/                # ESS portal views
│   │   ├── recruitment/        # Recruitment views
│   │   ├── projects/           # Project management views
│   │   └── settings/           # System settings
│   ├── css/
│   │   └── app.css             # Tailwind entry point
│   └── js/
│       └── app.js              # Alpine.js + custom JS
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── web.php                 # All web routes
├── tests/
│   ├── Unit/
│   └── Feature/
├── docs/                       # Project documentation (ini)
└── vite.config.js              # Vite (Tailwind + Alpine build)
```

---

## Core Architecture: Service Layer

> **Aturan #1:** Controller TIDAK boleh punya business logic.
> **Aturan #2:** Semua logic ada di Service class.

```php
// ❌ WRONG: Logic di controller
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // validate, encrypt, hash, save — semua di sini
        $employee = new Employee();
        $employee->nik_encrypted = encrypt($request->nik);
        $employee->nik_hash = hmac($request->nik);
        $employee->save();
        return redirect()->route('employees.index');
    }
}

// ✅ CORRECT: Controller tipis, logic di Service
class EmployeeController extends Controller
{
    public function store(StoreEmployeeRequest $request, EmployeeService $service)
    {
        $employee = $service->create($request->validated());
        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Karyawan berhasil ditambahkan');
    }
}

// Service: business logic, reusable
class EmployeeService
{
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::create([
                'full_name' => $data['full_name'],
                'nik_encrypted' => encrypt($data['nik']),
                'nik_hash' => hmac_hash($data['nik']),
                // ...
            ]);

            // Side effects: audit log, notifications, etc.
            return $employee;
        });
    }
}
```

### Kenapa Service Layer?

```
SEKARANG (Blade):
Route → Controller → Service → Model
             ↓
         Blade View

NANTI (Vue):
API Route → ApiController → Service (SAMA!) → Model
                  ↓
            JSON Response
```

**Service class ditulis sekali, dipakai dua kali.**

---

## Git Workflow

### Branch Strategy

```
main            ← Production-ready
└── develop     ← Integration branch
    ├── feature/employee-crud
    ├── feature/attendance-engine
    ├── feature/payroll-schema
    └── fix/leave-balance-calc
```

### Branch Naming

| Prefix | Untuk | Contoh |
|---|---|---|
| `feature/` | Fitur baru | `feature/payroll-run` |
| `fix/` | Bug fix | `fix/nik-duplicate-check` |
| `refactor/` | Refactoring | `refactor/attendance-service` |
| `docs/` | Dokumentasi | `docs/api-specs` |

### Commit Convention

Format: `type(scope): message`

| Type | Penggunaan |
|---|---|
| `feat` | Fitur baru |
| `fix` | Bug fix |
| `refactor` | Refactoring tanpa ubah behavior |
| `docs` | Dokumentasi |
| `test` | Tambah/ubah test |
| `chore` | Maintenance (deps update, config) |
| `style` | Formatting, non-logic change |

**Contoh:**
```
feat(payroll): add formula engine for schema components
fix(attendance): recalc not triggered on correction approval
refactor(employee): extract encryption to trait
docs(api): add payroll endpoint specifications
```

### Workflow Solo Developer

```
1. Checkout develop → buat branch feature/*
2. Develop + commit frequently
3. Test locally
4. Merge ke develop
5. Setelah fase selesai → merge develop ke main
6. Tag release (v1.0.0, v1.1.0, ...)
```

---

## Coding Conventions

### PHP / Laravel

```php
// Naming:
// - Model:      singular PascalCase          → Employee, PayrollRun
// - Controller: {Model}Controller            → EmployeeController
// - Service:    {Model}Service               → EmployeeService
// - Request:    {Action}{Model}Request       → StoreEmployeeRequest
// - Job:        {Action}{Model}Job           → RecalculateAttendanceJob
// - Event:      {Model}{Action}Event         → LeaveRequestApprovedEvent
// - Policy:     {Model}Policy                → EmployeePolicy
// - Trait:      {Capability}                 → Encryptable, Auditable
// - Observer:   {Model}Observer              → EmployeeObserver
```

### Blade Templates

```blade
{{-- Component-based Blade --}}
<x-app-layout>
    <x-page-header title="Daftar Karyawan" />

    <x-data-table :items="$employees" :columns="$columns">
        <x-slot:actions>
            <x-button href="{{ route('employees.create') }}">Tambah</x-button>
        </x-slot:actions>
    </x-data-table>
</x-app-layout>

{{-- Alpine.js untuk interaktif --}}
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>Content</div>
</div>
```

### File Naming

| Tipe | Convention | Contoh |
|---|---|---|
| Blade view | kebab-case | `employee-form.blade.php` |
| Component | kebab-case | `data-table.blade.php` |
| Route name | dot-separated | `employees.index`, `payroll.runs.show` |

---

## Testing Strategy

### Backend (PHP)

| Tipe | Tools | Coverage |
|---|---|---|
| **Unit** | PHPUnit | Services, helpers, calculations |
| **Feature** | PHPUnit | HTTP endpoints, auth, permissions |
| **Critical** | — | Payroll calculation, attendance recalc, encryption |

```bash
# Run all tests
php artisan test

# Run specific module
php artisan test --filter=PayrollTest
```

### Minimum Test Coverage (Priority)

1. 🔴 Payroll calculation engine (Service)
2. 🔴 Attendance recalculation (Service)
3. 🔴 Encryption/decryption helpers
4. 🟡 Leave balance calculation (Service)
5. 🟡 RBAC permission checks
6. 🟡 Approval workflows (Service)

> [!TIP]
> Karena logic ada di Service, **unit test Service = test business logic**.
> Feature test = test HTTP + auth + validation.

---

## Environment Setup

### .env

```env
APP_NAME=OrcaHR
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=orcahr
DB_USERNAME=root
DB_PASSWORD=

ENCRYPTION_KEY=       # Untuk field-level encryption (BUKAN APP_KEY)
HMAC_KEY=             # Untuk HMAC hashing

QUEUE_CONNECTION=redis
```

---

## Definition of Done (Per Fitur)

- [ ] Controller tipis, logic di Service ✅
- [ ] Blade view berfungsi
- [ ] RBAC permission diterapkan (middleware + @can)
- [ ] Data sensitif dienkripsi (jika applicable)
- [ ] Audit log tercatat (jika applicable)
- [ ] Validation di Form Request
- [ ] Error handling + flash messages
- [ ] Mobile-responsive (basic)
- [ ] Test untuk Service class (minimal happy path)

---

*Dibuat: 4 Maret 2026*
