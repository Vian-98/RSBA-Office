<?php

namespace App\Models\Surat;

use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratDisposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_disposisi';

    protected $fillable = [
        'no_agenda',
        'surat_masuk_id',
        'tgl_surat',
        'no_surat',
        'perihal',
        'asal_surat',
        'catatan',
        'diterima_oleh',
        'tgl_diterima',
        'jam_diterima',
        'direktur_id',
        'signature_hash',
        'signature_cert_id',
        'signed_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
        'tgl_diterima' => 'date',
        'signed_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(SuratDisposisiDetail::class, 'surat_disposisi_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function direktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direktur_id');
    }
}
