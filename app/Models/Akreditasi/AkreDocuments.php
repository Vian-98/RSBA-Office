<?php

namespace App\Models\Akreditasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AkreDocuments extends Model
{
    protected $table = 'akre_documents';
    protected $guarded = ['id'];
    protected $with = ['uploader.karyawan'];

    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime'
    ];

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

    // Scope untuk document yang belum dihapus
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope untuk document yang sudah dihapus
    public function scopeDeleted($query)
    {
        return $query->where('is_deleted', true);
    }


    /**
     * Elements relationship menggunakan custom pivot
     */
    public function elements()
    {
        return $this->belongsToMany(
            AkreElement::class,
            'akre_element_documents',
            'document_id',
            'element_id'
        )
            ->using(AkreElementDocuments::class)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get original element (yang pertama upload)
     */
    public function originalElement()
    {
        return $this->belongsToMany(
            AkreElement::class,
            'akre_element_documents',
            'document_id',
            'element_id'
        )
            ->using(AkreElementDocuments::class)
            ->wherePivot('is_original', true)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted']);
    }

    public function originalElementWithSource()
    {
        return $this->belongsToMany(
            AkreElement::class,
            'akre_element_documents',
            'document_id',
            'element_id'
        )
            ->using(AkreElementDocuments::class)
            ->wherePivot('is_original', true)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted'])
            ->with(['bab.chapter']); // Eager load bab dan chapter
    }


    public function getOriginalElementDocumentAttribute()
    {
        return $this->originalElement()->first();
    }
}
