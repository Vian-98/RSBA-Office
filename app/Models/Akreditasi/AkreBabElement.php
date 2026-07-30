<?php

namespace App\Models\Akreditasi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkreBabElement extends Model
{
    protected $table = 'akre_bab_elements';
    protected $guarded = ['id'];

    /**
     * Relasi ke Parent Bab (untuk Sub Bab)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AkreBabElement::class, 'parent_id');
    }

    /**
     * Relasi ke Children (Sub Bab)
     */
    public function children(): HasMany
    {
        return $this->hasMany(AkreBabElement::class, 'parent_id')->orderBy('no');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(AkreChapter::class, 'chapter_id', 'id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AkreElement::class, 'akre_bab_id', 'id');
    }

    /**
     * Scope untuk filter hanya Bab (header)
     */
    public function scopeOnlyBab($query)
    {
        return $query->where('bab', 'bab');
    }

    /**
     * Scope untuk filter hanya Sub Bab
     */
    public function scopeOnlySubBab($query)
    {
        return $query->where('bab', 'sub');
    }

    /**
     * Check apakah ini adalah Bab (header)
     */
    public function isBab(): bool
    {
        return $this->bab === 'bab';
    }

    /**
     * Check apakah ini adalah Sub Bab
     */
    public function isSubBab(): bool
    {
        return $this->bab === 'sub';
    }

    // In AkreBab Model
    public function getAllDocuments()
    {
        $documents = collect();

        // Documents from this bab's elements
        $documents = $documents->merge(
            $this->elements->pluck('documents')->flatten()
        );

        // Documents from children (sub-babs)
        $documents = $documents->merge(
            $this->children->pluck('elements')->flatten()->pluck('documents')->flatten()
        );

        return $documents->unique('id')->values();
    }
}
