<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratTarifPkl extends Model
{
    protected $table = 'surat_tarif_pkl';
    protected $guarded = [];

    protected $casts = [
        'biaya_praktik_per_bulan'   => 'double',
        'biaya_orientasi_per_orang' => 'double',
        'tgl_berlaku'               => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Ambil tarif aktif saat ini
     */
    public static function getAktif(): self
    {
        $tarif = static::where('tgl_berlaku', '<=', now()->toDateString())
            ->orderBy('tgl_berlaku', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$tarif) {
            return new static([
                'biaya_praktik_per_bulan'   => 150000,
                'biaya_orientasi_per_orang' => 50000,
                'nomor_sk'                  => '023/Kpts-S4/PBA-A10/10.01.22',
                'tgl_berlaku'               => now()->toDateString(),
            ]);
        }

        return $tarif;
    }
}
