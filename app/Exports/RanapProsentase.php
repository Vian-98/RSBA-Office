<?php

namespace App\Exports;

use App\Models\JmJasa;
use App\Models\JmProsentase;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RanapProsentase implements FromCollection, WithHeadings, WithTitle
{
    private $periode;
    private $cabar;
    private $kelompok;
    private $batch;

    public function __construct($periode, $cabar, $kelompok, $batch)
    {
        $this->periode = $periode;
        $this->cabar = $cabar;
        $this->kelompok  = $kelompok;
        $this->batch = $batch;
    }

    public function collection()
    {
        [$tahun, $bulan] = explode('-', $this->periode);

        // $pasien =  JmProsentase::select(
        //     'nama_pasien',
        //     'no_rekmedis',
        //     'sep',
        //     'kelompok',
        //     'total_billing',
        //     'chosaring',
        //     'jasa_p',
        //     'klaim_min_rincian',
        //     'jasa_pelayanan',
        //     'jasa_rs',
        //     'jasa_medis',
        //     'jasa_operator',
        //     'jasa_anastesi',
        //     'jasa_penata',
        //     'jasa_resus',
        //     'jasa_pekerja',
        //     'jasa_sppdkgh',
        //     'jasa_um_sertifikat'
        // )
        //     ->leftJoin('jm_pasien', 'jm_prosentase.jm_pasien_id', '=', 'jm_pasien.id')
        //     ->whereYear('jm_pasien.tgl_checkout', $tahun)
        //     ->whereMonth('jm_pasien.tgl_checkout', $bulan)
        //     ->where('jm_pasien.layanan', 'ranap')
        //     ->where('jm_pasien.cabar', 'bpjs')
        //     ->where('jm_pasien.kelompok', $this->kelompok)
        //     ->where('jm_pasien.batch', $this->batch)
        //     ->get();

        $prosentases =  JmProsentase::select(
            'jm_prosentase.id',
            'nama_pasien',
            'no_rekmedis',
            'sep',
            'kelompok',
            'total_billing',
            'chosaring',
            'jasa_p',
            'klaim_min_rincian',
            'jasa_pelayanan',
            'jasa_rs',
            'jasa_medis',
            'jasa_operator',
            'jasa_anastesi',
            'jasa_penata',
            'jasa_resus',
            'jasa_pekerja',
            'jasa_sppdkgh',
            'jasa_um_sertifikat',
            'jasa_dpjp_hd'
        )
            ->leftJoin('jm_pasien', 'jm_prosentase.jm_pasien_id', '=', 'jm_pasien.id')
            ->whereYear('jm_pasien.tgl_checkout', $tahun)
            ->whereMonth('jm_pasien.tgl_checkout', $bulan)
            ->where('jm_pasien.layanan', 'ranap')
            ->where('jm_pasien.cabar', $this->cabar)
            ->where('jm_pasien.kelompok', $this->kelompok)
            ->where('jm_pasien.batch', $this->batch)
            ->get();


        // Inisialisasi sebuah koleksi kosong yang dinamai $dokter
        $dokter = collect();

        foreach ($prosentases as $prosentase) {
            // Get JmJasa by jm_prosentase_id
            // select dokter dan jasa, menggabungkannya, dan alias sebagai dokter_jasa
            // Mengambil dokter_jasa dan menggabungkannya dengan ' ; ' untuk mendapatkan string tergabung
            $dokter[$prosentase->id] = JmJasa::where('jm_prosentase_id', $prosentase->id)
                ->selectRaw('concat(dokter, " (", ROUND(jasa), ")") as dokter_jasa')
                ->pluck('dokter_jasa')
                ->implode(' ; ');
        }

        // Memetakan setiap item dalam koleksi $prosentases
        $mergedData = $prosentases->map(function ($item) use ($dokter) {
            // Mengambil data dokter tergabung untuk prosentase_id saat ini dari koleksi $dokter
            $dokterConcatenated = $dokter[$item->id] ?? '';

            // Menambahkan data dokter yang diambil ke dalam item
            $item->dokter = $dokterConcatenated;

            // Mengembalikan item yang telah dimodifikasi
            return $item;
        });

        return $mergedData;
        // dd($pasien->prosentase);
    }

    public function headings(): array
    {
        return [
            'Id',
            'Nama Pasien',
            'No. Rekmedis',
            'SEP',
            'Kelompok',
            'Total Billing',
            'Chosaring',
            'Jasa (38%)',
            'Klaim Min Rincian',
            'Jasa Pelayanan',
            'Jasa RS',
            'Jasa Medis',
            'Jasa Operator',
            'Jasa Anastesi',
            'Jasa Penata',
            'Jasa Resus',
            'Jasa Pekerja',
            'Jasa Sppdkgh',
            'Jasa Umum Sertifikat',
            'jasa DPJP HD',
            'Dokter'
        ];
    }

    function title(): string
    {

        $nama_kelompok = [
            'ri_no' => 'Non Operatif',
            'ri_op' => 'Operatif',
            'ri_mata' => 'Operatif Mata',
            'ri_partus' => 'Partus',
            'ri_sc' => 'SC',
            'ri_curet' => 'Curet',
            'ri_hd' => 'HD'
        ];


        return 'Prosentase ' . $nama_kelompok[$this->kelompok];
    }
}
