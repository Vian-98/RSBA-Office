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
    use Notifiable;

    use HasRoles {
        hasPermissionTo as traitHasPermissionTo;
    }

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
     * Cek apakah user ini merupakan koordinator di ruangan manapun
     */
    public function isKoordinator(): bool
    {
        if ($this->traitHasPermissionTo('edit-kepegawaian-jadwal-kerja')) {
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
        if ($this->traitHasPermissionTo('approve-jadwal-kabid')) {
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
        if ($this->traitHasPermissionTo('approve-jadwal-wadir')) {
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
        if ($this->traitHasPermissionTo('view-kepegawaian-karyawan')) {
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
        if ($this->traitHasPermissionTo('manage-umum-asset')) {
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
        if ($this->traitHasPermissionTo('view-keuangan-hutang')) {
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

        if ($targetRoleName && !$this->traitHasPermissionTo('super-admin-bypass')) {
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
        if ($this->isWadir() || $this->traitHasPermissionTo('view-kepegawaian-karyawan')) {
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
     * Dapatkan daftar ruangan_id yang dikoordinasi user ini
     * Return null jika Super-Admin/Staff-SDM/Wadir (artinya akses semua ruangan)
     */
    public function getRuanganKoordinatorIds(): ?array
    {
        if ($this->isWadir() || $this->traitHasPermissionTo('view-kepegawaian-karyawan')) {
            return null; // null = akses semua ruangan
        }

        $idsFromPivot = $this->koordinatorRuangans()->pluck('ruangan_id')->toArray();

        // Auto-check ruangan dari sdm_kary_ruangan atau ruangan_id utama jika user memegang Jabatan Level 4
        $karyawan = $this->karyawan;
        if ($karyawan) {
            $hasKoorJabatan = $karyawan->jabatan()
                ->whereHas('tingkat', function ($q) {
                    $q->where('is_penyusun_jadwal', true)->orWhere('urutan', 4);
                })
                ->exists();

            if ($hasKoorJabatan) {
                $assignedRooms = $karyawan->ruangans()->pluck('ruangan.id')->toArray();
                if ($karyawan->ruangan_id) {
                    $assignedRooms[] = $karyawan->ruangan_id;
                }
                return array_unique(array_merge($idsFromPivot, $assignedRooms));
            }
        }

        return $idsFromPivot;
    }

    /**
     * Cek apakah user ini merupakan Dokter atau Manajemen Medis/SDM (Wadir/SDM/Super-Admin)
     */
    public function isDokterOrApprover(): bool
    {
        if ($this->isDokter() || $this->isWadir() || $this->traitHasPermissionTo('view-kepegawaian-jadwal-kerja')) {
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
        return \App\Models\Sdm\Dokter::where('karyawan_id', $this->karyawan_id)->exists();
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
