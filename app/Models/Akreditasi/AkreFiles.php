<?php

namespace App\Models\Akreditasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkreFiles extends Model
{
    protected $table = 'akre_files';
    protected $guarded = ['id'];
    protected $with = ['uploader.karyawan'];

    public function element(): BelongsTo
    {
        return $this->belongsTo(AkreElement::class, 'element_id', 'id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    public function getUserUploadAttribute(): string
    {
        if ($this->relationLoaded('uploader') && $this->uploader?->relationLoaded('karyawan')) {
            return $this->uploader?->karyawan?->nama ?? '-';
        }
        return optional(optional($this->uploader)?->karyawan)?->nama ?? "-";
    }
}
