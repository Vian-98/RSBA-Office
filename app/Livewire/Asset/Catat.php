<?php

namespace App\Livewire\Asset;

use Throwable;
use App\Livewire\Forms\Asset\AssetBarangForm;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Assets\AssetBarang;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Catat extends Component
{
    use Interactions;

    public AssetBarangForm $form;

    public ?AssetBarang $assetBarang;

    public $statusOptions = [
        ['label' => 'Baik', 'value' => 'baik'],
        ['label' => 'Perbaikan', 'value' => 'diperbaiki'],
        ['label' => 'Rusak', 'value' => 'rusak'],
        ['label' => 'Hilang', 'value' => 'hilang'],
    ];

    public function mount($id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super-Admin') && !$user->hasRole('Staff-Umum') && !$user->hasRole('Admin-Umum') && !$user->can('manage-umum-asset') && !$user->can('manage-asset'))) {
            abort(403, 'Hanya Bagian Umum yang berhak melakukan pencatatan aset.');
        }

        $this->assetBarang = AssetBarang::findOrFail($id);
    }

    public function submit()
    {

        $this->form->validate(
            [
                'tgl_catat' => 'required',
                'status' => 'required'
            ]
        );

        try {
            $this->form->catatAsset($this->assetBarang);

            $this->dispatch('new-asset-created');

            $this->toast()
                ->success('Berhasil', 'Barang berhasil dilakukan pencatatan sebagai asset.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error("Tidak Berhasil", "<i>{$e->getMessage()}</i> <br> Silahkan coba lagi.")
                ->send();
        }
    }

    public function render()
    {

        // $mains = AssetBarang::all();
        return view('livewire.asset.catat');
    }
}
