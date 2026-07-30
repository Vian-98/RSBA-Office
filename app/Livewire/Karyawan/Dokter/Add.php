<?php

namespace App\Livewire\Karyawan\Dokter;

use Throwable;
use App\Models\Sdm\Dokter;
use App\Models\Sdm\DokterSpesialisasi;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy(isolate: false)]
class Add extends Component
{
    use Interactions;


    public int $karyawan;
    public int $subSpesialis;


    public $subSpesialisOpt;

    public $rules = [
        'karyawan' => 'required',
        'subSpesialis' => 'required'
    ];

    function mount()
    {
        $this->subSpesialisOpt = DokterSpesialisasi::all();
    }

    function submit()
    {
        $this->validate();

        $data = [
            'karyawan_id' => $this->karyawan,
            'spesialis_id' => $this->subSpesialis
        ];

        DB::beginTransaction();
        try {
            $dokter = Dokter::create($data);
            DB::commit();


            $this->dispatch('new-dokter-created');

            $this->toast()
                ->success('Berhasil', "Karyawan <b>{$dokter->karyawan->nama}</b> ditambah kedokter.")
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.karyawan.dokter.add');
    }
}
