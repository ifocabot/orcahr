# 🔌 API Specifications — OrcaHR

> **📌 Status: REFERENSI UNTUK MIGRASI VUE (FASE MENDATANG)**
> Saat ini OrcaHR menggunakan **Blade + Alpine.js** dengan web routes.
> Dokumen ini menjadi blueprint saat migrasi ke **Laravel API + Vue.js SPA** nanti.
>
> Auth: **Laravel Sanctum** (cookie-based SPA auth) — untuk fase Vue

---

## Konvensi API

### Base URL
```
/api/v1
```

### Response Format (Standar)

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Success (Paginated):**
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"],
    "nik": ["NIK already exists"]
  }
}
```

### HTTP Status Codes

| Code | Penggunaan |
|---|---|
| `200` | Success (GET, PUT, PATCH) |
| `201` | Created (POST) |
| `204` | No Content (DELETE) |
| `400` | Bad Request |
| `401` | Unauthorized (not logged in) |
| `403` | Forbidden (no permission) |
| `404` | Not Found |
| `422` | Validation Error |
| `429` | Too Many Requests (rate limit) |
| `500` | Server Error |

### Query Parameters (Standar)

| Parameter | Tipe | Contoh | Deskripsi |
|---|---|---|---|
| `page` | int | `?page=2` | Pagination |
| `per_page` | int | `?per_page=25` | Items per page (max 100) |
| `search` | string | `?search=john` | Global search |
| `sort` | string | `?sort=-created_at` | Sort (`-` = desc) |
| `filter[field]` | string | `?filter[status]=active` | Filter by field |
| `include` | string | `?include=department,position` | Eager load relations |

### Naming Convention

| Pattern | Contoh |
|---|---|
| List | `GET /api/v1/employees` |
| Show | `GET /api/v1/employees/{id}` |
| Create | `POST /api/v1/employees` |
| Update | `PUT /api/v1/employees/{id}` |
| Delete | `DELETE /api/v1/employees/{id}` |
| Action | `POST /api/v1/employees/{id}/activate` |
| Nested | `GET /api/v1/employees/{id}/documents` |

---

## Auth Endpoints

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Login → set Sanctum cookie | ❌ |
| `POST` | `/api/v1/auth/logout` | Logout → revoke token | ✅ |
| `GET` | `/api/v1/auth/me` | Get current user + role + permissions | ✅ |
| `POST` | `/api/v1/auth/forgot-password` | Send password reset email | ❌ |
| `POST` | `/api/v1/auth/reset-password` | Reset password with token | ❌ |
| `PUT` | `/api/v1/auth/change-password` | Change own password | ✅ |

---

## Module 0: Foundation

### Users & Roles
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/users` | List users | manage-users |
| `POST` | `/api/v1/users` | Create user | manage-users |
| `PUT` | `/api/v1/users/{id}` | Update user | manage-users |
| `DELETE` | `/api/v1/users/{id}` | Delete user | manage-users |
| `GET` | `/api/v1/roles` | List roles | manage-users |
| `POST` | `/api/v1/users/{id}/assign-role` | Assign role | manage-users |

### Settings
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/settings` | Get all settings | system-settings |
| `PUT` | `/api/v1/settings` | Update settings (batch) | system-settings |
| `GET` | `/api/v1/settings/{group}` | Get settings by group | system-settings |

### Audit Log
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/audit-logs` | List audit logs (paginated) | view-audit |
| `GET` | `/api/v1/audit-logs/{auditable_type}/{id}` | Logs for specific record | view-audit |

### Dashboard
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/dashboard` | Dashboard data (role-based) | authenticated |

---

## Module 1: Employee Management

### Employees
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/employees` | List employees | view-employees |
| `GET` | `/api/v1/employees/{id}` | Show employee detail | view-employees |
| `POST` | `/api/v1/employees` | Create employee | manage-employees |
| `PUT` | `/api/v1/employees/{id}` | Update employee | manage-employees |
| `DELETE` | `/api/v1/employees/{id}` | Soft-delete employee | manage-employees |
| `GET` | `/api/v1/employees/{id}/employment-history` | Employment history (effective-dated) | view-employees |
| `POST` | `/api/v1/employees/{id}/employment` | Add new employment record | manage-employees |
| `GET` | `/api/v1/employees/{id}/documents` | List employee documents | view-employees |
| `POST` | `/api/v1/employees/{id}/documents` | Upload document | manage-employees |
| `DELETE` | `/api/v1/employees/{id}/documents/{docId}` | Delete document | manage-employees |
| `GET` | `/api/v1/employees/{id}/documents/{docId}/download` | Download (signed URL) | view-employees |
| `GET` | `/api/v1/employees/export` | Export CSV/Excel | view-employees |

### Organisation
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/departments` | List departments | authenticated |
| `POST` | `/api/v1/departments` | Create department | manage-employees |
| `PUT` | `/api/v1/departments/{id}` | Update department | manage-employees |
| `DELETE` | `/api/v1/departments/{id}` | Delete department | manage-employees |
| `GET` | `/api/v1/departments/tree` | Department hierarchy (tree) | authenticated |
| `GET` | `/api/v1/positions` | List positions | authenticated |
| `POST` | `/api/v1/positions` | Create position | manage-employees |
| `PUT` | `/api/v1/positions/{id}` | Update position | manage-employees |
| `GET` | `/api/v1/job-levels` | List job levels | authenticated |
| `POST` | `/api/v1/job-levels` | Create job level | manage-employees |

---

## Module 2: Attendance

### Shifts & Schedules
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/shifts` | List shifts | manage-attendance |
| `POST` | `/api/v1/shifts` | Create shift | manage-attendance |
| `PUT` | `/api/v1/shifts/{id}` | Update shift | manage-attendance |
| `GET` | `/api/v1/schedule-assignments` | List assignments | manage-attendance |
| `POST` | `/api/v1/schedule-assignments` | Assign shift (effective-dated) | manage-attendance |

### Clock
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `POST` | `/api/v1/attendance/clock-in` | Clock in | authenticated |
| `POST` | `/api/v1/attendance/clock-out` | Clock out | authenticated |
| `GET` | `/api/v1/attendance/today` | Today's status for current user | authenticated |

### Daily Attendance
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/attendances` | List daily attendances | view-attendance |
| `GET` | `/api/v1/attendances/recap` | Monthly recap | view-attendance |
| `GET` | `/api/v1/attendances/department/{id}` | Bulk view per department | view-attendance |
| `POST` | `/api/v1/attendances/recalculate` | Trigger recalculation (admin) | manage-attendance |

### Corrections & Overtime
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/attendance-corrections` | List corrections | view-attendance |
| `POST` | `/api/v1/attendance-corrections` | Submit correction | authenticated |
| `POST` | `/api/v1/attendance-corrections/{id}/approve` | Approve correction | approve-attendance |
| `POST` | `/api/v1/attendance-corrections/{id}/reject` | Reject correction | approve-attendance |
| `GET` | `/api/v1/overtime-requests` | List OT requests | view-attendance |
| `POST` | `/api/v1/overtime-requests` | Submit OT request | authenticated |
| `POST` | `/api/v1/overtime-requests/{id}/approve` | Approve OT | approve-attendance |
| `POST` | `/api/v1/overtime-requests/{id}/reject` | Reject OT | approve-attendance |

---

## Module 3: Leave Management

| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/leave-types` | List leave types | authenticated |
| `POST` | `/api/v1/leave-types` | Create leave type | manage-leave |
| `PUT` | `/api/v1/leave-types/{id}` | Update leave type | manage-leave |
| `GET` | `/api/v1/leave-balances` | List balances (filterable) | view-leave |
| `GET` | `/api/v1/leave-balances/me` | My balances | authenticated |
| `GET` | `/api/v1/leave-requests` | List requests (filterable) | view-leave |
| `POST` | `/api/v1/leave-requests` | Submit leave request | authenticated |
| `POST` | `/api/v1/leave-requests/{id}/approve` | Approve leave | approve-leave |
| `POST` | `/api/v1/leave-requests/{id}/reject` | Reject leave | approve-leave |
| `POST` | `/api/v1/leave-requests/{id}/cancel` | Cancel own request | authenticated |
| `GET` | `/api/v1/leave-requests/calendar` | Team calendar view | view-leave |
| `GET` | `/api/v1/holidays` | List holidays | authenticated |
| `POST` | `/api/v1/holidays` | Create holiday | manage-leave |
| `PUT` | `/api/v1/holidays/{id}` | Update holiday | manage-leave |

---

## Module 4: Payroll Engine

### Setup
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/payroll/components` | List components | manage-payroll |
| `POST` | `/api/v1/payroll/components` | Create component | manage-payroll |
| `PUT` | `/api/v1/payroll/components/{id}` | Update component | manage-payroll |
| `GET` | `/api/v1/payroll/schemas` | List schemas | manage-payroll |
| `POST` | `/api/v1/payroll/schemas` | Create schema | manage-payroll |
| `PUT` | `/api/v1/payroll/schemas/{id}` | Update schema | manage-payroll |
| `POST` | `/api/v1/payroll/schemas/{id}/components` | Add component to schema | manage-payroll |
| `PUT` | `/api/v1/payroll/schemas/{id}/components/{cid}` | Update schema component | manage-payroll |

### Assignment
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/payroll/assignments` | List employee assignments | manage-payroll |
| `POST` | `/api/v1/payroll/assignments` | Assign schema (effective-dated) | manage-payroll |
| `GET` | `/api/v1/payroll/overrides` | List overrides | manage-payroll |
| `POST` | `/api/v1/payroll/overrides` | Create override | manage-payroll |

### Processing
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/payroll/periods` | List periods | manage-payroll |
| `POST` | `/api/v1/payroll/periods` | Create/open period | manage-payroll |
| `POST` | `/api/v1/payroll/periods/{id}/run` | Run payroll calculation | run-payroll |
| `GET` | `/api/v1/payroll/periods/{id}/results` | Results of payroll run | manage-payroll |
| `GET` | `/api/v1/payroll/periods/{id}/results/{empId}` | Detail per employee | manage-payroll |
| `PUT` | `/api/v1/payroll/periods/{id}/results/{empId}` | Adjust before lock | manage-payroll |
| `POST` | `/api/v1/payroll/periods/{id}/lock` | Lock period | run-payroll |
| `POST` | `/api/v1/payroll/periods/{id}/unlock` | Unlock period | super-admin |

### Output
| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/payroll/periods/{id}/slip/{empId}` | Download slip PDF | view-payslip |
| `GET` | `/api/v1/payroll/periods/{id}/recap` | Payroll recap | manage-payroll |
| `GET` | `/api/v1/payroll/periods/{id}/bank-export` | Bank transfer export | manage-payroll |
| `POST` | `/api/v1/payroll/adjustments` | Create adjustment | manage-payroll |

---

## Module 5: ESS (Employee Self-Service)

> Semua endpoint ESS hanya mengakses data **user sendiri**.

| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/ess/profile` | My profile | authenticated |
| `PUT` | `/api/v1/ess/profile` | Update request (needs approval) | authenticated |
| `GET` | `/api/v1/ess/attendance` | My attendance recap | authenticated |
| `GET` | `/api/v1/ess/leave-balance` | My leave balances | authenticated |
| `GET` | `/api/v1/ess/payslips` | My payslip list | authenticated |
| `GET` | `/api/v1/ess/payslips/{periodId}` | Download my payslip | authenticated |
| `GET` | `/api/v1/ess/requests` | All my requests (leave, OT, correction) | authenticated |
| `GET` | `/api/v1/ess/notifications` | My notifications | authenticated |
| `POST` | `/api/v1/ess/notifications/{id}/read` | Mark as read | authenticated |

---

## Module 6: Recruitment & Onboarding

| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/manpower-requests` | List requests | view-recruitment |
| `POST` | `/api/v1/manpower-requests` | Create request | create-manpower |
| `POST` | `/api/v1/manpower-requests/{id}/approve` | Approve | approve-manpower |
| `GET` | `/api/v1/job-postings` | List postings | view-recruitment |
| `POST` | `/api/v1/job-postings` | Create posting | manage-recruitment |
| `PUT` | `/api/v1/job-postings/{id}` | Update posting | manage-recruitment |
| `POST` | `/api/v1/job-postings/{id}/publish` | Publish | manage-recruitment |
| `POST` | `/api/v1/job-postings/{id}/close` | Close | manage-recruitment |
| `GET` | `/api/v1/applicants` | List applicants | view-recruitment |
| `POST` | `/api/v1/applicants` | Add applicant | manage-recruitment |
| `PUT` | `/api/v1/applicants/{id}/status` | Update status | manage-recruitment |
| `POST` | `/api/v1/applicants/{id}/hire` | Hire → create employee | manage-recruitment |
| `GET` | `/api/v1/interviews` | List interviews | view-recruitment |
| `POST` | `/api/v1/interviews` | Schedule interview | manage-recruitment |
| `PUT` | `/api/v1/interviews/{id}` | Update feedback/rating | manage-recruitment |
| `GET` | `/api/v1/onboarding/{employeeId}` | Get checklist | manage-recruitment |
| `PUT` | `/api/v1/onboarding/{employeeId}/items/{id}` | Complete item | manage-recruitment |

---

## Module 7: Project Management

| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/projects` | List projects | authenticated |
| `POST` | `/api/v1/projects` | Create project | authenticated |
| `PUT` | `/api/v1/projects/{id}` | Update project | project-owner |
| `GET` | `/api/v1/projects/{id}/board` | Get board (columns + tasks) | project-member |
| `POST` | `/api/v1/projects/{id}/columns` | Add column | project-owner |
| `PUT` | `/api/v1/projects/{id}/columns/{cid}` | Update column | project-owner |
| `PUT` | `/api/v1/projects/{id}/columns/reorder` | Reorder columns | project-owner |
| `GET` | `/api/v1/projects/{id}/tasks` | List tasks | project-member |
| `POST` | `/api/v1/projects/{id}/tasks` | Create task | project-member |
| `PUT` | `/api/v1/tasks/{id}` | Update task | project-member |
| `PUT` | `/api/v1/tasks/{id}/move` | Move task (column + order) | project-member |
| `DELETE` | `/api/v1/tasks/{id}` | Delete task | project-owner |
| `GET` | `/api/v1/tasks/{id}/comments` | List comments | project-member |
| `POST` | `/api/v1/tasks/{id}/comments` | Add comment | project-member |
| `POST` | `/api/v1/tasks/{id}/attachments` | Upload attachment | project-member |
| `GET` | `/api/v1/tasks/{id}/activities` | Activity log | project-member |
| `GET` | `/api/v1/projects/{id}/milestones` | List milestones | project-member |
| `POST` | `/api/v1/projects/{id}/milestones` | Create milestone | project-owner |
| `POST` | `/api/v1/projects/{id}/members` | Add member | project-owner |
| `DELETE` | `/api/v1/projects/{id}/members/{empId}` | Remove member | project-owner |

---

## Module 8: Announcement

| Method | Endpoint | Deskripsi | Permission |
|---|---|---|---|
| `GET` | `/api/v1/announcements` | List announcements (active) | authenticated |
| `POST` | `/api/v1/announcements` | Create | manage-announcements |
| `PUT` | `/api/v1/announcements/{id}` | Update | manage-announcements |
| `DELETE` | `/api/v1/announcements/{id}` | Delete | manage-announcements |

---

## Endpoint Summary

| Module | Endpoints |
|---|---|
| Auth | 6 |
| Foundation (Users, Settings, Audit, Dashboard) | 10 |
| Employee Management | 22 |
| Attendance | 18 |
| Leave Management | 14 |
| Payroll Engine | 22 |
| ESS | 9 |
| Recruitment & Onboarding | 17 |
| Project Management | 20 |
| Announcement | 4 |
| **Total** | **~142 endpoints** |

---

*Dibuat: 4 Maret 2026*
