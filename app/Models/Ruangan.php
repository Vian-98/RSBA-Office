<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $guarded = [];

    public function shiftValid()
    {
        return $this->hasMany(\App\Models\Sdm\RuanganShift::class, 'ruangan_id');
    }
<<<<<<< HEAD

    public function karyawans()
    {
        return $this->belongsToMany(\App\Models\Sdm\Karyawan::class, 'sdm_kary_ruangan', 'ruangan_id', 'karyawan_id')
            ->using(\App\Models\Sdm\KaryawanRuangan::class)
            ->withPivot('id', 'tgl_mulai', 'tgl_berakhir', 'is_utama', 'keterangan');
    }

    public function karyawanPrimary()
    {
        return $this->hasMany(\App\Models\Sdm\Karyawan::class, 'ruangan_id')->whereNull('resign_at');
    }

    public function koordinatorAktif()
    {
        return $this->hasOne(\App\Models\Sdm\RuanganKoordinator::class, 'ruangan_id')->where('aktif', true);
    }

    public function getKoordinatorInfoAttribute(): array
    {
        // 1. Penugasan Langsung via sdm_ruangan_koordinator
        if ($this->relationLoaded('koordinatorAktif') && $this->koordinatorAktif) {
            $karyawan = $this->koordinatorAktif->karyawan;
            $user = $this->koordinatorAktif->user;
            $nama = $karyawan?->full_nama ?? $user?->name ?? 'Terhubung (Penugasan)';

            return [
                'nama' => $nama,
                'source' => 'direct',
                'label' => $nama,
                'is_assigned' => true,
            ];
        }

        // 2. Auto-Detect dari Karyawan yang bertugas di ruangan ini (sdm_karyawan.ruangan_id ATAU pivot sdm_kary_ruangan)
        $ruanganId = $this->id;
        $roomKaryawans = \App\Models\Sdm\Karyawan::whereNull('resign_at')
            ->where(function ($q) use ($ruanganId) {
                $q->where('ruangan_id', $ruanganId)
                  ->orWhereHas('ruangans', function ($rq) use ($ruanganId) {
                      $rq->where('ruangan.id', $ruanganId);
                  });
            })
            ->get();

        $koorKaryawan = $roomKaryawans->first(function ($karyawan) {
            return $karyawan->jabatan()->whereHas('tingkat', function ($q) {
                $q->where('is_penyusun_jadwal', true)->orWhere('urutan', 4);
            })->exists();
        });

        if ($koorKaryawan) {
            return [
                'nama' => $koorKaryawan->full_nama,
                'source' => 'jabatan',
                'label' => $koorKaryawan->full_nama,
                'is_assigned' => true,
            ];
        }

        // 3. Fallback: Belum Ada Yang Menjabat
        return [
            'nama' => 'Belum Ada Yang Menjabat',
            'source' => 'none',
            'label' => '⚠️ Belum Ada Yang Menjabat',
            'is_assigned' => false,
        ];
    }
=======
>>>>>>> origin/kepegawaian/absensi
}
