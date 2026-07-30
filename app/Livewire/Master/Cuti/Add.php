<?php

namespace App\Livewire\Master\Cuti;

use Throwable;
use App\Models\Surat\CutiJenis;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Add extends Component
{
    use Interactions;

    public $nama, $lama;
    public $periode;

    public $rules = [
        'nama' => 'required|string|unique:surat_cuti_jenis,nama',
        'lama' => 'required|integer'
    ];

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $this->nama,
                'lama' => $this->lama,
                'periode' => $this->periode
            ];

            CutiJenis::create($data);

            DB::commit();

            $this->toast()
                ->success('Berhasil')
                ->send();

            $this->dispatch('jenis-cuti-created');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.cuti.add');
    }
}
