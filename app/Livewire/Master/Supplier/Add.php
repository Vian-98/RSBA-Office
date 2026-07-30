<?php

namespace App\Livewire\Master\Supplier;

use Throwable;
use App\Models\Master\Supplier;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public string $nama, $telp, $email = '', $npwp = '', $bank = '', $norek = '', $an = '', $alamat;

    public $rules = [
        'nama' => 'required|unique:um_supplier,nama',
        'telp' => 'required',
        'alamat' => 'required'
    ];

    public function mount($nama = '')
    {
        $this->nama = $nama ?? '';
    }


    public function submit(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $this->nama,
                'telp' => $this->telp,
                'email' => $this->email,
                'npwp' => $this->npwp,
                'bank' => $this->bank,
                'norek' => $this->norek,
                'an' => $this->an,
                'alamat' => $this->alamat,
            ];

            Supplier::create($data);
            DB::commit();

            $this->dispatch('new-supplier-created');
            $this->dispatch('close-modal', id: 'modal-new-supplier');

            $this->toast()
                ->success('Berhasil', 'Supplier berhasil disimpan.')
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.supplier.add');
    }
}
