<h1 align="center">RSBA OFFICE</h1>

<p align="center">
  Aplikasi perkantoran (SIM-SDM & Internal Office) pada <b>Rumah Sakit Bintang Amin Lampung</b>.
</p>

## Fitur Utama & Pembaruan
- **Collapsible Sidebar**: Menu navigasi sidebar modern yang dapat dilipat (*collapsible*) melalui tombol hamburger di navbar desktop/mobile dengan scroll terpisah dan auto-scroll prevention.
- **Dynamic Header & Title**: Sinkronisasi dinamis judul halaman pada navbar (misal: "Profile", "Notifikasi", "Settings") dan browser tab title template menggunakan nama instansi **RS Bintang Amin**.
- **Notification System (Tandai Dibaca)**: Fitur notifikasi yang interaktif dengan opsi menandai dibaca per notif atau tandai semua dibaca, lengkap dengan *badge bell indicator* dinamis (realtime event updates).
- **Payroll & Slip Gaji Digital**:
  - Tampilan tabel slip gaji yang rapi dengan grid solid garis pemisah tegas (black/dark double-line separator).
  - Cetak langsung (*print layout*) dengan styling CSS mandiri (instan tanpa delay CDN).
  - Kirim slip gaji ke email karyawan secara otomatis via SMTP Gmail/Mailtrap.
  - Dilengkapi lampiran dokumen **PDF Slip Gaji** otomatis menggunakan library `barryvdh/laravel-dompdf`.
- **Izin & Cuti Refactoring**: Pembaharuan nama istilah dari "Cuti" menjadi "Izin dan Cuti" pada seluruh modul, modal, dan seeder.
- **Profil Karyawan & BPJS**: Pencatatan nomor kepesertaan BPJS Kesehatan dan BPJS Ketenagakerjaan yang terintegrasi dengan migrasi database.
- **Konversi Satuan (UoM)**: Kemampuan untuk menyimpan satuan dasar dan satuan konversi tambahan pada Master Barang. Transaksi Pembelian Langsung akan secara otomatis mengkonversi jumlah barang dan nominal harganya (misal: 1 Box = 16 Pcs) agar mempermudah perhitungan stok.
- **Docstore & Digital Signature**: Integrasi Docstore untuk audit dokumen, penerbitan QR Header Sistem, serta enkripsi dan verifikasi tanda tangan digital dokumen persuratan.
- **Laporan Kepegawaian & Ekspor Data**: Modul laporan kepegawaian komprehensif berbasis tab interaktif dengan filter pencarian, ekspor format Excel/CSV, serta cetak dokumen (*print view*).
- **Absensi & Rekonsiliasi**: Pencatatan dan alur *pairing* absensi fleksibel untuk berbagai *shift* (termasuk *cross-midnight*), deteksi *Single Punch*, dan pengelolaan konflik jadwal absensi.

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
Jalankan migrasi untuk membuat tabel (termasuk kolom BPJS dan notifikasi) serta jalankan seeder untuk mengisi data awal:
```bash
php artisan migrate:fresh --seed
```

### 4. Clear Cache (Penting setelah edit `.env`)
Jika melakukan perubahan konfigurasi pada file `.env`, jalankan perintah berikut:
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

## Lisensi
Aplikasi ini berlisensi di bawah [MIT license](https://opensource.org/licenses/MIT).
