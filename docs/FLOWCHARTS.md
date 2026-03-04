# 📊 Business Process Flowcharts — OrcaHR

> Visualisasi alur proses bisnis utama.

---

## 1. Attendance: Daily Processing

```mermaid
flowchart TD
    A[Karyawan Clock In/Out] --> B[Clock Log Tersimpan]
    B --> C{Event: New Clock Log}
    C --> D[Tandai Tanggal Dirty]
    D --> E[Queue: Recalc Job]
    E --> F[Ambil Schedule Berlaku Hari Itu]
    F --> G[Ambil Clock Logs Hari Itu]
    G --> H[Ambil Approved Corrections]
    H --> I[Ambil Approved OT]
    I --> J{Ada Clock In & Out?}
    J -->|Ya| K[Hitung Late, Early Leave, OT, Work Hours]
    J -->|Tidak| L[Status: Absent / Incomplete]
    K --> M{Telat > Tolerance?}
    M -->|Ya| N[Status: Late]
    M -->|Tidak| O[Status: Present]
    L --> P[Simpan Daily Attendance]
    N --> P
    O --> P
    P --> Q[Log: Apa berubah, trigger apa]
```

---

## 2. Leave Request & Approval

```mermaid
flowchart TD
    A[Karyawan Submit Leave Request] --> B{Saldo Cuti Cukup?}
    B -->|Tidak| C[❌ Reject: Saldo tidak cukup]
    B -->|Ya| D{Butuh Attachment?}
    D -->|Ya, tidak ada| E[❌ Reject: Lampiran wajib]
    D -->|Tidak / Ada| F[Status: Pending]
    F --> G[Notifikasi ke Department Head]
    G --> H{Dept Head Decision}
    H -->|Reject| I[Status: Rejected + Alasan]
    I --> J[Saldo Dikembalikan]
    H -->|Approve| K[Status: Approved]
    K --> L[Kurangi Saldo Cuti]
    L --> M[Trigger Attendance Recalc]
    M --> N[Hari Cuti → Status: Leave]

    style C fill:#ff6b6b,color:#fff
    style E fill:#ff6b6b,color:#fff
    style K fill:#4ecdc4,color:#fff
```

---

## 3. Overtime Request & Approval

```mermaid
flowchart TD
    A[Karyawan Submit OT Request] --> B[Status: Pending]
    B --> C[Notifikasi ke Dept Head]
    C --> D{Dept Head Decision}
    D -->|Reject| E[Status: Rejected]
    D -->|Approve| F[Status: Approved]
    F --> G[Trigger Attendance Recalc]
    G --> H[Daily Attendance: OT Minutes Updated]

    style F fill:#4ecdc4,color:#fff
```

---

## 4. Attendance Correction

```mermaid
flowchart TD
    A[Karyawan / Admin Submit Correction] --> B[Status: Pending]
    B --> C[Notifikasi ke Approver]
    C --> D{Approval Decision}
    D -->|Reject| E[Status: Rejected]
    D -->|Approve| F[Status: Approved]
    F --> G[Correction Jadi Fakta Baru]
    G --> H[Trigger Attendance Recalc]
    H --> I[Daily Attendance Re-calculated]
    I --> J[Log: Correction ID sebagai trigger]

    style F fill:#4ecdc4,color:#fff
```

---

## 5. Payroll Processing

```mermaid
flowchart TD
    A[Payroll Admin: Open Period] --> B[Status: Open]
    B --> C[Run Payroll]
    C --> D[Per Karyawan: Ambil Skema Aktif]
    D --> E[Per Komponen: Calculate]
    E --> F{Formula Type}
    F -->|Fixed| G[Value / Override]
    F -->|Percentage| H[% × Base Salary]
    F -->|Formula| I[Evaluate Expression]
    F -->|Attendance| J[Ambil dari Daily Attendance]
    G --> K[Simpan Detail]
    H --> K
    I --> K
    J --> K
    K --> L[Hitung PPh 21]
    L --> M[Gross - Deductions = Net]
    M --> N[Status: Review]
    N --> O{Payroll Admin Review}
    O -->|Adjust| P[Edit + Re-run]
    P --> E
    O -->|Approve| Q[Lock Period]
    Q --> R[Status: Locked ✅]
    R --> S[Generate Slip Gaji]
    R --> T[Generate Bank Export]

    style Q fill:#ff6b6b,color:#fff
    style R fill:#4ecdc4,color:#fff
```

---

## 6. Payroll Correction (Post-Lock)

```mermaid
flowchart TD
    A[Kesalahan Ditemukan] --> B{Period Sudah Lock?}
    B -->|Belum| C[Edit Langsung + Re-run]
    B -->|Sudah| D[❌ TIDAK BOLEH Edit]
    D --> E[Buat Payroll Adjustment]
    E --> F[Target: Period Lama]
    E --> G[Applied: Period Berikutnya]
    F --> H[Adjustment Masuk Slip Bulan Depan]
    G --> H

    style D fill:#ff6b6b,color:#fff
```

---

## 7. Recruitment Pipeline

```mermaid
flowchart TD
    A[Dept Head: Manpower Request] --> B[Status: Pending]
    B --> C{HR Approval}
    C -->|Reject| D[Status: Rejected]
    C -->|Approve| E[Status: Approved]
    E --> F[HR: Create Job Posting]
    F --> G[Publish Lowongan]
    G --> H[Applicants Apply]
    H --> I[HR: Screening]
    I --> J{Pass Screening?}
    J -->|Tidak| K[Status: Rejected]
    J -->|Ya| L[Schedule Interview]
    L --> M[Interview HR]
    M --> N[Interview Technical]
    N --> O[Interview User/Final]
    O --> P{Final Decision}
    P -->|Reject| Q[Status: Rejected]
    P -->|Accept| R[Generate Offering Letter]
    R --> S{Candidate Accept?}
    S -->|Tidak| T[Status: Declined]
    S -->|Ya| U[Status: Hired]
    U --> V[Auto-Create Employee + User Account]
    V --> W[Assign Onboarding Checklist]
    W --> X[Probation Tracking]

    style U fill:#4ecdc4,color:#fff
    style V fill:#45b7d1,color:#fff
```

---

## 8. Onboarding Process

```mermaid
flowchart TD
    A[New Employee Created] --> B[Auto-Assign Onboarding Template]
    B --> C[Checklist Generated]
    C --> D[Document Collection]
    D --> E[Account Setup]
    E --> F[Training Plan]
    F --> G[Daily Check-in]
    G --> H{Probation H-30}
    H --> I[Reminder Sent to Manager]
    I --> J{Evaluation}
    J -->|Pass| K[Status: Permanent]
    J -->|Extend| L[Extend Probation]
    J -->|Fail| M[Terminate]

    style K fill:#4ecdc4,color:#fff
```

---

## 9. General Approval Flow Pattern

> Banyak modul menggunakan pattern approval yang sama.

```mermaid
flowchart TD
    A[User Submit Request] --> B[Status: Pending]
    B --> C[Notifikasi ke Approver]
    C --> D[Approver Review]
    D --> E{Decision}
    E -->|Approve| F[Status: Approved]
    E -->|Reject| G[Status: Rejected + Reason]
    F --> H[Execute Side Effects]
    G --> I[Revert Side Effects]
    H --> J[Log Audit Trail]
    I --> J
```

**Modul yang pakai pattern ini:**
- Leave Request
- Overtime Request
- Attendance Correction
- Manpower Request
- Profile Update (ESS)

---

*Dibuat: 4 Maret 2026*
