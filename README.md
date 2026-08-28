<h1 align="center">RSBA OFFICE</h1>

<p align="center">
  Aplikasi perkantoran (SIM-SDM & Internal Office) pada <b>Rumah Sakit Bintang Amin Lampung</b>.
</p>

- **Integrasi SATUSEHAT Practitioner (IHS Number, STR, SIP & Upload Softcopy STR)**:
  - Pencatatan Practitioner IHS Number dari Kemenkes RI, Nomor STR, Jenis STR, Tanggal Terbit & Kadaluarsa STR, Jenis Profesi, serta Spesialisasi/Kompetensi pada data identitas karyawan.
  - Fitur unggah berkas softcopy STR (PDF/Gambar) terintegrasi langsung di form identitas & lisensi medis yang otomatis tersinkronisasi ke repositori dokumen pegawai (`sdm_kary_document`).
  - Tampilan kolom IHS Number, Nomor STR, dan badge status STR Expired (Aktif, Warning ≤90 hari, Expired) pada tabel daftar Dokter & Pegawai Medis.
- **Export PDF Jadwal Kerja Dinamis dengan Header Logo RSBA**:
  - Fitur ekspor jadwal kerja ruangan ke format PDF (*Landscape A4*) dengan tampilan presisi mengacu pada format standar dokumen fisik rumah sakit.
  - Dilengkapi **Header Logo Resmi RSBA** (Base64 Data URI) dan Nama Perusahaan.
  - Sinkronisasi dinamis 100% dengan tampilan web UI: pencetakan kode shift singkat (`REG`, `PAGI`, `SIANG`, `MALAM`), skema warna sel (*background & font contrast*), serta tabel legenda shift otomatis sesuai konfigurasi shift ruangan aktif.
  - Tabel Kontak Nomor Telepon Petugas bertugas 4-kolom berpasangan dan blok tanda tangan resmi 2-kolom (Koordinator & Wadir Medis & Keperawatan).
- **Collapsible Sidebar**: Menu navigasi sidebar modern yang dapat dilipat (*collapsible*) melalui tombol hamburger di navbar desktop/mobile dengan scroll terpisah dan auto-scroll prevention.
- **Dynamic Header & 2-Tier Card Layout**: Layout header dua tingkat yang responsif untuk judul halaman, breadcrumb, serta tombol aksi (*action buttons*) agar tampilan rapi tanpa overflow. Sinkronisasi dinamis judul tab browser dengan nama instansi **RS Bintang Amin**.
- **Engine Kalkulasi Otomatis PPh 21 (TER & Pasal 17)**:
  - Perhitungan otomatis PPh 21 menggunakan skema Tarif Efektif Rata-Rata (TER A, B, C) untuk bulanan dan Tarif Pasal 17 UU HPP untuk Rekonsiliasi Akhir Tahun (Desember YTD).
  - Pengelolaan Master Aturan Pajak dan PTKP yang fleksibel serta pembentukan rincian potongan pajak otomatis pada Slip Gaji.
- **Payroll & Slip Gaji Digital (Background Queue & Scheduler)**:
  - Tampilan tabel slip gaji interaktif dengan grid solid garis pemisah tegas (black/dark double-line separator).
  - Cetak langsung (*print layout*) dengan styling CSS mandiri (instan tanpa delay CDN).
  - Pengiriman massal slip gaji via email secara *non-blocking* menggunakan **Background Queue** (`SendPayrollSlipJob`) dan indikator status pengiriman *real-time* (polling UI & progres bar).
  - Log pengiriman slip gaji terintegrasi (`PayrollSendLog`) untuk pemantauan audit email yang berhasil/gagal dikirim.
  - Perintah Artisan otomatis (`app:send-scheduled-payroll-slips`) dengan eksekusi dinamis melalui Laravel Scheduler.
  - Dilengkapi lampiran dokumen **PDF Slip Gaji** otomatis menggunakan library `barryvdh/laravel-dompdf`.
- **Komponen Tunjangan & Potongan Penggajian**:
  - Pengelolaan Master Tunjangan Jabatan, Tunjangan Lain-Lain, Denda Keterlambatan Flat, dan Rekening Bank Karyawan.
  - Fitur Ekspor & Impor Excel untuk slip gaji bulanan serta rincian modal potongan/tunjangan.
  
  - Matriks Golongan dinamis dan pencatatan Log Edit Payroll (*Audit Trail*) untuk transparansi perubahan nilai gaji.
- **Audit Log Koreksi Absensi**: Pencatatan riwayat perubahan/koreksi absensi karyawan (`sdm_absensi_koreksi_log`) yang dilengkapi modal audit log interaktif dengan pencarian dan paginasi pada tampilan Rekap Absensi.
- **Backfill & Optimasi Kinerja Absensi**: Perintah CLI `app:backfill-absensi-metrics` dan pembuatan indeks tabel database untuk mempercepat kalkulasi rekapitulasi absensi dan performa kueri.
- **Master Spesialis Dokter & Struktur Organisasi**: Seeder data komprehensif untuk struktur organisasi rumah sakit (`StrukturOrganisasiSeeder`) dan akun/role Dokter (`DokterSeeder`), serta pembaharuan otorisasi hak akses (Spatie permission) untuk Wadir SDM dan Wadir Keuangan.
- **Izin & Cuti Refactoring**: Pembaharuan nama istilah dari "Cuti" menjadi "Izin dan Cuti" pada seluruh modul, modal, dan seeder, serta dilengkapi command reset kuota cuti tahunan (`app:reset-cuti`).
- **Profil Karyawan & BPJS**: Pencatatan nomor kepesertaan BPJS Kesehatan dan BPJS Ketenagakerjaan yang terintegrasi dengan migrasi database.
- **Docstore & Digital Signature**: Integrasi Docstore untuk audit dokumen, resinkronisasi dokumen (`app:docstore-sync-all`, `app:docstore-resync`), penerbitan QR Header Sistem, serta enkripsi dan verifikasi tanda tangan digital dokumen persuratan.
- **Konversi Satuan (UoM)**: Kemampuan untuk menyimpan satuan dasar dan satuan konversi tambahan pada Master Barang. Transaksi Pembelian Langsung akan secara otomatis mengkonversi jumlah barang dan nominal harganya (misal: 1 Box = 16 Pcs) agar mempermudah perhitungan stok.
- **Laporan Kepegawaian & Ekspor Data**: Modul laporan kepegawaian komprehensif berbasis tab interaktif dengan filter pencarian, ekspor format Excel/CSV, serta cetak dokumen (*print view*).
- **Notification System (Tandai Dibaca)**: Fitur notifikasi yang interaktif dengan opsi menandai dibaca per notif atau tandai semua dibaca, lengkap dengan *badge bell indicator* dinamis (realtime event updates).

## Prerequisite
- **PHP**: `^8.2` (atau mengikuti Laravel)
- **Laravel Framework**: [Laravel 12](https://laravel.com/docs/12.x)
- **Database**: MySQL / MariaDB
- **TallStackUI**: [TallStackUI](https://tallstackui.com/docs/v2) 
- **Livewire**: [Livewire](https://livewire.laravel.com/docs/quickstart)
- **Filament Table**: [Filament Table](https://filamentphp.com/docs/3.x/tables/installation) (untuk filter & listing data)
- **Tailwind CSS**: [Tailwind 3](https://v3.tailwindcss.com/docs/installation)
- **Tabler Icons**: [Tabler Icon](https://tabler.io/icons) menggunakan library `secondnetwork/blade-tabler-icons`
- **PDF Engine**: [Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) (`barryvdh/laravel-dompdf`)

## Langkah Instalasi & Konfigurasi

### 1. Clone Repository & Install Dependencies
```bash
composer install
npm install
npm run dev
```

### 2. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env` dan konfigurasikan database serta mail SMTP untuk pengiriman slip gaji:
```ini
APP_NAME="RS Bintang Amin"

# Konfigurasi Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Queue Driver (disarankan 'database' untuk async queue)
QUEUE_CONNECTION=database

# Konfigurasi Mail SMTP (Contoh Gmail)
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=sandi_aplikasi_16_karakter
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="email_anda@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Migrasi & Seeding Database
Jalankan migrasi untuk membuat tabel (termasuk kolom BPJS, log koreksi absensi, log pengiriman payroll, dan notifikasi) serta jalankan seeder untuk mengisi data awal:
```bash
php artisan migrate:fresh --seed
```

### 4. Jalankan Queue Worker & Scheduler (Penting untuk Payroll & Email)
Untuk memproses antrean email slip gaji dan jadwal otomatis:
```bash
# Jalankan queue worker
php artisan queue:work

# Jalankan scheduler di lingkungan pengembangan
php artisan schedule:work
```

### 5. Perintah Artisan Kustom
```bash
# Pengiriman slip gaji terjadwal
php artisan app:send-scheduled-payroll-slips

# Backfill metrik absensi
php artisan app:backfill-absensi-metrics

# Reset kuota cuti tahunan
php artisan app:reset-cuti

# Sinkronisasi & Resync Docstore
php artisan app:docstore-sync-all
php artisan app:docstore-resync
```

### 6. Clear Cache (Penting setelah edit `.env`)
Jika melakukan perubahan konfigurasi pada file `.env`, jalankan perintah berikut:
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🧪 Skenario Pengujian & Validasi Fitur Otomatis (39/39 Passed)

Aplikasi **RSBA Office** telah divalidasi menggunakan suite pengujian komprehensif yang mencakup seluruh pilar SIM-SDM, alur otorisasi multi-aktor, tanda tangan digital, payroll, dan akreditasi rumah sakit. Seluruh 39 kasus pengujian telah berhasil diverifikasi (**100% Passed**).

### 👥 1. Matriks 10 Aktor Pengujian & Direct Spatie Permissions
Pengujian menerapkan arsitektur otorisasi berbasis *Direct Spatie Permissions* yang dikombinasikan dengan jenjang jabatan struktural (Tingkat 1 - 5):

| No | Aktor | Email Akun | Password | Tingkat Jabatan | Direct Permissions | Cakupan Pengujian & Tanggung Jawab |
|:---|:---|:---|:---|:---|:---:|:---|
| 1 | **Super-Admin** | `admin@rsba.com` | `1234` | - | *Bypass All* | Administrasi sistem, konfigurasi master, & bypass penuh |
| 2 | **Direktur Utama** | `direktur@rsba.test` | `password123` | Tingkat 1 | 24 | Final Signatory SK/Cuti, Kebijakan, & Laporan RS |
| 3 | **Wadir Medis & Keperawatan** | `wadir@rsba.test` | `password123` | Tingkat 2 | 28 | Approval Jadwal Tingkat 2 (Final), Cuti Medis, Payroll |
| 4 | **Kabid Keperawatan** | `kabid@rsba.test` | `password123` | Tingkat 3 | 23 | Approval Jadwal Tingkat 1, Verifikasi Cuti Perawat |
| 5 | **Kepala Ruangan IGD (Karu)** | `karu@rsba.test` | `password123` | Tingkat 4 | 19 | Pembuat & Pengatur Grid Shift IGD, Review Cuti Staf |
| 6 | **Perawat Pelaksana IGD** | `staf@rsba.test` | `password123` | Tingkat 5 | 10 | Lihat Jadwal Dinas Personal, Ajukan Cuti, Slip Gaji |
| 7 | **Dokter Jaga IGD** | `dokter@rsba.test` | `password123` | Tingkat 5 | 10 | Jadwal Jaga Medis, Pengajuan Cuti Dokter |
| 8 | **Staff SDM Officer** | `sdm@rsba.test` | `password123` | Tingkat 5 | 94 | Pengelola Data Pegawai, Import Absensi, Generate Payroll |
| 9 | **Staff Keuangan** | `keuangan@rsba.test` | `password123` | Tingkat 5 | 21 | Approval Final Gaji, Verifikasi SP3 Finansial |
| 10 | **Assessor Akreditasi** | `akreditasi@rsba.test` | `password123` | Tingkat 5 | 9 | Modul Akreditasi RS & Download Dokumen / Chapter |

---

### 📊 2. Matriks 9 Modul & 39 Kasus Uji Otomatis

```
========================================================================================
HASIL PENGUJIAN OTOMATIS: 39 / 39 TEST CASES BERHASIL (100% SUKSES)
========================================================================================
```

| Modul | Kasus Uji (Test Case) | Status | Hasil Validasi & Ekspektasi |
|:---|:---|:---:|:---|
| **Master Data** | 1. Master Bagian | `PASSED` | 4 Bagian struktural (Medis, Keperawatan, SDM, Keuangan) terdaftar |
| | 2. Master Ruangan | `PASSED` | 5 Ruangan aktif (IGD, ICU, Dahlia, SDM, Keuangan) terdaftar |
| | 3. Master Jabatan (Tingkat 1-5) | `PASSED` | Relasi `parent_id` hierarkis & tunjangan jabatan tervalidasi |
| | 4. Master Shift Kerja | `PASSED` | 4 Shift standar (Pagi, Siang, Malam, Reguler) terkonfigurasi |
| | 5. Ruangan-Shift Mapping | `PASSED` | Pemetaan shift aktif per ruangan terpasang |
| | 6. Master Jenis Cuti | `PASSED` | 4 Jenis cuti resmi (Tahunan, Sakit, Melahirkan, Alasan Penting) |
| | 7. Spesialisasi Dokter | `PASSED` | Dokter Umum & Spesialis Bedah terhubung ke profil medis |
| **Aktor & Auth** | 8. Super-Admin Permission | `PASSED` | Bypass seluruh Gate & Spatie Permission |
| | 9. Direktur Utama Permission | `PASSED` | 24 Permissions (TTE & Laporan Eksekutif) |
| | 10. Wadir Medis Permission | `PASSED` | 28 Permissions (`approve-jadwal-wadir`, cuti, payroll) |
| | 11. Kabid Keperawatan Permission | `PASSED` | 23 Permissions (`approve-jadwal-kabid`, cuti perawat) |
| | 12. Karu IGD Permission | `PASSED` | 19 Permissions (CRUD Jadwal Shift IGD, Cuti Staf) |
| | 13. Perawat Pelaksana Permission | `PASSED` | 10 Permissions (Jadwal saya, Ajukan cuti, Slip gaji) |
| | 14. Dokter Jaga Permission | `PASSED` | 10 Permissions (Jadwal jaga, Cuti dokter) |
| | 15. Staff SDM Permission | `PASSED` | 94 Permissions (CRUD Pegawai, Import Absen, Payroll) |
| | 16. Staff Keuangan Permission | `PASSED` | 21 Permissions (Approval Payroll, Verifikasi SP3) |
| | 17. Assessor Akreditasi Permission | `PASSED` | 9 Permissions (View & Download Dokumen Akreditasi) |
| **Jadwal Kerja** | 18. Pembuatan Draft Jadwal | `PASSED` | Karu membuat jadwal shift bulanan & status menjadi `draft` |
| | 19. Pengajuan Step 1 ke Kabid | `PASSED` | Status `menunggu_kabid` & target approver resolusi otomatis |
| | 20. Approval Step 2 oleh Kabid | `PASSED` | Status `menunggu_wadir` & metadata `diketahui_oleh` tercatat |
| | 21. Approval Final Step 3 oleh Wadir | `PASSED` | Status `published` & metadata `disetujui_oleh` tercatat |
| | 22. Ekspor PDF Jadwal Resmi | `PASSED` | HTTP 200, Layout Landscape A4, Header Logo RSBA & 2 Blok TTD |
| **Absensi** | 23. Registrasi PIN Karyawan | `PASSED` | PIN `IGD02` terhubung valid dengan profil karyawan |
| | 24. Presensi Tepat Waktu | `PASSED` | Tap `06:55` (Shift `07:00`) terhitung *Tepat Waktu* |
| | 25. Presensi Terlambat | `PASSED` | Tap `07:25` terhitung *Terlambat 25 Menit* secara presisi |
| **Persuratan & Cuti** | 26. Alur Cuti Berjenjang | `PASSED` | Pengajuan disetujui Karu & Kabid &rarr; Status `approved` |
| | 27. Deduksi Saldo Cuti Otomatis | `PASSED` | Kuota cuti tahunan otomatis terpotong 3 hari (12 &rarr; 9 hari) |
| | 28. Surat Perintah Tugas (SPT) | `PASSED` | Nomor `001/SPT/RSBA/IX/2026` terbit dan tersimpan |
| | 29. Surat Balasan PKL | `PASSED` | Terbit dengan data universitas & snapshot biaya mahasiswa |
| | 30. Surat Balasan Penelitian | `PASSED` | Terbit untuk riset klinis mahasiswa kedokteran |
| | 31. Surat SP3 Finansial | `PASSED` | Terbit untuk pengadaan sarana/alat medis rumah sakit |
| **TTE & QR Portal** | 32. Sequential TTE Signing | `PASSED` | TTD berurutan Kabid & Direktur &rarr; Status `signed` |
| | 33. Verifikasi QR Publik | `PASSED` | Endpoint `/verifikasi-surat/{hash}` merespons valid tanpa login |
| **Payroll & PPh 21** | 34. Kalkulasi Pajak TER A | `PASSED` | Bruto Rp 5.000.000 &times; TER 0.25% = PPh21 Rp 12.500 (Gaji Bersih Rp 4.987.500) |
| | 35. Period Lock Gaji | `PASSED` | Penguncian status periode penggajian untuk audit integritas |
| **Jasmed & Laporan** | 36. Modul Jasa Medis | `PASSED` | Endpoint `/kepegawaian/jasmed` aktif untuk klaim BPJS/Tunai |
| | 37. Modul Laporan SDM | `PASSED` | Endpoint `/kepegawaian/laporan` aktif untuk rekapitulasi data |
| **Akreditasi** | 38. Modul Akreditasi RS | `PASSED` | Endpoint `/kepegawaian/akreditasi` siap untuk bab & EP standar |
| | 39. Download Chapter & File | `PASSED` | Rute controller download dokumen akreditasi siap pakai |

---

### 🚀 3. Panduan Menjalankan Pengujian Otomatis

#### A. Eksekusi Melalui PHP Artisan CLI
```bash
# 1. Jalankan Seeder Multi-Aktor & Master Data Pengujian
php artisan db:seed --class=MultiActorTestSeeder

# 2. Jalankan Validator Pengujian Otomatis
php artisan tinker --execute="(new \Tests\Feature\ComprehensiveFeatureValidator())->runAll();"
```

#### B. Eksekusi Melalui Docker Isolated Testing Environment (Port 2023)
Aplikasi menyediakan *Docker environment* terisolasi yang dapat dijalankan secara instan:
```bash
# 1. Nyalakan Docker Container
docker compose up -d

# 2. Jalankan Seeder & Feature Tests di dalam Container
docker compose exec app php artisan db:seed --class=MultiActorTestSeeder
docker compose exec app php artisan test --filter=ComprehensiveFeatureValidator
```

#### C. Rekaman Playwright UI End-to-End Walkthrough
Tersedia skrip otomasi browser Playwright (`record_test_video.mjs`) untuk merekam alur antarmuka pengguna:
```bash
# Menjalankan rekaman UI walkthrough (menghasilkan video WebM HD)
node record_test_video.mjs
```

---

## 📄 Lisensi
Aplikasi ini berlisensi di bawah [MIT license](https://opensource.org/licenses/MIT).

