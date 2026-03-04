# 🔒 Security & Compliance Blueprint — OrcaHR

> Strategi keamanan data untuk HRIS. Prinsip utama: **"Kalau database kebobolan, dampaknya jangan jadi kiamat."**

---

## Filosofi Keamanan

Enkripsi saja **tidak cukup**. Yang membuat sistem kuat adalah **kombinasi berlapis**:

```
Enkripsi + Hashing + Kontrol Akses + Audit Trail + Minimisasi Data
```

> [!CAUTION]
> Enkripsi melindungi dari **data exfiltration** (dump DB, backup bocor). Tapi kalau attacker sudah pegang kredensial admin aplikasi, enkripsi kolom bisa tetap dibuka. Makanya **kontrol akses & monitoring sama pentingnya**.

---

## 1. Data Classification

### 🔴 HIGH RISK — Wajib Enkripsi (Field-Level)

| Kategori | Field | Encrypt | Hash/HMAC |
|---|---|---|---|
| **Identitas** | NIK (KTP) | ✅ | ✅ (search + dedup) |
| | Nomor KK | ✅ | — |
| | Passport | ✅ | — |
| | NPWP | ✅ | ✅ (matching) |
| | BPJS Kesehatan | ✅ | — |
| | BPJS Ketenagakerjaan | ✅ | — |
| | No SIM | ✅ | — |
| **Finansial** | Nomor rekening bank | ✅ | — |
| | Nama bank + cabang | ✅ | — |
| **Kontak** | Alamat rumah lengkap | ✅ | — |
| | Nomor telepon pribadi | ✅ | — |
| **Medis** | Surat dokter / diagnosis | ✅ | — |
| | Catatan kesehatan | ✅ | — |
| | Data disabilitas | ✅ | — |

### 🟡 MEDIUM RISK — Kontrol Akses Ketat

| Field | Strategi |
|---|---|
| Email pribadi | RBAC ketat, opsional enkripsi |
| Tanggal lahir | RBAC ketat (PII), enkripsi jika threat model tinggi |
| Take-home pay | Akses hanya payroll team |
| Detail potongan gaji | Akses hanya payroll team |

### 🟢 LOW RISK — Standar Protection

| Field | Strategi |
|---|---|
| Nama karyawan | Standar (tidak perlu enkripsi) |
| Department | Standar |
| Jabatan | Standar |
| Status aktif/nonaktif | Standar |

---

## 2. Strategi Enkripsi + Hash

### Problem Klasik: Pencarian Data Terenkripsi

> HR butuh cari "NIK = 3173…" atau cek duplikasi. Kalau NIK dienkripsi non-deterministic, pencarian equality jadi mustahil.

### Solusi: Dual-Column Pattern

```
┌──────────────────────────────────────────────┐
│  Kolom          │ Isi              │ Fungsi   │
├──────────────────────────────────────────────┤
│  nik_encrypted  │ AES-256 value    │ Tampilan │
│  nik_hash       │ HMAC-SHA256      │ Search   │
└──────────────────────────────────────────────┘
```

**Cara kerja:**
- **Simpan** → `nik_encrypted` (untuk decrypt & tampilkan ke role tertentu)
- **Simpan** → `nik_hash` (HMAC untuk cek unik + pencarian exact match)
- **Cari** → Hash input user → bandingkan dengan `nik_hash`
- **Tampilkan** → Decrypt `nik_encrypted` hanya untuk role terotorisasi

> [!IMPORTANT]
> Ini **jauh lebih aman** daripada menyimpan NIK plaintext "karena butuh search".

### Field yang Butuh Dual-Column

| Field | Encrypted | Hash (HMAC) | Alasan hash |
|---|---|---|---|
| NIK | ✅ | ✅ | Search + cek duplikat |
| NPWP | ✅ | ✅ | Matching antar sistem |
| No Rekening | ✅ | ❌ | Jarang di-search |
| Alamat | ✅ | ❌ | Jarang di-search by exact |
| Telepon | ✅ | ❌ | Jarang di-search |

---

## 3. Dokumen & File Upload

| Layer | Strategi |
|---|---|
| **Storage** | Server-side encryption (AES-256) |
| **Akses URL** | Signed URL + expiring (15-30 menit) |
| **Yang disimpan** | Scan KTP, NPWP, kontrak, surat dokter |
| **Jangan** | Simpan file tanpa enkripsi di public storage |

---

## 4. Lapisan Keamanan Wajib

| Layer | Requirement | Status |
|---|---|---|
| **Encryption at Rest** | Seluruh database + backup terenkripsi | 🔲 |
| **Encryption in Transit** | TLS/HTTPS untuk semua komunikasi | 🔲 |
| **Key Management** | KMS — kunci **JANGAN** disimpan bareng DB | 🔲 |
| **Key Rotation** | Rotasi kunci enkripsi berkala | 🔲 |
| **RBAC** | Role-based access control ketat | 🔲 |
| **Audit Log** | Log siapa akses data sensitif (NIK, rekening), kapan | 🔲 |
| **Data Minimization** | Jangan simpan yang tidak perlu | 🔲 |
| **Backup Encryption** | Backup DB terenkripsi terpisah dari production | 🔲 |

---

## 5. RBAC Matrix (Contoh)

| Data | Super Admin | HR Admin | Payroll Admin | Department Head | Employee |
|---|---|---|---|---|---|
| Profil dasar | ✅ | ✅ | ✅ | ✅ (tim) | ✅ (self) |
| NIK / NPWP | ✅ | ✅ | ❌ | ❌ | ✅ (self) |
| Rekening bank | ✅ | ❌ | ✅ | ❌ | ✅ (self) |
| Slip gaji | ✅ | ❌ | ✅ | ❌ | ✅ (self) |
| Dokumen medis | ✅ | ✅ | ❌ | ❌ | ✅ (self) |
| Audit log | ✅ | ❌ | ❌ | ❌ | ❌ |

> [!WARNING]
> **Payroll team tidak perlu lihat dokumen medis.** HR admin tidak perlu lihat rekening penuh. Prinsip: **least privilege**.

---

## 6. ISO Compliance Roadmap

### Relevan untuk HRIS

| ISO | Nama | Prioritas | Fungsi |
|---|---|---|---|
| **27001:2022** | Information Security Management | 🔴 Wajib | Manajemen risiko, kontrol akses, enkripsi, audit |
| **27002** | Security Controls | 🔴 Pendukung 27001 | Panduan teknis detail (resep teknis) |
| **27701** | Privacy Information Management | 🟡 Sangat Dianjurkan | Fokus PII, pendekatan mirip GDPR |
| **27017** | Cloud Security | 🟡 Jika SaaS | Shared responsibility, isolasi tenant |
| **27018** | PII Protection in Cloud | 🟡 Jika SaaS | Perlindungan data pribadi di cloud publik |
| **22301** | Business Continuity | 🟡 Penting | Disaster recovery, RTO/RPO, backup |

### Tidak Relevan

| ISO | Nama | Alasan |
|---|---|---|
| 9001 | Quality Management | Proses umum, bukan keamanan data |
| 14001 | Lingkungan | Tidak terkait |
| 45001 | K3 | Tidak terkait |

### Strategi Adopsi

```
Fase 1 → Bangun arsitektur mengikuti ISO 27001 sejak awal
Fase 2 → Dokumentasi kontrol dari hari pertama
Fase 3 → Prinsip least privilege + encryption + audit trail
Fase 4 → (Opsional) Sertifikasi formal jika skala bisnis membutuhkan
```

> [!NOTE]
> **Real talk:** ISO bukan tameng anti-breach. ISO adalah bukti bahwa kamu punya **sistem manajemen risiko yang matang**. Perusahaan bisa ISO-certified tapi tetap kena breach. Bedanya: mereka punya prosedur respons, logging, root cause analysis, dan perbaikan sistematis.
>
> **Keamanan itu bukan status. Itu proses yang terus hidup.**

---

## 7. Checklist Implementasi

### Foundation (Fase 1) — Harus Dari Awal

- [ ] Setup HTTPS/TLS
- [ ] Implementasi RBAC dengan Spatie Permission
- [ ] Setup database encryption at rest
- [ ] Buat helper class untuk encrypt/decrypt field
- [ ] Buat helper class untuk HMAC hashing
- [ ] Setup audit log (siapa akses data apa, kapan)
- [ ] Kunci enkripsi di `.env` / KMS (BUKAN di database)
- [ ] Session-based auth (standard Laravel)

### Employee Module (Fase 1)

- [ ] Terapkan dual-column pattern pada NIK, NPWP
- [ ] Enkripsi field sensitif (BPJS, alamat, telepon, rekening)
- [ ] File upload dengan server-side encryption
- [ ] Signed URL untuk akses dokumen
- [ ] RBAC test: pastikan role tidak bisa akses data terlarang

### Payroll Module (Fase 3)

- [ ] Enkripsi data finansial
- [ ] Audit log setiap akses slip gaji
- [ ] RBAC payroll terpisah dari HR admin

### ESS Portal (Fase 4)

- [ ] Pastikan employee hanya bisa akses data sendiri (policy-based)
- [ ] Rate limiting pada ESS routes

### Post-Launch

- [ ] Penetration testing
- [ ] Key rotation setup
- [ ] Backup encryption verification
- [ ] Incident response plan

---

*Dibuat: 3 Maret 2026*
*Referensi: ISO/IEC 27001:2022, ISO 27002, ISO 27701*
