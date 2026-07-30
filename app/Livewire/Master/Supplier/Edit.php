<?php

namespace App\Livewire\Master\Supplier;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Master\Supplier;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public string $nama = '', $telp = '', $email = '', $npwp = '', $bank = '', $norek = '', $an = '', $alamat = '';

    public function rules(): array
    {
        return [
            'nama' => ['required', 'unique:um_supplier,nama,' . $this->supplier->id],
            'telp' => 'required',
            'alamat' => 'required'
        ];
    }

    #[Locked]
    public ?Supplier $supplier;

    function mount($id)
    {
        $this->supplier = Supplier::findOrFail($id);
        $this->loadData();
    }

    function loadData()
    {
        $this->nama = $this->supplier->nama ?? '';
        $this->telp = $this->supplier->telp ?? '';
        $this->email = $this->supplier->email ?? '';
        $this->npwp = $this->supplier->npwp ?? '';
        $this->bank = $this->supplier->bank ?? '';
        $this->norek = $this->supplier->norek ?? '';
        $this->an = $this->supplier->an ?? '';
        $this->alamat = $this->supplier->alamat ?? '';
    }

    function submit()
    {

        $this->validate();

        DB::beginTransaction();
        try {
            $this->supplier->update([
                'nama' => $this->nama,
                'telp' => $this->telp,
                'email' => $this->email,
                'npwp' => $this->npwp,
                'bank' => $this->bank,
                'norek' => $this->norek,
                'an' => $this->an,
                'alamat' => $this->alamat,
            ]);
            DB::commit();

            $this->dispatch('new-supplier-updated');
            $this->dispatch('close-modal', id: 'modal-edit-supplier');

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
        return view('livewire.master.supplier.edit');
    }
}
