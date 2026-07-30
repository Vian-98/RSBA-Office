<?php

namespace App\Models\Akreditasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkreChapter extends Model
{
    protected $table = "akre_chapter";
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'pic_id', 'id');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(AkreKegiatan::class, 'kegiatan_id', 'id');
    }

    public function babs(): HasMany
    {
        return $this->hasMany(AkreBabElement::class, 'chapter_id', 'id');
    }

    // Get all documents in this chapter
    // public function getAllDocuments()
    // {
    //     return AkreDocuments::whereHas('elements.bab', function ($query) {
    //         $query->where('chapter_id', $this->id)
    //             ->orWhereHas('parent', function ($q) {
    //                 $q->where('chapter_id', $this->id);
    //             });
    //     })->distinct()->get();
    // }

    public function getAllDocuments()
    {
        $documents = collect();

        foreach ($this->babs as $bab) {

            // Documents dari bab ini
            $bab->elements->each(function ($element) use (&$documents) {
                $documents = $documents->merge($element->documents);
            });

            // Documents from sub-babs (children)
            $bab->children->each(function ($sub) use (&$documents) {
                $sub->elements->each(
                    function ($element) use (&$documents) {
                        $documents = $documents->merge($element->documents);
                    }
                );
            });
        }

        return $documents->unique('id')->values();
    }
}
