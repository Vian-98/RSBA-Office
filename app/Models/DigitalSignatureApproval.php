<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignatureApproval extends Model
{
    protected $table = 'digital_signature_approvals';
    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'datetime',
        'step_order' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DigitalSignatureDocument::class, 'digital_signature_document_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
