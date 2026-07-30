<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RajalExport implements WithMultipleSheets
{
    protected $periode;
    protected $cabar;
    protected $kelompok;
    protected $pelayanan = 'rajal';
    protected $batch;

    public function __construct($periode, $cabar, $kelompok, $batch)
    {
        $this->periode = $periode;
        $this->cabar = $cabar;
        $this->kelompok = $kelompok;
        $this->batch = $batch;
    }
    /**
     * @return Collection
     */
    public function sheets(): array
    {

        if ($this->kelompok) {
            return [
                'Sheet1' => new RajalProsentase(
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
                ),
            ];
        }
        // kelompok tidak dipilih
        else {
            $semua_kelompok = [
                'rj_sp',
                'rj_um',
                'rj_mata',
                'rj_hd'
            ];

            $sheets = [];

            foreach ($semua_kelompok as $kelompok) {
                // prosentase per kelompok
                $sheets[] = new RajalProsentase(
                    periode: $this->periode,
                    cabar: $this->cabar,
                    kelompok: $kelompok,
                    batch: $this->batch
                );
            }

            // sheets rekap dokter
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
