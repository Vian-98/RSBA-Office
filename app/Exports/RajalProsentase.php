<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use App\Models\JmProsentase;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RajalProsentase implements FromCollection, WithHeadings, WithTitle
{
    private $periode;
    private $cabar;
    private $kelompok;
    private $batch;

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
    public function collection()
    {
        [$tahun, $bulan] = explode("-", $this->periode);

        return JmProsentase::select(
            'nama_pasien',
            'no_rekmedis',
            'sep',
            'kelompok',
            'jasa_pelayanan',
            'jasa_rs',
            'jasa_medis',
            'jasa_pekerja',
            'jasa_sppdkgh',
            'jasa_um_sertifikat'
        )
            ->leftJoin('jm_pasien', 'jm_prosentase.jm_pasien_id', '=', 'jm_pasien.id')
            ->whereYear('jm_pasien.tgl_checkout', $tahun)
            ->whereMonth('jm_pasien.tgl_checkout', $bulan)
            ->where('jm_pasien.layanan', 'rajal')
            ->where('jm_pasien.cabar', $this->cabar)
            ->where('jm_pasien.kelompok', $this->kelompok)
            ->where('jm_pasien.batch', $this->batch)
            ->get();
    }


    function headings(): array
    {
        return [
            'Nama Pasien',
            'No. Rekmedis',
            'SEP',
            'Kelompok',
            'Jasa Pelayanan',
            'Jasa RS',
            'Jasa Medis Dokter',
            'Jasa Pekerja',
            'Jasa SPPDKGH',
            'Jasa Umum Sertifikat'
        ];
    }

    function title(): string
    {
        $nama_kelompok = [
            'rj_sp' => 'Spesialis',
            'rj_um' => 'Umum',
            'rj_mata' => 'Mata',
            'rj_hd' => 'HD'
        ];
        return 'Prosentase ' . $nama_kelompok[$this->kelompok];
    }
}
