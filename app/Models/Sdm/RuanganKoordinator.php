<?php

namespace App\Models\Sdm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ruangan;

class RuanganKoordinator extends Model
{
    protected $table = 'sdm_ruangan_koordinator';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean'];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
