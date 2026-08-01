# 📝 Draft Status Pengerjaan – UAT Modul SDM
**Proyek:** RSBA Office  
**Tanggal:** 15 Juli 2026  

---

## ✅ SUDAH BERHASIL

### A. Persiapan & Analisis
- [x] Menelusuri semua route SDM di `routes/kepegawaian.php`
- [x] Memetakan semua komponen Livewire di `app/Livewire/Karyawan/` dan `app/Livewire/Kepegawaian/`
- [x] Mengidentifikasi fungsi-fungsi utama: Tambah, Edit Identitas, Edit Kedinasan, Resign, Hapus, Upload Dokumen, Import, Rekonsiliasi Absensi, Approval Cuti (digital signature), dsb.
- [x] Mengetahui kredensial akun uji: `sdm@rsba.com` / `1234`

### B. Pembuatan Halaman Black Box Testing
- [x] File `public/uat-sdm.html` dibuat dan dapat diakses di [http://office.test/uat-sdm.html](http://office.test/uat-sdm.html)
- [x] **101 Test Case** dari 11 modul SDM:
  - [x] Data Karyawan (20 TC)
  - [x] Master Jabatan (6 TC)
  - [x] Master Bagian (6 TC)
  - [x] Master Jadwal Shift (6 TC)
  - [x] Jadwal Kerja (10 TC)
  - [x] Absensi (17 TC)
  - [x] Surat Cuti (12 TC)
  - [x] Surat SP (4 TC)
  - [x] Penggajian / Payroll (11 TC)
  - [x] Jasa Medis (4 TC)
  - [x] Master Tunjangan (5 TC)
- [x] Kolom per TC: ID, Fungsi, Prioritas, Input Data Uji, Expected Output, Actual Output, Status, Bug Severity, Catatan
- [x] Fitur: Progress bar real-time, sidebar badge per modul, auto-save localStorage, Export CSV
- [x] Halaman COA (Certificate of Acceptance) Sign-off: info tester/reviewer/manajer, kesimpulan, blok tanda tangan

### C. UAT Otomatis – Smoke Test (16 TC)
- [x] Login sebagai `sdm@rsba.com` → **PASS**
- [x] Halaman Daftar Karyawan `/kepegawaian/karyawan` → **PASS**
- [x] Pencarian karyawan (search bar) → **PASS**
- [x] Master Jabatan `/kepegawaian/master/jabatan` → **PASS**
- [x] Master Bagian `/kepegawaian/master/bagian` → **PASS**
- [x] Master Jadwal Shift `/kepegawaian/master/jadwal-shift` → **PASS**
- [x] Jadwal Kerja `/kepegawaian/jadwal-kerja` → **PASS**
- [x] Absensi Dashboard `/kepegawaian/absensi` → **PASS**
- [x] Absensi Import `/kepegawaian/absensi/import` → **PASS**
- [x] Absensi Rekap `/kepegawaian/absensi/rekap` → **PASS**
- [x] Surat Cuti `/kepegawaian/surat/cuti` → **PASS**
- [x] Surat SP3 `/kepegawaian/surat/sp3` → **PASS**
- [x] Rekap Gaji `/kepegawaian/gaji` → **PASS**
- [x] Jasa Medis `/kepegawaian/jasmed` → **PASS**
- [x] Master Tunjangan Golongan `/kepegawaian/master/tunjangan-golongan` → **PASS**
- [x] Konfigurasi Jadwal `/kepegawaian/konfigurasi-jadwal` → **PASS**

> **Hasil: 16/16 PASS · Pass Rate 100% · Tidak ada error 403/404/500**

### D. Perbaikan Bug
- [x] **Bug ditemukan:** `ArgumentCountError` di `WilayahController.php`
  - Penyebab: Route `/api/kab/{id?}`, `/api/kec/{id?}`, `/api/desa/{id?}` menggunakan parameter opsional, tapi controller tidak punya default value → dropdown wilayah crash di form Tambah/Edit Karyawan
- [x] **Solusi diterapkan:** Parameter `$id` dijadikan opsional (`$id = null`) + guard empty check + dual-mode (listing by parent code / fetch by own code)
- [x] File `app/Http/Controllers/WilayahController.php` sudah diperbaiki dan berfungsi

---

## ❌ BELUM SELESAI

### A. Deep Testing Fungsional (Klik Tombol & Form)
- [ ] **Tambah Karyawan Baru** — mengisi form, klik Simpan, verifikasi toast sukses
- [ ] **Validasi form Tambah Karyawan kosong** — submit tanpa isi, tangkap pesan error
- [ ] **NIP duplikat** — verifikasi validasi unique
- [ ] **Edit Identitas** — ubah alamat, simpan, verifikasi toast
- [ ] **Domisili = KTP (checkbox)** — verifikasi field otomatis terisi
- [ ] **Edit Kedinasan – ganti Jabatan** — pilih jabatan baru, isi tgl, simpan
- [ ] **Edit Kedinasan – ganti Status Karyawan** — validasi tgl wajib
- [ ] **Upload Dokumen karyawan** — upload file, verifikasi tersimpan
- [ ] **Proses Resign** — isi form resign, verifikasi karyawan pindah ke tab Resign
- [ ] **Hapus karyawan** — konfirmasi → hapus, verifikasi redirect
- [ ] **Batal hapus** — klik Batal, verifikasi data tidak terhapus

### B. Master Data CRUD
- [ ] **Tambah Jabatan baru** — simpan, verifikasi muncul di list
- [ ] **Hapus Jabatan yang masih digunakan** — verifikasi error FK constraint
- [ ] **Tambah Bagian baru** — simpan, verifikasi
- [ ] **Assign Koordinator Bagian** — verifikasi tersimpan
- [ ] **Tambah Shift baru** — isi jam masuk/keluar/toleransi, simpan
- [ ] **Shift lintas hari** — centang flag, verifikasi logika jam

### C. Jadwal Kerja
- [ ] **Generate jadwal bulanan** — pilih ruangan + bulan, klik Generate
- [ ] **Kelola jadwal** — ubah shift per hari, simpan, verifikasi log perubahan
- [ ] **Publikasikan jadwal** — klik Publikasikan → konfirmasi
- [ ] **Edit jadwal LOCKED** — verifikasi error "Jadwal sudah terkunci"
- [ ] **Hapus jadwal DRAFT** — verifikasi hapus berhasil

### D. Absensi
- [ ] **Upload file Excel fingerprint** — upload file valid, verifikasi preview
- [ ] **Upload file format salah** — upload .pdf, verifikasi error validasi
- [ ] **Proses Import ke staging** — klik Proses, redirect ke rekonsiliasi
- [ ] **Rekonsiliasi – tautan manual** — match baris unmatched ke karyawan
- [ ] **Rekonsiliasi – abaikan baris**
- [ ] **Commit ke Jadwal Kerja** — verifikasi data absensi masuk ke JadwalKerjaDetail
- [ ] **Koreksi manual status absensi** — ubah status, verifikasi tersimpan

### E. Surat Cuti
- [ ] **Buat pengajuan cuti** — isi form, verifikasi sisa cuti berkurang
- [ ] **Cuti masa kerja < 1 tahun** — verifikasi toast warning
- [ ] **Cuti melebihi sisa jatah** — verifikasi validasi error
- [ ] **Approval – Setujui** (digital signature) — isi password, verifikasi status approved
- [ ] **Approval – Tolak** — verifikasi keterangan wajib, sisa cuti dikembalikan
- [ ] **Password digital signature salah** — verifikasi toast error
- [ ] **Cetak Surat Cuti** — verifikasi print dialog terbuka

### F. Penggajian
- [ ] **Generate slip gaji** — verifikasi komponen dihitung otomatis
- [ ] **Live preview kalkulasi** — isi gaji pokok, verifikasi BPJS & PPh21 otomatis berubah
- [ ] **Tambah item tunjangan dinamis** — add tunjangan lain-lain
- [ ] **Lock periode gaji** — verifikasi form ter-disable setelah lock
- [ ] **Export rekap gaji** — verifikasi file terdownload

### G. Halaman BBT – Isi Actual Output
- [ ] Actual output tiap TC diisi dengan hasil nyata dari pengujian
- [ ] Status setiap TC dipilih (Pass/Fail/NA)
- [ ] Bug Severity diisi untuk TC yang Fail
- [ ] COA Sign-off diisi dan disimpan
- [ ] Export CSV laporan final

---

## 📌 Tindak Lanjut

| Prioritas | Item |
|-----------|------|
| 🔴 Segera | Lanjutkan deep testing fungsional (form submit, validasi, CRUD) |
| 🟡 Penting | Isi Actual Output di [http://office.test/uat-sdm.html](http://office.test/uat-sdm.html) |
| 🟢 Opsional | Ekspor CSV & cetak COA sebagai dokumen penerimaan resmi |
