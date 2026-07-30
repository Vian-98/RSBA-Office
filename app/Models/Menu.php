<?php

namespace App\Models;

use App\Enums\MenuGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $table = 'menus';
    protected $guarded = [];

    protected $casts = [
        'permission'   => 'array',
        'route_params' => 'array',
        'group'        => MenuGroup::class
    ];

    function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'id');
    }

    function submenus()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }
}
