<?php

namespace App\Models\Sdm;

use App\Models\Surat\CutiJenis;
use App\Models\Surat\SuratCuti;
use App\Models\User;
use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class CutiBersama extends Model
{
    use Blameable;

    protected $table = 'sdm_cuti_bersama';
    protected $guarded = [];

    protected $casts = [
        'potong_cuti_tahunan' => 'boolean',
        'diproses_at' => 'datetime',
    ];

    public function tanggal()
    {
        return $this->hasMany(CutiBersamaTanggal::class, 'cuti_bersama_id', 'id')->orderBy('tanggal', 'asc');
    }

    public function suratCuti()
    {
        return $this->hasMany(SuratCuti::class, 'cuti_bersama_id', 'id');
    }

<<<<<<< HEAD
    public function partisipasiKaryawan()
    {
        return $this->hasMany(CutiBersamaKaryawan::class, 'cuti_bersama_id', 'id');
    }

=======
>>>>>>> origin/kepegawaian/absensi
    public function jenisCuti()
    {
        return $this->belongsTo(CutiJenis::class, 'jenis_cuti_id', 'id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh', 'id');
    }

    public function createdOleh()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedOleh()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
