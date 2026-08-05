<?php

namespace App\Models\Maintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    protected $table = 'maint_ticket_comments';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getNamaAttribute(): string
    {
        if (!$this->user_id) {
            return 'Sistem';
        }
        return $this->user?->karyawan?->nama ?? $this->user?->name ?? 'Unknown';
    }

    public function getIsLogAttribute(): bool
    {
        return $this->type === 'log';
    }
}
