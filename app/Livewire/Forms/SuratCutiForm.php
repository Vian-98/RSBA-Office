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

    public function messages()
    {
        return [
            'lama_cuti.max' => 'Lama cuti tidak dapat lebih dari sisa cuti.',
        ];
    }

    // generate no surat 
    public static function generateNoSurat(): string
    {
        $tahun = date('Y');
        $last = SuratCuti::select('id', 'no_surat')
            ->whereYear('tgl_surat', $tahun)
            ->orderBy('id', 'desc')
            ->first();

        $no = 1;
        if ($last) {
            $no = (int)substr($last->no_surat, 1, 4) + 1;
        }
        // buat nomor jadi 3 digit
        $no = str_pad($no, 4, '0', STR_PAD_LEFT);

        return "C{$no}{$tahun}";
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
