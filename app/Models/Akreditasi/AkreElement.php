<?php

namespace App\Models\Akreditasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkreElement extends Model
{
    protected $table = 'akre_elements';
    protected $guarded = ['id'];

    protected $casts = [
        'methode' => 'array'
    ];


    public function bab(): BelongsTo
    {
        return $this->belongsTo(AkreBabElement::class, 'akre_bab_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AkreFiles::class, 'element_id', 'id');
    }

    public function validate_user()
    {
        return $this->belongsTo(User::class, 'validated_by', 'id');
    }

    public function getValidatorAttribute(): string
    {
        return $this->validate_user?->karyawan?->nama ?? 'n/a';
    }

    /**
     * Documents relationship menggunakan custom pivot
     */
    public function documents()
    {
        return $this->belongsToMany(
            AkreDocuments::class,
            'akre_element_documents',
            'element_id',
            'document_id'
        )
            ->using(AkreElementDocuments::class) // Gunakan custom pivot model
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get only original documents (uploaded by this element)
     */
    public function originalDocuments()
    {
        return $this->belongsToMany(
            AkreDocuments::class,
            'akre_element_documents',
            'element_id',
            'document_id'
        )
            ->using(AkreElementDocuments::class)
            ->wherePivot('is_original', true)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get only shared documents (attached from other elements)
     */
    public function sharedDocuments()
    {
        return $this->belongsToMany(
            AkreDocuments::class,
            'akre_element_documents',
            'element_id',
            'document_id'
        )
            ->using(AkreElementDocuments::class)
            ->wherePivot('is_original', false)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get documents where source has been deleted
     */
    public function deletedSourceDocuments()
    {
        return $this->belongsToMany(
            AkreDocuments::class,
            'akre_element_documents',
            'element_id',
            'document_id'
        )
            ->using(AkreElementDocuments::class)
            ->wherePivot('source_deleted', true)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get active documents only
     */
    public function activeDocuments()
    {
        return $this->belongsToMany(
            AkreDocuments::class,
            'akre_element_documents',
            'element_id',
            'document_id'
        )
            ->using(AkreElementDocuments::class)
            ->where('akre_documents.is_deleted', false)
            ->withPivot(['source_element_id', 'is_original', 'source_deleted', 'created_at', 'updated_at'])
            ->withTimestamps();
    }
}
