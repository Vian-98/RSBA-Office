# 🌿 Strategi Branching Granular - Modul Kepegawaian (HR/SDM)

> **Pendekatan:** *Micro-Feature Branching* (Terisolasi Per Fungsi Spesifik)  
> **Tujuan:** Memudahkan *code review*, pengujian terisolasi, dan mencegah konflik penggabungan (*merge conflict*).

---

## 📋 Daftar Branch Spesifik per Fungsi Fitur

### A. Kelompok Penjadwalan & Shift Work

| Nama Branch | Target Component / Route | Deskripsi & Fungsi Fitur |
| :--- | :--- | :--- |
| `feature/sdm-master-shift` | `master/jadwal-shift`<br>`master/ruangan-shift`<br>`master/jadwal-aturan` | CRUD Master Jam Shift (Pagi/Siang/Malam), pemetaan shift per ruangan, dan aturan kerja shift. |
| `feature/sdm-kelola-jadwal` | `kepegawaian/jadwal-kerja/kelola/{id}` | Editor Matriks Jadwal Kerja & Grid Alokasi Shift Karyawan per Ruangan. |
| `feature/sdm-generate-jadwal` | `kepegawaian/jadwal-kerja/generate` | Generator Otomatis Alokasi Shift Bulanan Karyawan. |
| `feature/sdm-tukar-jadwal` | `kepegawaian/jadwal-kerja/tukar-dokter` | Pengajuan, Verifikasi, & Approval Tukar Shift Karyawan/Dokter. |
| `feature/sdm-jadwal-tugas-saya` | `profile/jadwal-tugas-saya` | Tampilan Personal Penayangan Shift & Tugas User di Profil. |
| `feature/sdm-konfigurasi-jadwal` | `kepegawaian/konfigurasi-jadwal` | Pengaturan Parameter Aturan, Batas, & Toleransi Jam Kerja. |

---

### B. Kelompok Presensi & Absensi Karyawan

| Nama Branch | Target Component / Route | Deskripsi & Fungsi Fitur |
| :--- | :--- | :--- |
| `feature/sdm-absensi-import` | `kepegawaian/absensi/import` | Fitur Upload, Parsing, & Validasi Import Punch Machine Absensi (Excel/CSV). |
| `feature/sdm-absensi-rekap` | `kepegawaian/absensi/rekap` | Rekapitulasi Presensi Harian & Bulanan dengan Agregasi Memori Efisien. |
| `feature/sdm-absensi-duplicate-report` | `kepegawaian/absensi/duplicate-report` | Laporan Deteksi & Resolusi Log Tap Ganda (Duplicate Tap Report). |
| `feature/sdm-absensi-rekonsiliasi` | `kepegawaian/absensi/rekonsiliasi` | Rekonsiliasi Log Tap Mesin dengan Aturan Shift & Toleransi Jam Kerja. |
| `feature/sdm-absensi-koreksi` | `kepegawaian/absensi/koreksi` | Modal & Tab Koreksi Manual Jam Kehadiran per Karyawan. |

---

### C. Kelompok Penggajian & Tunjangan (Payroll)

| Nama Branch | Target Component / Route | Deskripsi & Fungsi Fitur |
| :--- | :--- | :--- |
| `feature/sdm-master-tunjangan-pajak` | `master/tunjangan-*`<br>`master/aturan-pajak` | Master Tunjangan Golongan, Jabatan, Tunjangan Lain, & Aturan Pajak PPh. |
| `feature/sdm-payroll-gaji` | `gaji`<br>`gaji/detail` | Perhitungan Gaji, Rekap Gaji, Generasi PDF Slip Gaji, & Delivery Email Job. |

---

### D. Kelompok Cuti & Master SDM

| Nama Branch | Target Component / Route | Deskripsi & Fungsi Fitur |
| :--- | :--- | :--- |
| `feature/sdm-cuti-bersama` | `kepegawaian/cuti-bersama`<br>`profile/cuti` | Pengaturan Cuti Bersama & Reset Automatic Sisa Cuti Berbasis Anniversary Tanggal Masuk. |
| `feature/sdm-koordinator-ruangan` | `master/bagian-koordinator`<br>`karyawan/dokter/koor-ruangan` | Pengaturan Tugas Tambahan Koordinator Bagian/Ruangan & Form Kedinasan. |

---

## ⏳ Urutan Eksekusi Branch yang Direkomendasikan

1. **Fase 1 (Pondasi Shift & Master SDM):**
   - `feature/sdm-koordinator-ruangan`
   - `feature/sdm-master-shift`
2. **Fase 2 (Penjadwalan Workload):**
   - `feature/sdm-kelola-jadwal`
   - `feature/sdm-generate-jadwal`
   - `feature/sdm-tukar-jadwal`
   - `feature/sdm-jadwal-tugas-saya`
3. **Fase 3 (Absensi & Presensi):**
   - `feature/sdm-absensi-import`
   - `feature/sdm-absensi-rekap`
   - `feature/sdm-absensi-koreksi`
   - `feature/sdm-absensi-rekonsiliasi`
4. **Fase 4 (Payroll & Cuti):**
   - `feature/sdm-master-tunjangan-pajak`
   - `feature/sdm-payroll-gaji`
   - `feature/sdm-cuti-bersama`

---

## 💻 Cheat-Sheet Command Git

```bash
# Update branch HR terbaru
git checkout HR
git pull origin HR

# Membuat branch mikro baru dari HR
git checkout -b <nama-branch>

# Contoh:
git checkout -b feature/sdm-master-shift
```
