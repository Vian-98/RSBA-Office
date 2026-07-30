<?php

namespace App\Livewire\Master\Spesialisasi;

use Throwable;
use App\Models\Sdm\DokterSpesialisasi;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public string $nama;
    public ?string $singkatan = null;
    public string $kategori = 'umum';
    public $kategoriOptions = [
        'umum' => 'Umum',
        'spesialis' => 'Spesialis'
    ];

    public $rules = [
        'nama' => 'required|string'
    ];

    function submit()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'singkatan' => $this->singkatan,
            'kategori' => $this->kategori
        ];

        DB::beginTransaction();
        try {
            DokterSpesialisasi::create($data);
            DB::commit();

            $this->dispatch('new-spesialisasi-created');
            $this->toast()
                ->success('Berhasil', 'Data spesialis disimpan.')
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
        return view('livewire.master.spesialisasi.add');
    }
}
