<?php

namespace App\Models\Maintenance;

use App\Models\Assets\AssetBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Request extends Model
{
    protected $table = 'asset_maintc_requests';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'lampiran' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->nomor_tiket)) {
                $prefix = 'TKT-MNT-' . now()->format('Ymd') . '-';
                $lastRecord = static::where('nomor_tiket', 'like', $prefix . '%')
                                   ->orderBy('id', 'desc')
                                   ->first();
                $nextId = $lastRecord ? intval(substr($lastRecord->nomor_tiket, -3)) + 1 : 1;
                $model->nomor_tiket = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }

    public function getUserRequestAttribute(): ?string
    {
        if ($this->relationLoaded('user_req') && $this->user_req?->relationLoaded('karyawan')) {
            return $this->user_req?->karyawan?->nama ?? '-';
        }

        return optional(optional($this->user_req)?->karyawan)?->nama;
    }

    public function getUserVerifyAttribute(): ?string
    {

        if ($this->relationLoaded('user_verif') && $this->user_verif?->relationLoaded('karyawan')) {
            return $this->user_verif?->karyawan?->nama ?? '-';
        }

        return optional(optional($this->user_verif)?->karyawan)?->nama;
    }

    public function user_req()
    {
        return $this->belongsTo(User::class, 'user_req_id', 'id');
    }

    public function user_verif(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_verify_id', 'id');
    }

    // public function getUserRequestAttribute(): ?string
    // {
    //     return $this->user_req->karyawan->nama;
    // }

    // public function getUserVerifyAttribute(): ?string
    // {
    //     return $this->user_verif->karyawan->nama ?? null;
    // }

    public function jadwal(): HasOne
    {
        return $this->hasOne(Jadwal::class, 'maintc_request_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'request_id', 'id')
            ->orderBy('created_at', 'asc');
    }

    public function getTicketStatusAttribute(): string
    {
        if ($this->status === 'rejected') {
            return 'rejected';
        }

        $jadwal = $this->relationLoaded('jadwal') ? $this->jadwal : $this->jadwal()->with('work')->first();

        if (!$jadwal)           return 'open';
        if (!$jadwal->work)     return 'assigned';

        return match ($jadwal->work->status ?? '') {
            'in_progress' => 'in_progress',
            'done'        => 'resolved',
            default       => 'assigned',
        };
    }

    public function getTicketStatusLabelAttribute(): string
    {
        return match ($this->ticket_status) {
            'open'        => 'Open',
            'rejected'    => 'Rejected',
            'assigned'    => 'Assigned',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            default       => 'Unknown',
        };
    }

    public function getTicketStatusColorAttribute(): string
    {
        return match ($this->ticket_status) {
            'open'        => 'warning',
            'rejected'    => 'danger',
            'assigned'    => 'primary',
            'in_progress' => 'info',
            'resolved'    => 'success',
            default       => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'normal'  => 'gray',
            'penting' => 'warning',
            'darurat' => 'danger',
            default   => 'gray',
        };
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved'])
            ->whereDoesntHave('jadwal.work', function ($q) {
                $q->where('status', 'done');
            })
            ->orderBy('created_at', 'desc');
    }
}
