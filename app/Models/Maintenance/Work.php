<?php

namespace App\Models\Maintenance;

use App\Models\Assets\AssetBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $table = 'asset_maintc_work';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'dokumentasi' => 'array',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'maintc_jadwal_id', 'id');
    }

    public function mulai_oleh()
    {
        return $this->belongsTo(User::class, 'mulai_by', 'id');
    }

    public function getUserMulaiAttribute()
    {
        return $this->mulai_oleh ? $this->mulai_oleh->karyawan->nama : '-';
    }

    public function selesai_oleh()
    {
        return $this->belongsTo(User::class, 'selesai_by', 'id');
    }


    public function getUserSelesaiAttribute()
    {
        return $this->selesai_oleh ? $this->selesai_oleh->karyawan->nama : '-';
    }

    public function teknisi()
    {
        return $this->belongsToMany(User::class, 'asset_maintc_teknisi_assigment', 'maintc_jadwal_id', 'teknisi_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function asset()
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }


    // Work Parts
    public function parts()
    {
        return $this->hasMany(
            WorkParts::class,
            'maintc_work_id',
            'id'
        );
    }
}
