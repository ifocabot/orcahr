# 🧪 Testing Strategy — OrcaHR

> Strategi testing untuk solo developer.
> Stack: **Laravel 11 + PHPUnit** • Pattern: **Service Layer**

---

## Filosofi Testing

```
Test yang paling penting = test yang mencegah bug MAHAL.

Bug payroll salah hitung gaji   → MAHAL (harus koreksi, reputasi jatuh)
Bug tombol warna salah           → MURAH (fix 5 menit)

Fokus test ke yang MAHAL.
```

> [!IMPORTANT]
> Kamu solo developer. **Jangan test semuanya — test yang PENTING.**
> Target: **critical path coverage**, bukan 100% coverage.

---

## Test Pyramid

```
         ╱ ╲
        ╱ E2E ╲         ← Sedikit (nanti, Fase 7)
       ╱───────╲
      ╱ Feature  ╲      ← HTTP test: route → response
     ╱─────────────╲
    ╱   Unit Test    ╲   ← Service class, helpers
   ╱───────────────────╲
```

| Layer | Tools | Apa yang di-test | Jumlah |
|---|---|---|---|
| **Unit** | PHPUnit | Service methods, helpers, calculations | Banyak |
| **Feature** | PHPUnit | HTTP request → response, auth, validation | Sedang |
| **E2E** | Browser (nanti) | Critical user flows end-to-end | Sedikit |

---

## Prioritas Test per Modul

### 🔴 CRITICAL — Wajib Test (Salah = Uang / Data Rusak)

| Modul | Yang Di-test | Test Type |
|---|---|---|
| **Payroll Calculation** | Formula engine, gross/net/tax calculation | Unit |
| **Payroll Run** | Multi-schema, component override, attendance integration | Unit + Feature |
| **Payroll Lock** | Locked period tidak bisa diedit, adjustment flow | Feature |
| **Attendance Recalc** | Event-driven recalculation, dirty date marking | Unit |
| **Encryption Helper** | Encrypt → decrypt roundtrip, HMAC consistency | Unit |
| **Leave Balance** | Deduction on approve, return on reject, carry-forward | Unit |

### 🟡 IMPORTANT — Sebaiknya Test (Salah = UX Buruk / Security Issue)

| Modul | Yang Di-test | Test Type |
|---|---|---|
| **RBAC** | Role X tidak bisa akses route Y | Feature |
| **Approval Flow** | Pending → approve/reject, side effects triggered | Feature |
| **ESS Data Isolation** | Employee hanya lihat data sendiri | Feature |
| **Employee CRUD** | Create, update, soft-delete, restore | Feature |
| **Clock In/Out** | Duplicate prevention, timezone handling | Feature |

### 🟢 NICE TO HAVE — Test Kalau Sempat

| Modul | Yang Di-test | Test Type |
|---|---|---|
| Announcement | CRUD, target filtering | Feature |
| Project Management | Board operations, task CRUD | Feature |
| Reports | Export generates file | Feature |

---

## Test Pattern: AAA (Arrange-Act-Assert)

```php
/** @test */
public function it_calculates_net_salary_correctly()
{
    // Arrange
    $employee = Employee::factory()->create();
    $schema = PayrollSchema::factory()
        ->withComponents([
            ['code' => 'GAPOK', 'type' => 'earning', 'formula_type' => 'fixed_value'],
            ['code' => 'POT-BPJS', 'type' => 'deduction', 'formula_type' => 'percentage'],
        ])
        ->create();

    // Act
    $result = $this->payrollService->calculateForEmployee($employee, $schema, $period);

    // Assert
    $this->assertEquals(5_000_000, $result->gross_salary);
    $this->assertEquals(200_000, $result->total_deductions);
    $this->assertEquals(4_800_000, $result->net_salary);
}
```

---

## Contoh Test per Layer

### Unit Test — Service Class

```php
// tests/Unit/Services/PayrollServiceTest.php

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PayrollService::class);
    }

    /** @test */
    public function fixed_value_component_uses_default_or_override()
    {
        // Arrange: employee with override on GAPOK
        // Act: calculate
        // Assert: override value used, not default
    }

    /** @test */
    public function percentage_component_calculates_from_base_salary()
    {
        // 2% of 5.000.000 = 100.000
    }

    /** @test */
    public function attendance_based_component_reads_daily_attendance()
    {
        // Employee late 3x → deduction = 3 × per_late_penalty
    }

    /** @test */
    public function locked_period_cannot_be_modified()
    {
        // Expect exception when modifying locked period
    }
}
```

### Unit Test — Encryption Helper

```php
// tests/Unit/Helpers/EncryptionHelperTest.php

/** @test */
public function encrypt_decrypt_roundtrip()
{
    $original = '3173012345678901';
    $encrypted = encrypt_field($original);
    $decrypted = decrypt_field($encrypted);

    $this->assertNotEquals($original, $encrypted);
    $this->assertEquals($original, $decrypted);
}

/** @test */
public function hmac_hash_is_deterministic()
{
    $value = '3173012345678901';
    $hash1 = hmac_hash($value);
    $hash2 = hmac_hash($value);

    $this->assertEquals($hash1, $hash2);
}

/** @test */
public function hmac_hash_differs_for_different_inputs()
{
    $hash1 = hmac_hash('3173012345678901');
    $hash2 = hmac_hash('3173012345678902');

    $this->assertNotEquals($hash1, $hash2);
}
```

### Feature Test — RBAC

```php
// tests/Feature/EmployeeAccessTest.php

/** @test */
public function hr_admin_can_view_employees()
{
    $user = User::factory()->create();
    $user->assignRole('HR Admin');

    $this->actingAs($user)
        ->get(route('employees.index'))
        ->assertOk();
}

/** @test */
public function employee_cannot_view_employee_list()
{
    $user = User::factory()->create();
    $user->assignRole('Employee');

    $this->actingAs($user)
        ->get(route('employees.index'))
        ->assertForbidden();
}

/** @test */
public function employee_can_only_view_own_profile()
{
    $user = User::factory()->create();
    $user->assignRole('Employee');
    $otherEmployee = Employee::factory()->create();

    $this->actingAs($user)
        ->get(route('ess.profile'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('employees.show', $otherEmployee))
        ->assertForbidden();
}
```

### Feature Test — Approval Flow

```php
// tests/Feature/LeaveApprovalTest.php

/** @test */
public function approved_leave_deducts_balance()
{
    // Arrange: employee with 12 days cuti tahunan
    // Act: submit 3-day leave → approve
    // Assert: balance = 9
}

/** @test */
public function rejected_leave_returns_balance()
{
    // Arrange: employee with 12 days, pending leave (balance shows 9)
    // Act: reject
    // Assert: balance back to 12
}

/** @test */
public function leave_without_sufficient_balance_is_rejected()
{
    // Arrange: employee with 2 days remaining
    // Act: submit 5-day leave
    // Assert: validation error
}
```

---

## Test Data Strategy

### Factories

| Factory | Key Traits |
|---|---|
| `UserFactory` | Default: Employee role |
| `EmployeeFactory` | With encrypted fields, linked to user |
| `PayrollSchemaFactory` | `withComponents()` for quick schema+components |
| `DailyAttendanceFactory` | `present()`, `late()`, `absent()` |
| `LeaveRequestFactory` | `pending()`, `approved()`, `rejected()` |
| `PayrollRunFactory` | `draft()`, `finalized()` |

### Seeders vs Factories

| Gunakan | Untuk |
|---|---|
| **Factories** | Tests → data bersih per test, `RefreshDatabase` |
| **Seeders** | Demo/staging → data realistis untuk manual QA |

---

## Kapan Menulis Test

```
1. Buat Service method
2. Test happy path (minimal 1)
3. Test edge case kritis (jika ada)
4. Lanjut ke fitur berikutnya

Jangan: tulis semua test dulu baru coding.
Jangan: skip test untuk fitur critical.
```

### Per Fase — Minimum Test Requirement

| Fase | Minimum Tests |
|---|---|
| **Fase 1** | Encryption helper (3), Employee CRUD (5), RBAC (5) |
| **Fase 2** | Attendance recalc (5), Leave balance (5), Approval flow (5) |
| **Fase 3** | Payroll calculation (10+), Lock mechanism (3), Adjustment (3) |
| **Fase 4** | Data isolation ESS (5) |
| **Fase 5-6** | Happy path per feature (2-3 each) |
| **Fase 7** | Bug regression tests |

---

## Running Tests

```bash
# Semua test
php artisan test

# Specific module
php artisan test --filter=Payroll
php artisan test --filter=AttendanceRecalc

# Specific test class
php artisan test --filter=PayrollServiceTest

# Dengan coverage (butuh Xdebug/PCOV)
php artisan test --coverage --min=60

# Parallel (lebih cepat)
php artisan test --parallel
```

---

## CI/CD (Nanti, Fase 7)

```yaml
# .github/workflows/test.yml (contoh)
on: push
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: php artisan test
```

> Untuk sekarang: **cukup jalankan `php artisan test` sebelum merge ke develop.**

---

*Dibuat: 4 Maret 2026*
*Minimum target: ~50 tests di akhir Fase 3*
