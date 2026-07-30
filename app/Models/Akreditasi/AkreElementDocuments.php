<?php

namespace App\Models\Akreditasi;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AkreElementDocuments extends Pivot
{
    protected $table = 'akre_element_documents';

    protected $fillable = [
        'element_id',
        'document_id',
        'source_element_id',
        'is_original',
        'source_deleted'
    ];

    protected $casts = [
        'is_original' => 'boolean',
        'source_deleted' => 'boolean',
    ];

    public $timestamps = true;

    /**
     * Get element
     */
    public function element()
    {
        return $this->belongsTo(AkreElement::class, 'element_id', 'id');
    }

    // Documents
    public function document()
    {
        return $this->belongsTo(AkreDocuments::class, 'document_id', 'id');
    }

    // Get Source Element (Sumber)
    public function sourceElement()
    {
        return $this->belongsTo(AkreElement::class, 'source_element_id', 'id')
            ->with(['bab.chapter']);;
    }

    // Scope: get original upload
    public function scopeOriginal($query)
    {
        return $query->where('is_original', true);
    }

    public function scopeShared($query)
    {
        return $query->where('is_original', false);
    }

    /**
     * Scope: Get documents where source has been deleted
     */
    public function scopeSourceDeleted($query)
    {
        return $query->where('source_deleted', true);
    }

    /**
     * Scope: Get active documents (source not deleted)
     */
    public function scopeSourceActive($query)
    {
        return $query->where('source_deleted', false);
    }

    /**
     * Mark source as deleted
     */
    public function markSourceAsDeleted()
    {
        $this->update(['source_deleted' => true]);
    }

    /**
     * Restore source status
     */
    public function restoreSource()
    {
        $this->update(['source_deleted' => false]);
    }


    /**
     * Get full source path (Chapter > Bab > Sub Bab > Element)
     */
    public function getSourceFullPathAttribute(): ?string
    {
        if (!$this->sourceElement?->bab?->chapter) {
            return null;
        }

        // $element = $this->sourceElement->load('bab.chapter');

        return sprintf(
            '%s > %s > %s',
            $this->sourceElement->bab->chapter->singkatan,
            $this->sourceElement->bab->nama,
            $this->sourceElement->nomor
        );
    }

    /**
     * Check if this is a shared document
     */
    public function isShared()
    {
        return !$this->is_original;
    }

    /**
     * Check if source is deleted
     */
    public function isSourceDeleted()
    {
        return $this->source_deleted;
    }
}
