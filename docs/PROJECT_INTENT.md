# 🎯 Project Intent — OrcaHR

> Dokumen 1 halaman. Dibuat untuk menjelaskan **mengapa** project ini ada dan **apa** yang ingin dicapai.

---

## 1. Problem Statement

> *Masalah utama apa yang ingin diselesaikan?*

**Situasi saat ini:**
- Kantor sudah memiliki HRIS dari vendor, namun memiliki **banyak skema payroll** yang berbeda-beda
- Setiap kali ingin custom skema payroll baru, vendor mematok **biaya tinggi**
- Akibatnya, beberapa proses payroll masih dikerjakan **manual/semi-manual**
- Tidak ada platform terpusat untuk **project management** dan kolaborasi tim

**Dampak masalah:**
- Cost besar untuk setiap request kustomisasi ke vendor
- Ketergantungan penuh pada vendor untuk perubahan sekecil apapun
- Proses payroll lambat dan rawan error karena workaround manual
- Karyawan tidak punya akses self-service ke data mereka sendiri

**Akar masalah:**
- HRIS vendor dirancang "one-size-fits-all" — tidak fleksibel untuk skema payroll yang kompleks dan beragam
- Tidak ada kontrol penuh atas sistem, sehingga setiap perubahan harus lewat vendor dengan biaya tambahan

---

## 2. Target User

> *Siapa user pertama yang akan menggunakan solusi ini?*

| Aspek | Detail |
|---|---|
| **Siapa?** | Admin HR, Staff Payroll, Department Head, dan seluruh karyawan (ESS) |
| **Pain point utama mereka?** | Harus koordinasi dengan vendor setiap ada perubahan skema payroll, proses lama dan mahal |
| **Apa yang mereka lakukan sekarang?** | Pakai HRIS vendor + workaround manual (Excel) untuk skema yang tidak didukung |
| **Kenapa mereka mau pindah?** | Ingin fleksibilitas penuh untuk kelola skema payroll sendiri tanpa biaya tambahan |

---

## 3. Solusi (Satu Kalimat)

> *Jelaskan solusi dalam 1 kalimat tanpa jargon teknis.*

**"Platform terpadu dimana Admin HR bisa kelola seluruh skema payroll secara mandiri, karyawan bisa akses data mereka sendiri, dan semua tim bisa berkolaborasi dalam satu sistem — tanpa ketergantungan vendor."**

---

## 4. Success Criteria (30 Hari)

> *Apa bukti nyata bahwa project ini sukses dalam 30 hari pertama?*

| # | Kriteria Sukses | Ukuran |
|---|---|---|
| 1 | Data karyawan berhasil di-manage | Semua data karyawan aktif sudah terinput di sistem |
| 2 | Absensi berjalan harian | Admin tidak perlu input manual, absensi tercatat otomatis |
| 3 | Minimal 1 skema payroll berjalan | Slip gaji bisa di-generate untuk 1 skema payroll |
| 4 | User mengadopsi sistem | Admin HR login dan gunakan sistem setiap hari kerja |

---

## 5. Bukan Scope (Out of Scope)

> *Apa yang TIDAK akan dikerjakan di fase awal?*

- ❌ Mobile app native (fokus web app dulu, responsive)
- ❌ Integrasi dengan sistem keuangan/accounting eksternal
- ❌ Multi-company / multi-branch support (single company only)
- ❌ AI/ML-based analytics
- ❌ Procurement / purchasing module

> [!NOTE]
> Module berikut termasuk scope tapi **dijadwalkan di fase lanjutan:**
> - Employee Self-Service (ESS) portal
> - Recruitment: manpower request, job posting, onboarding
> - Project Management: kanban, task list, sprint board
>
> **Scope:** Sistem ini didesain untuk **satu perusahaan** (single-tenant), bukan multi-company.

> [!IMPORTANT]
> Bagian ini sama pentingnya dengan scope. Menentukan batasan sejak awal mencegah scope creep.

---

## 6. Risiko & Asumsi

> *Apa yang bisa gagal? Apa yang kita asumsikan benar?*

**Risiko:**
| Risiko | Dampak | Mitigasi |
|---|---|---|
| Skema payroll terlalu kompleks untuk di-modelkan | Tinggi | Mulai dari 1 skema, iterasi bertahap |
| User enggan pindah dari sistem lama | Sedang | Onboarding hands-on, tunjukkan value langsung |
| Development time melebihi estimasi | Tinggi | Scope ketat per fase, MVP-first mindset |
| Data migration dari sistem lama bermasalah | Tinggi | Validasi data bertahap, jalankan paralel |

**Asumsi:**
- ✅ Manajemen mendukung pengembangan HRIS in-house
- ✅ Admin HR bersedia menjadi early adopter dan memberikan feedback
- ✅ Infrastruktur server/hosting sudah tersedia atau bisa disiapkan
- ✅ Dokumentasi skema payroll yang ada bisa didapatkan dari tim HR

---

## 7. Keputusan Awal

> *Keputusan teknis/bisnis yang sudah ditentukan.*

| Keputusan | Alasan |
|---|---|
| **Laravel 12** (full-stack) | Ekosistem mature, familiar, Blade + Eloquent |
| **Blade + Alpine.js** (frontend) | Server-rendered, fast development, interaktif cukup |
| **Tailwind CSS** (styling) | Utility-first, rapid UI development |
| **Service Layer pattern** | Logic di Service class, bukan controller — siap migrasi ke API + Vue nanti |
| Bahasa Indonesia (UI) | Target user lokal, mempermudah adopsi |
| Build in-house (bukan beli vendor) | Fleksibilitas penuh untuk skema payroll custom |
| Mulai dari payroll + employee management | Ini core problem yang harus diselesaikan duluan |
| Deployment internal / private server | Data HR sensitif, lebih aman di infrastruktur sendiri |

---

*Dibuat: 3 Maret 2026 • Terakhir diperbarui: 3 Maret 2026*
*Project: OrcaHR*
