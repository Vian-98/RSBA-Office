<?php

namespace App\Models\Surat;

use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratDisposisiDetail extends Model
{
    use HasFactory;

    protected $table = 'surat_disposisi_detail';

    protected $fillable = [
        'surat_disposisi_id',
        'jabatan_id',
        'karyawan_id',
        'user_id',
        'nama_tujuan',
        'is_info',
        'is_action',
        'is_arsip',
        'status_tindak_lanjut',
        'catatan_penerima',
        'paraf',
        'tgl_paraf',
    ];

    protected $casts = [
        'is_info' => 'boolean',
        'is_action' => 'boolean',
        'is_arsip' => 'boolean',
        'tgl_paraf' => 'datetime',
    ];

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(SuratDisposisi::class, 'surat_disposisi_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
