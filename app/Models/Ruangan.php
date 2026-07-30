<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $guarded = [];

    public function shiftValid()
    {
        return $this->hasMany(\App\Models\Sdm\RuanganShift::class, 'ruangan_id');
    }
}
