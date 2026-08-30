<?php

namespace App\Livewire\Forms;

use Throwable;
use App\Models\Surat\CutiJenis;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

class SuratCutiForm extends Form
{
    public ?array $tgl_cuti = [];
    public $jenis_cuti;
    public int $sisa_cuti = 0, $lama_cuti = 0;
    public ?array $atasan = [];
    public ?string $keterangan = null;
    public ?string $alamat = null;
    public $options_urgensi = [];

    // public $options_urgensi;
    public $options_atasan, $karyawan_options;


    public function initOptionsUrgensi()
    {
        $this->options_urgensi = CutiJenis::all()->map(fn($item) => [
            'label' => $item->nama,
            'value' => $item->id
        ])->toArray();
    }
    //s
    // validations
    public function rules()
    {
        return [
            'jenis_cuti' => 'required',
            'tgl_cuti' => 'required|array|min:1',
            'tgl_cuti.*' => 'date',
            'lama_cuti' => 'required|integer|min:1|max:' . $this->sisa_cuti,
            'atasan' => 'required|array|min:1',
        ];
    }

    public function validationAttributes()
    {
        return [
            'jenis_cuti' => 'Jenis Cuti',
            'tgl_cuti' => 'Tanggal Cuti',
            'lama_cuti' => 'Lama Cuti',
            'atasan' => 'Persetujuan Atasan',
        ];
    }

    public function messages()
    {
        return [
            'jenis_cuti.required' => 'Jenis cuti wajib dipilih.',
            'tgl_cuti.required'   => 'Tanggal cuti wajib dipilih minimal 1 hari.',
            'lama_cuti.required'  => 'Lama cuti wajib diisi.',
            'lama_cuti.max'       => 'Lama cuti tidak dapat lebih dari sisa cuti.',
            'atasan.required'     => 'Persetujuan Atasan wajib dipilih minimal 1 orang pejabat penyetuju.',
            'atasan.min'          => 'Persetujuan Atasan wajib dipilih minimal 1 orang pejabat penyetuju.',
        ];
    }

    // generate no surat 
    public static function generateNoSurat(): string
    {
        $tahun = date('Y');

        $records = SuratCuti::where('no_surat', 'like', "C%{$tahun}")
            ->pluck('no_surat');

        $maxNo = 0;
        foreach ($records as $noSurat) {
            if (preg_match('/^C(\d+)' . $tahun . '$/i', $noSurat, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNo) {
                    $maxNo = $num;
                }
            }
        }

        $nextNo = $maxNo + 1;

        do {
            $formattedNo = str_pad($nextNo, 4, '0', STR_PAD_LEFT);
            $candidate = "C{$formattedNo}{$tahun}";
            $exists = SuratCuti::where('no_surat', $candidate)->exists();
            if ($exists) {
                $nextNo++;
            }
        } while ($exists);

        return $candidate;
    }


    public function submiting($karyawan)
    {
        sort($this->tgl_cuti); // sort array $tgl_cuti
        $tgl_mulai = $this->tgl_cuti[0]; // First date
        $tgl_akhir = $this->tgl_cuti[count($this->tgl_cuti) - 1]; // Last date

        // populate data
        $dataSuratCuti = [
            'karyawan_id' => $karyawan?->id,
            'no_surat' => $this->generateNoSurat(),
            'tgl_surat' => date('Y-m-d'),
            'tgl_mulai' => $tgl_mulai,
            'tgl_akhir' => $tgl_akhir,
            'tgl_cuti' => json_encode($this->tgl_cuti),
            'lama_cuti' => $this->lama_cuti,
            'urgensi_id' => $this->jenis_cuti,
            'keterangan' => $this->keterangan,
            'alamat' => $this->alamat,
            'created_by' => auth()->user()->id
        ];

        DB::beginTransaction();
        try {
            $cuti = SuratCuti::create($dataSuratCuti); //create record 
            foreach ($this->atasan as $acc) {

                SuratCutiApproval::insert([
                    'surat_cuti_id' => $cuti->id,
                    'disetujui_oleh' => $acc,
                    'status' => 'waiting',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $karyawan->cuti += $this->lama_cuti; //update sisa cuti
            $karyawan->save();
            DB::commit();

            return [
                'success' => true,
                'message' => 'Inserted'
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage() . $e->getLine()
            ];
        }
    }
}
