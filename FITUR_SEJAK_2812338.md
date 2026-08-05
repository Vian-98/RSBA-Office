# Ringkasan Fitur & Perubahan Sistem
> **Titik Awal (Baseline Commit):** `28123385d4b6666e28f07477313c61bf65ad1b6f`  
> **Tanggal Baseline:** 7 Juli 2026  
> **Total Perubahan:** 507 file diubah/ditambah (`+49.982` baris)

---

## 📌 Status Fitur (Legend)
- `[BARU]` Fitur/Modul baru yang dibuat setelah commit baseline.
- `[DIPERBAHARUI]` Modul eksisting yang mengalami penambahan fitur & perubahan besar.

---

## 1. 👥 Kepegawaian & SDM (`/kepegawaian` & `/karyawan`)

| Type | Path / Modul | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `[BARU]` | `kepegawaian/absensi` | Dashboard Container Presensi & Kehadiran Karyawan |
| `[BARU]` | `kepegawaian/absensi/import` | Fitur Import Machine Punch Data Absensi (Excel/CSV) |
| `[BARU]` | `kepegawaian/absensi/rekap` | Rekapitulasi Presensi Harian & Bulanan dengan Aggregation Efisien Memori |
| `[BARU]` | `kepegawaian/absensi/duplicate-report` | Laporan Deteksi & Resolusi Log Tap Ganda Karyawan |
| `[BARU]` | `kepegawaian/absensi/rekonsiliasi` | Rekonsiliasi Log Tap dengan Rule Shift & Toleransi Jam Kerja |
| `[BARU]` | `kepegawaian/absensi/koreksi` | Tab & Modal Koreksi Manual Presensi per Karyawan |
| `[BARU]` | `kepegawaian/jadwal-kerja` | Manajemen Penugasan & Publikasi Jadwal Kerja Bulanan |
| `[BARU]` | `kepegawaian/jadwal-kerja/kelola/{id}` | Editor Matriks Shift Karyawan per Ruangan |
| `[BARU]` | `kepegawaian/jadwal-kerja/tukar-dokter` | Pengajuan & Approval Tukar Shift Karyawan/Dokter |
| `[BARU]` | `kepegawaian/jadwal-kerja/generate` | Generator Otomatis Alokasi Shift Ruangan |
| `[BARU]` | `kepegawaian/konfigurasi-jadwal` | Konfigurasi Aturan & Batas Jam Kerjaf |
| `[BARU]` | `kepegawaian/cuti-bersama` | Penetapan & Pengaturan Cuti Bersama Nasional/Internal |
| `[BARU]` | `gaji` | Penggajian (Payroll System), Rekapitulasi Gaji & Slip PDF via Email |
| `[BARU]` | `gaji/detail` | Rincian Komponen Gaji & Potongan Karyawan |
| `[DIPERBAHARUI]` | `karyawan` | Integrasi Koordinator Ruangan & Form Kedinasan Karyawan |
| `[DIPERBAHARUI]` | `laporan` | Laporan Komprehensif Kepegawaian & SDM |

---

## 2. 🔏 Surat & Tanda Tangan Digital (`/surat`)

| Type | Path / Modul | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `[BARU]` | `surat/audit-bank-surat` | Audit Keaslian Dokumen Surat & Integration Bank Surat (Docstore) |
| `[BARU]` | `verifikasi-surat/{hash}` | Portal Publik Verifikasi TTD Digital & QR Code Scanner |
| `[BARU]` | `surat/cuti/verify` | Panel Verifikasi Keaslian Surat Cuti Karyawan |
| `[DIPERBAHARUI]` | `surat/sp3` | Redesain Tabel SP3, Custom Filter, Flow Approval & TTD Digital |
| `[DIPERBAHARUI]` | `surat/cuti` | Pengajuan Cuti, Approval & Kalkulasi Automatic Reset Sisa Cuti Anniversary |

---

## 3. ⚙️ Master Data Kepegawaian (`/master`)

| Type | Path / Modul | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `[BARU]` | `master/jadwal-shift` | Master Jam Operasional Shift (Pagi / Siang / Malam) |
| `[BARU]` | `master/ruangan-shift` | Pemetaan Shift yang Berlaku untuk Masing-masing Ruangan |
| `[BARU]` | `master/jadwal-aturan` | Master Rules Operasional & Toleransi Shift |
| `[BARU]` | `master/bagian-koordinator` | Pengaturan Tugas Tambahan Koordinator Bagian |
| `[BARU]` | `master/tunjangan-golongan` | Master Tunjangan Berdasarkan Golongan |
| `[BARU]` | `master/tunjangan-jabatan` | Master Tunjangan Berdasarkan Jabatan |
| `[BARU]` | `master/tunjangan-lain` | Master Tunjangan Tambahan Komponen Gaji |
| `[BARU]` | `master/aturan-pajak` | Master Rules & Potongan Pajak PPh |

---

## 4. 👤 Profil User & Display Monitor (`/profile` & `/dashboard`)

| Type | Path / Modul | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `[BARU]` | `profile/jadwal-tugas-saya` | Halaman Mandiri Jadwal & Shift Kerja User |
| `[BARU]` | `profile/email-aktivasi` | Fitur Aktivasi Email Karyawan |
| `[BARU]` | `profile/login-session` | Riwayat & Manajemen Active Login Sessions |
| `[BARU]` | `dashboard/display-monitor/admin` | Admin Panel untuk Management Display Monitor Ruangan |
| `[BARU]` | `dashboard/poli/admin` | Admin Panel Display Monitor Antrean Poliklinik |
| `[DIPERBAHARUI]` | `profile/setting` | Form Sertifikat TTD Digital & Sertifikat Kunci |
| `[DIPERBAHARUI]` | `dashboard` | Summary Card Kehadiran Personal (Jam Lembur, Terlambat, Pulang Cepat) |

---

## 5. 📦 Logistik & Maintenance (`/gudang`, `/pembelian`, `/maintenance`)

| Type | Path / Modul | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `[BARU]` | `maintenance/ticket/{id}` | Detail & Status Tracking Ticket Problem Maintenance |
| `[DIPERBAHARUI]` | `gudang` | Pengelolaan Lokasi Penyimpanan (*Stock Splitting*) & Kartu Stok |
| `[DIPERBAHARUI]` | `pembelian` | Transaksi Beli Langsung & Integrasi Variabel Satuan Barang |
