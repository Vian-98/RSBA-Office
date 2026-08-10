<?php

namespace App\Models\Gaji;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sdm\Karyawan;

class PayrollSendLog extends Model
{
    use HasFactory;

    protected $table = 'sdm_payroll_send_logs';

    protected $fillable = [
        'periode',
        'karyawan_id',
        'email',
        'status',
        'tipe_pengiriman',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
