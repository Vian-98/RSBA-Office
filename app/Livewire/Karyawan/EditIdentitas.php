<?php

namespace App\Livewire\Karyawan;

use Throwable;
use App\Enums\Agama;
use App\Enums\Kelamin;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use Livewire\Attributes\Lazy;
use App\Livewire\Forms\KaryawanForm;
use TallStackUi\Traits\Interactions;

#[Lazy]
class EditIdentitas extends Component
{
    use Interactions;

    public KaryawanForm $form;

    public $status_options;
    public $agama_options;
    public $jk_options;
    public $pernikahan_options = [
        ['value' => 'belum', 'label' => 'Belum Menikah'],
        ['value' => 'menikah', 'label' => 'Menikah'],
        ['value' => 'single', 'label' => 'Janda/Duda']
    ];

    public $isDomisiliKTP = false;

    public function mount($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $this->form->mount($karyawan);

        $this->form->setIdentitas($karyawan);

        $this->status_options = StatusKaryawan::options();
        $this->agama_options = Agama::options();
        $this->jk_options = Kelamin::options();
    }

    public function update()
    {
        $this->validate();

        try {
            $this->form->updateIdentitas();

            // Clear cache for updated employee's user, and current logged-in user
            $karyawan = $this->form->karyawan;
            if ($karyawan && $karyawan->user) {
                \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $karyawan->user->id);
            }
            \Illuminate\Support\Facades\Cache::forget("navbar-user:" . auth()->id());

            $this->dispatch('updated-karywan');

            $this->toast()
                ->success('Updated', 'Update identitas karyawan berhasil.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    function domisili()
    {
        $this->isDomisiliKTP = ! $this->isDomisiliKTP;
        if ($this->isDomisiliKTP) {
            $this->form->dom_prov = $this->form->prov;
            $this->form->dom_kab = $this->form->kab;
            $this->form->dom_kec = $this->form->kec;
            $this->form->dom_desa = $this->form->desa;
            $this->form->dom_alamat = $this->form->alamat;
        } else {
            $this->reset('form.dom_prov');
            $this->reset('form.dom_kab');
            $this->reset('form.dom_kec');
            $this->reset('form.dom_desa');
            $this->reset('form.dom_alamat');
        }
    }

    public function render()
    {
        $canEditTglMasuk = auth()->user()->hasRole('Staff-SDM') || auth()->user()->hasRole('Super-Admin');
        return view('livewire.karyawan.edit-identitas', compact('canEditTglMasuk'));
    }
}
