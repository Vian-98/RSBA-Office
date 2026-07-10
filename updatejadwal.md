# Cek Status Fase 0, 1, 2 — Modul Jadwal Kerja Pegawai

> Dicek pada: 2026-07-09 | Berdasarkan panduan `panduan-jadwal-kerja-pegawai (1).md`

---

## ✅ FASE 0 — Master & Konfigurasi Dasar

### Migrations
| Item | Status |
|---|---|
| Kolom `kategori_kerja` di `sdm_karyawan` | ✅ SELESAI (migration sudah Ran) |
| Tabel `sdm_jadwal_shift` | ✅ SELESAI |
| Tabel `sdm_bagian_koordinator` | ✅ SELESAI (ada di daftar tabel DB) |
| Tabel `sdm_bagian_shift` | ✅ SELESAI (ada di daftar tabel DB) |
| Tabel `sdm_jadwal_aturan` | ✅ SELESAI (migration sudah Ran) |

### Enums
| Item | Status |
|---|---|
| `KategoriKerja` | ✅ SELESAI (`app/Enums/KategoriKerja.php` ada) |
| `KodeAturanJadwal` | ✅ SELESAI (`app/Enums/KodeAturanJadwal.php` ada) |
| `StatusJadwalKerja` | ✅ SELESAI (`app/Enums/StatusJadwalKerja.php` ada) |

### Model & Relasi
| Item | Status |
|---|---|
| `JadwalShift` model | ✅ SELESAI |
| `JadwalAturan` model | ✅ SELESAI |
| `Karyawan` — cast `kategori_kerja` + relasi `bagianKoordinasi()` + `isKoordinatorBagian()` | ✅ SELESAI (terverifikasi di file model) |
| `Bagian` — relasi `koordinators()` + `shiftValid()` | ✅ SELESAI (terverifikasi) |

### Service & Policy
| Item | Status |
|---|---|
| `AturanJadwalService` | ✅ SELESAI (terdeteksi via tinker) |
| `JadwalKerjaPolicy` + registrasi ke `AuthServiceProvider` | ✅ SELESAI (terdeteksi via tinker) |

### CRUD Master Livewire
| Item | Status |
|---|---|
| `Master/JadwalShift/{Index,Add,Edit}.php` | ✅ SELESAI (3 file ada) |
| `Master/BagianKoordinator/{Index,Add,Edit}.php` | ✅ SELESAI (3 file ada) |
| `Master/BagianShift/{Index,Add,Edit}.php` | ✅ SELESAI (3 file ada) |
| `Master/JadwalAturan/{Index,Add,Edit}.php` | ✅ SELESAI (3 file ada) |

### Lainnya
| Item | Status |
|---|---|
| Permission & assign ke role | ✅ SELESAI (sudah disinkronisasi ke Spatie + Super-Admin) |
| Data awal (min. 1 shift REGULER, 1 koordinator per bagian) | ⚠️ **PERLU DICEK MANUAL** — Seeder/data awal belum dikonfirmasi ada di DB |

### **Kesimpulan Fase 0: HAMPIR SELESAI** 🟡
Satu hal yang perlu dikonfirmasi secara manual: apakah data master (shift REGULER, koordinator bagian) sudah diisi lewat UI atau seeder.

---

## ✅ FASE 1 — Jadwal Kerja Bulanan

### Migrations
| Item | Status |
|---|---|
| `sdm_jadwal_kerja` (header bulanan) | ✅ SELESAI |
| `sdm_jadwal_kerja_detail` (baris harian) | ✅ SELESAI |

> ⚠️ **CATATAN DEVIASI**: Implementasi aktual menggunakan `ruangan_id` (bukan `bagian_id` seperti di panduan). Ini adalah keputusan desain yang disengaja — jadwal dikaitkan ke ruangan (misal: Ruang IGD) bukan ke bagian organisasi.

### Enums
| Item | Status |
|---|---|
| `StatusKehadiran` | ✅ SELESAI (`app/Enums/StatusKehadiran.php` ada) |

### Model
| Item | Status |
|---|---|
| `JadwalKerja` model | ✅ SELESAI (relasi ke `ruangan`, bukan `bagian`) |
| `JadwalKerjaDetail` model | ✅ SELESAI |

### Livewire Components
| Item | Status |
|---|---|
| `Kepegawaian/JadwalKerja/Index.php` — daftar jadwal | ✅ SELESAI |
| `Kepegawaian/JadwalKerja/Generate.php` — modal generate | ✅ SELESAI |
| `Kepegawaian/JadwalKerja/Kelola.php` — grid editor | ✅ SELESAI |
| `Profile/JadwalTugasSaya.php` (read-only) | ✅ SELESAI (terdeteksi via tinker) |

### Route, Permission, Menu
| Item | Status |
|---|---|
| Route `kepegawaian.jadwal-kerja.*` | ✅ SELESAI (ada di `routes/kepegawaian.php`) |
| Route `profile.jadwal-tugas-saya` | ✅ SELESAI |
| Menu sidebar "Jadwal & Kehadiran" | ✅ SELESAI (sudah dibuat & submenu lengkap) |
| Permission di Spatie | ✅ SELESAI |

### **Kesimpulan Fase 1: SELESAI** ✅
Seluruh item checklist sudah terimplementasi. Deviasi utama: `bagian_id` diganti `ruangan_id`.

---

## ❌ FASE 2 — Tukar Jadwal

### Migrations
| Item | Status |
|---|---|
| `sdm_jadwal_tukar` | ❌ **BELUM ADA** (tabel tidak ditemukan di DB) |
| `sdm_jadwal_tukar_approval` | ❌ **BELUM ADA** |

### Enums
| Item | Status |
|---|---|
| `PeranApprovalTukar` | ❌ **BELUM ADA** (tidak ditemukan di `app/Enums/`) |

### Model
| Item | Status |
|---|---|
| `JadwalTukar` model | ❌ **BELUM ADA** |
| `JadwalTukarApproval` model | ❌ **BELUM ADA** |

### Livewire Components
| Item | Status |
|---|---|
| `Kepegawaian/JadwalTukar/Add.php` | ❌ **BELUM ADA** |
| `Kepegawaian/JadwalTukar/Index.php` | ❌ **BELUM ADA** |
| `Kepegawaian/JadwalTukar/Approve.php` | ❌ **BELUM ADA** |

### Lainnya
| Item | Status |
|---|---|
| Tombol "Ajukan Tukar" di `JadwalTugasSaya.php` | ❌ **BELUM ADA** |
| Entri "Tukar Cepat" dari grid `Kelola.php` | ❌ **BELUM ADA** |
| Validasi & edge case (4.6) | ❌ **BELUM ADA** |
| Permission `view/add/edit-kepegawaian-jadwal-tukar` | ❌ **BELUM ADA** |
| Route `kepegawaian.jadwal-tukar.*` | ❌ **BELUM ADA** |

### **Kesimpulan Fase 2: BELUM DIMULAI** ❌
Tidak ada satu pun item dari checklist Fase 2 yang terimplementasi.

---

## Ringkasan Akhir

| Fase | Status | Keterangan |
|---|---|---|
| **Fase 0** | 🟡 95% | Semua file & kode ada; perlu verifikasi data awal di DB |
| **Fase 1** | ✅ 100% | Selesai, dengan deviasi desain (`ruangan` vs `bagian`) |
| **Fase 2** | ❌ 0% | Belum dimulai sama sekali |
| **Fase 3** | ⏸️ Di luar scope | Perlu Fase 1 & 2 stabil dulu |

---

## Yang Perlu Dikerjakan Selanjutnya

### Prioritas 1 — Verifikasi Data Fase 0
- Pastikan ada minimal **1 shift REGULER** yang sudah diisi lewat menu *Master Shift*.
- Pastikan ada minimal **1 koordinator per ruangan/bagian** yang aktif lewat menu *Bagian Koordinator*.

### Prioritas 2 — Implementasi Fase 2 (Tukar Jadwal)
Urutan pengerjaan yang disarankan:
1. Migration `sdm_jadwal_tukar` & `sdm_jadwal_tukar_approval`
2. Enum `PeranApprovalTukar`
3. Model `JadwalTukar` & `JadwalTukarApproval`
4. Method `approveTukar()` di `JadwalKerjaPolicy`
5. Livewire: `JadwalTukar/Add.php` (Jalur A & B)
6. Livewire: `JadwalTukar/Index.php` & `Approve.php`
7. Tambah tombol "Ajukan Tukar" ke `JadwalTugasSaya.php`
8. Tambah "Tukar Cepat" ke grid `Kelola.php` (untuk koordinator)
9. Route, permission, menu
