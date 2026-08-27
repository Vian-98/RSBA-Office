<?php

namespace App\Models\Surat;

use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratSp3Log extends Model
{
    protected $table = 'surat_sp3_logs';
    protected $guarded = [];

    protected $casts = [
        'perubahan' => 'array',
    ];


    public function suratSp3()
    {
        return $this->belongsTo(SuratSp3::class, 'surat_sp3_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
}
