<?php

namespace App\Models\Maintenance;

use App\Models\Master\Barang;
use Illuminate\Database\Eloquent\Model;

class WorkParts extends Model
{
    protected $table = 'asset_maintc_work_parts';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function work()
    {
        return $this->belongsTo(
            Work::class,
            'maintc_work_id',
            'id'
        );
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}
