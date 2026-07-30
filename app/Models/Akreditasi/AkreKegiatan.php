<?php

namespace App\Models\Akreditasi;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Akreditasi\AkreChapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkreKegiatan extends Model
{
    protected $table = "akre_kegiatan";
    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid'; // Use UUID instead of ID in routes
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


    public function chapters(): HasMany
    {
        return $this->hasMany(AkreChapter::class, 'kegiatan_id', 'id');
    }
}
