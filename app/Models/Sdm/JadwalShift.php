<?php

namespace App\Models\Sdm;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JadwalShift extends Model
{
    use Blameable;

    protected $table = 'sdm_jadwal_shift';

    protected $guarded = [];

    protected $casts = [
        'aktif' => 'boolean',
        'lintas_hari' => 'boolean',
    ];

    /**
     * Bagian yang secara khusus menggunakan shift ini.
     * Jika tidak ada mapping, shift berlaku umum untuk semua Bagian.
     */
    public function bagians(): BelongsToMany
    {
        return $this->belongsToMany(
            Bagian::class,
            'sdm_bagian_shift',
            'shift_id',
            'bagian_id'
        )->withTimestamps();
    }
}

