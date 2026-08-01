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

    use HasRoles;

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
        // Super-Admin dan Staff-SDM selalu lolos — tidak perlu cek tabel koordinator
        if ($this->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }
        return $this->koordinatorRuangans()->exists();
    }

    /**
     * Dapatkan daftar ruangan_id yang dikoordinasi user ini
     * Return null jika Super-Admin/Staff-SDM (artinya akses semua ruangan)
     */
    public function getRuanganKoordinatorIds(): ?array
    {
        if ($this->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return null; // null = akses semua ruangan
        }
        return $this->koordinatorRuangans()->pluck('ruangan_id')->toArray();
    }

    /**
     * Cek apakah user ini merupakan Dokter atau Pengawas (Wadir/SDM/Super-Admin)
     */
    public function isDokterOrApprover(): bool
    {
        if ($this->hasRole(['Super-Admin', 'Wakil-Direktur', 'Staff-SDM']) || $this->can('approve-jadwal-wadir')) {
            return true;
        }

        if ($this->hasRole(['Koordinator-Dokter', 'Dokter'])) {
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
}

