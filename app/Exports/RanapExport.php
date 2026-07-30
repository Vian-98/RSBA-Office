<?php

namespace App\Exports;

use App\Exports\RanapProsentase;
use App\Exports\RekapJasaDokter;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RanapExport implements WithMultipleSheets
{
    protected $periode;
    protected $cabar;
    protected $kelompok;
    protected $pelayanan = 'ranap';
    protected $batch;

    public function __construct($periode, $cabar, $kelompok, $batch)
    {
        $this->periode = $periode;
        $this->cabar = $cabar;
        $this->kelompok = $kelompok;
        $this->batch = $batch;
    }

    public function sheets(): array
    {
        // selected kelompok nya apa 
        if ($this->kelompok) {
            return [
                'Sheet1' => new RanapProsentase(
                    periode: $this->periode,
                    cabar: $this->cabar,
                    kelompok: $this->kelompok,
                    batch: $this->batch
                ),
                'Sheet2' => new RekapJasaDokter(
                    periode: $this->periode,
                    cabar: $this->cabar,
                    kelompok: $this->kelompok,
                    pelayanan: $this->pelayanan,
                    batch: $this->batch
                )
            ];
        }
        // tidak diselected kelompoknya
        else {
            $semua_kelompok = [
                'ri_no',
                'ri_op',
                'ri_mata',
                'ri_partus',
                'ri_sc',
                'ri_curet',
                'ri_hd'
            ];

            $sheets = [];

            foreach ($semua_kelompok as $kelompok) {
                $sheets[] = new RanapProsentase(
                    periode: $this->periode,
                    cabar: $this->cabar,
                    kelompok: $kelompok,
                    batch: $this->batch
                );
            }

            $sheets[] = new RekapJasaDokter(
                periode: $this->periode,
                cabar: $this->cabar,
                kelompok: $this->kelompok,
                pelayanan: $this->pelayanan,
                batch: $this->batch
            );

            return $sheets;
        }
    }
}
