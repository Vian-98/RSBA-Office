<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PayrollEditLog extends Model
{
    protected $table = 'sdm_payroll_edit_logs';
    protected $guarded = [];

    protected $casts = [
        'perubahan' => 'array',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
