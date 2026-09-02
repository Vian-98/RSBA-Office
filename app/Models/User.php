<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratSp3Approval;
use App\Models\Sdm\RuanganKoordinator;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'karyawan_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'read_notifications' => 'array',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->karyawan?->nama
            ?? $this->karyawan?->full_nama
            ?? ($this->email ? explode('@', $this->email)[0] : 'User');
    }

    function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function certificate(): HasMany
    {
        return $this->hasMany(SignatureCerts::class, 'user_id', 'id');
    }

    public function approval(): HasMany
    {
        return $this->hasMany(SuratSp3Approval::class, 'disetujui', 'id');
    }

    /**
     * Relasi ke tabel penugasan koordinator (via user_id)
     */
    public function koordinatorRuangans(): HasMany
    {
        return $this->hasMany(RuanganKoordinator::class, 'user_id')->where('aktif', true);
    }

    /**
     * Cek apakah user ini memiliki hak akses Super-Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super-Admin') || rescue(fn () => $this->hasPermissionTo('super-admin-bypass'), false, false);
    }

    /**
     * Cek apakah user ini merupakan koordinator di ruangan manapun
     */
    public function isKoordinator(): bool
    {
        if ($this->isSuperAdmin() || $this->hasRole('Koordinator') || $this->hasRole('Koordinator-Dokter')) {
            return true;
        }

        if ($this->koordinatorRuangans()->exists()) {
            return true;
        }

        // Auto-check dari Jabatan Level 4 (Koordinator) / is_penyusun_jadwal
        $karyawan = $this->karyawan;
        if ($karyawan) {
            $hasKoorJabatan = $karyawan->jabatan()
                ->whereHas('tingkat', function ($q) {
                    $q->where('is_penyusun_jadwal', true)->orWhere('urutan', 4);
                })
                ->exists();

            if ($hasKoorJabatan) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah user ini merupakan Kepala Bagian / Kepala Bidang / Kepala Dept (Tingkat 3)
     */
    public function isKepalaDept(): bool
    {
        if ($this->isSuperAdmin() || $this->hasRole('Kepala-Bidang')) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            return $karyawan->jabatan()
                ->whereHas('tingkat', function ($q) {
                    $q->where('urutan', 3);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Cek apakah user ini merupakan Wakil Direktur (Tingkat 2)
     */
    public function isWadir(): bool
    {
        if ($this->isSuperAdmin() || $this->hasRole('Wadir') || $this->hasRole('Wakil-Direktur') || $this->hasRole('Wadir-Medis-Keperawatan') || $this->hasRole('Wadir-SDM-Umum') || $this->hasRole('Wadir-Keuangan')) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            return $karyawan->jabatan()
                ->whereHas('tingkat', function ($q) {
                    $q->where('urutan', 2);
                })
                ->exists();
        }

        return false;
    }

    public function isKabagSDM(): bool
    {
        if ($this->hasRole('Staff-SDM') || $this->hasRole('Ka. SDM') || rescue(fn () => $this->hasPermissionTo('view-kepegawaian-karyawan'), false, false)) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            $jabatan = $karyawan->jabatan->first();
            if ($jabatan && $this->isKepalaDept()) {
                $namaJ = strtolower($jabatan->nama ?? '');
                $namaB = strtolower($jabatan->bagian?->nama ?? '');
                return str_contains($namaJ, 'sdm') || str_contains($namaJ, 'kepegawaian') || str_contains($namaB, 'sdm');
            }
        }

        return false;
    }

    public function isKabagUmum(): bool
    {
        if ($this->hasRole('Bagian-Umum') || $this->hasRole('Ka. Umum') || rescue(fn () => $this->hasPermissionTo('manage-umum-asset'), false, false)) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            $jabatan = $karyawan->jabatan->first();
            if ($jabatan && $this->isKepalaDept()) {
                $namaJ = strtolower($jabatan->nama ?? '');
                $namaB = strtolower($jabatan->bagian?->nama ?? '');
                return str_contains($namaJ, 'umum') || str_contains($namaJ, 'sarpras') || str_contains($namaB, 'umum');
            }
        }

        return false;
    }

    public function isKabagKeuangan(): bool
    {
        if ($this->hasRole('Keuangan') || $this->hasRole('Pajak') || rescue(fn () => $this->hasPermissionTo('view-keuangan-hutang'), false, false)) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            $jabatan = $karyawan->jabatan->first();
            if ($jabatan && $this->isKepalaDept()) {
                $namaJ = strtolower($jabatan->nama ?? '');
                $namaB = strtolower($jabatan->bagian?->nama ?? '');
                return str_contains($namaJ, 'keuangan') || str_contains($namaB, 'keuangan');
            }
        }

        return false;
    }

    /**
     * Sinkronkan Role Spatie akun berdasarkan Jabatan Karyawan
     */
    public function syncRoleFromJabatan(): void
    {
        $karyawan = $this->karyawan;
        if (!$karyawan) return;

        $jabatanAktif = $karyawan->jabatan()->first();
        if (!$jabatanAktif) return;

        $targetRoleName = $jabatanAktif->resolveTargetRoleName();

        if ($targetRoleName && !$this->can('super-admin-bypass')) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $targetRoleName]);
            $this->syncRoles([$role]);
        }

        // Hapus cache sidebar permissions user agar menu langsung ter-refresh
        cache()->forget('user-permissions:view:' . $this->id);
    }

    /**
     * Dapatkan daftar ruangan_id yang dinaungi oleh bagian dari jabatan aktif user (khusus Kabid/Kepala Bagian).
     * Return null jika Super-Admin/Staff-SDM/Wadir/Direktur (akses semua ruangan).
     */
    public function getBagianScopedRuanganIds(): ?array
    {
        if ($this->isSuperAdmin() || $this->isWadir() || $this->can('view-kepegawaian-karyawan')) {
            return null; // null = akses semua ruangan
        }

        $karyawan = $this->karyawan;
        if ($karyawan) {
            $bagianId = $karyawan->active_bagian_id;
            if ($bagianId) {
                return \App\Models\Ruangan::where('bagian_id', $bagianId)
                    ->pluck('id')
                    ->toArray();
            }
        }

        return [];
    }

    /**
     * Return the effective departments of the user's active assignments.
     * Assignment-level department takes precedence over the job master default.
     */
    public function getActiveBagianIds(): array
    {
        return $this->karyawan?->jabatan
            ?->map(fn ($jabatan) => $jabatan->pivot?->bagian_id ?? $jabatan->bagian_id)
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];
    }

    /**
     * Ruangan aktif milik karyawan yang terhubung ke user ini.
     *
     * Ruangan utama tetap dipertahankan sebagai fallback karena beberapa
     * data lama belum memiliki baris pada sdm_kary_ruangan.
     */
    public function getOwnRuanganIds(): array
    {
        $karyawan = $this->karyawan;

        if (!$karyawan || $karyawan->resign_at) {
            return [];
        }

        $ids = $karyawan->ruangans()->pluck('ruangan.id')->all();

        if ($karyawan->ruangan_id) {
            $ids[] = (int) $karyawan->ruangan_id;
        }

        return collect($ids)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Dapatkan daftar ruangan_id yang dapat diakses user berdasarkan tingkat wewenang (permission Spatie):
     * 1. Global Scope (approve-jadwal-wadir, super-admin-bypass) -> return null (akses semua)
     * 2. Department Scope (approve-jadwal-kabid) -> return array ruangan_id di bawah bagian aktifnya
     * 3. Room Scope (edit-kepegawaian-jadwal-kerja / penugasan koordinator) -> return array ruangan_id yang dipimpin
     * 4. Member Scope (view-kepegawaian-jadwal-kerja / default) -> return array ruangan_id tempat user ditugaskan
     */
    public function getAccessibleRuanganIds(?string $ability = 'view'): ?array
    {
        // 1. Global Scope: Wadir, Super-Admin, SDM Pusat
        if ($this->isSuperAdmin() 
            || rescue(fn () => $this->hasPermissionTo('super-admin-bypass'), false, false) 
            || rescue(fn () => $this->hasPermissionTo('approve-jadwal-wadir'), false, false)) {
            return null;
        }

        // 2. Department Scope: Kepala Bidang / Kepala Bagian
        if (rescue(fn () => $this->hasPermissionTo('approve-jadwal-kabid'), false, false) || $this->isKepalaDept()) {
            $bagianIds = $this->getActiveBagianIds();
            if ($this->karyawan?->active_bagian_id && !in_array((int)$this->karyawan->active_bagian_id, $bagianIds)) {
                $bagianIds[] = (int) $this->karyawan->active_bagian_id;
            }
            if (!empty($bagianIds)) {
                return \App\Models\Ruangan::whereIn('bagian_id', $bagianIds)->pluck('id')->map(fn($id) => (int)$id)->toArray();
            }
        }

        // 3. Room Scope: Koordinator Ruangan (Karu / Dokter Jaga)
        if ($this->isKoordinator()) {
            $koorRuangans = $this->getRuanganKoordinatorIdsOnly();
            if (!empty($koorRuangans)) {
                return $koorRuangans;
            }
        }

        // 4. Member / Staff Scope: Ruangan penugasan sendiri
        return $this->getOwnRuanganIds();
    }

    /**
     * Dapatkan daftar ruangan_id yang murni dikoordinasikan oleh user ini (tanpa fallback null global)
     */
    public function getRuanganKoordinatorIdsOnly(): array
    {
        $idsFromPivot = $this->koordinatorRuangans()->pluck('ruangan_id')->map(fn($id) => (int)$id)->toArray();

        $karyawan = $this->karyawan;
        if ($karyawan) {
            $hasKoorJabatan = $karyawan->jabatan()
                ->whereHas('tingkat', function ($q) {
                    $q->where('is_penyusun_jadwal', true)->orWhere('urutan', 4);
                })
                ->exists();

            if ($hasKoorJabatan) {
                $assignedRooms = $karyawan->ruangans()->pluck('ruangan.id')->map(fn($id) => (int)$id)->toArray();
                if ($karyawan->ruangan_id) {
                    $assignedRooms[] = (int) $karyawan->ruangan_id;
                }
                return array_values(array_unique(array_merge($idsFromPivot, $assignedRooms)));
            }
        }

        return array_values(array_unique($idsFromPivot));
    }

    /**
     * Cek apakah user berhak mengelola/mengedit ruangan tertentu
     */
    public function canManageRuangan(int $ruanganId): bool
    {
        if ($this->isSuperAdmin() 
            || rescue(fn () => $this->hasPermissionTo('super-admin-bypass'), false, false) 
            || rescue(fn () => $this->hasPermissionTo('approve-jadwal-wadir'), false, false)) {
            return true;
        }

        // KaBid
        if (rescue(fn () => $this->hasPermissionTo('approve-jadwal-kabid'), false, false) || $this->isKepalaDept()) {
            $deptRooms = $this->getBagianScopedRuanganIds() ?? [];
            return in_array($ruanganId, $deptRooms, true);
        }

        // Koordinator Ruangan
        if ($this->isKoordinator()) {
            return in_array($ruanganId, $this->getRuanganKoordinatorIdsOnly(), true);
        }

        return false;
    }

    /**
     * Dapatkan daftar ruangan_id yang dikoordinasi user ini.
     * Return null jika Global Approver (Wadir/Super-Admin/SDM)
     */
    public function getRuanganKoordinatorIds(): ?array
    {
        if ($this->isSuperAdmin() 
            || rescue(fn () => $this->hasPermissionTo('super-admin-bypass'), false, false) 
            || rescue(fn () => $this->hasPermissionTo('approve-jadwal-wadir'), false, false)) {
            return null; // null = akses semua ruangan
        }

        if (rescue(fn () => $this->hasPermissionTo('approve-jadwal-kabid'), false, false)) {
            $bagianScoped = $this->getBagianScopedRuanganIds();
            if ($bagianScoped !== null) {
                return $bagianScoped;
            }
        }

        return $this->getRuanganKoordinatorIdsOnly();
    }

    /**
     * Cek apakah user ini merupakan Dokter atau Manajemen Medis/SDM (Wadir/SDM/Super-Admin)
     */
    public function isDokterOrApprover(): bool
    {
        if ($this->isSuperAdmin() || $this->isDokter() || $this->isWadir() || $this->can('view-kepegawaian-jadwal-kerja')) {
            return true;
        }

        if (!$this->karyawan_id) {
            return false;
        }

        if (\Illuminate\Support\Facades\DB::table('dokter')->where('karyawan_id', $this->karyawan_id)->exists()) {
            return true;
        }

        $karyawan = $this->karyawan;
        if ($karyawan && (str_contains(strtolower($karyawan->gelar_depan ?? ''), 'dr') || str_contains(strtolower($karyawan->gelar_belakang ?? ''), 'sp'))) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah karyawan dari user ini adalah Dokter
     */
    public function isDokter(): bool
    {
        if (!$this->karyawan_id) {
            return false;
        }
        if (\App\Models\Sdm\Dokter::where('karyawan_id', $this->karyawan_id)->exists()) {
            return true;
        }
        $karyawan = $this->karyawan;
        if ($karyawan) {
            $gelarDepan = strtolower($karyawan->gelar_depan ?? '');
            $gelarBelakang = strtolower($karyawan->gelar_belakang ?? '');
            $nama = strtolower($karyawan->nama ?? '');
            if (str_contains($gelarDepan, 'dr') || str_contains($gelarBelakang, 'sp') || str_starts_with($nama, 'dr.') || str_starts_with($nama, 'dr ')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cek apakah user ini adalah Koordinator yang berprofesi Dokter
     */
    public function isKoordinatorDokter(): bool
    {
        return $this->isKoordinator() && $this->isDokter();
    }

    /**
     * Cek apakah user ini adalah Koordinator Ruangan Karyawan (Non-Dokter)
     */
    public function isKoordinatorKaryawan(): bool
    {
        return $this->isKoordinator() && !$this->isDokter();
    }
}
