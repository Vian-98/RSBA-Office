<?php

namespace App\Livewire\Karyawan;

use App\Enums\Agama;
use App\Enums\Kelamin;
use App\Enums\StatusKaryawan;
use App\Enums\KategoriKerja;
use App\Livewire\Forms\KaryawanForm;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Add extends Component
{
    use Interactions;

    public KaryawanForm $form;

    public $status_options;
    public $kategori_options;
    public $agama_options;
    public $isDomisiliKTP = false;
    public $jk_options;
    public $pernikahan_options = [
        ['value' => 'belum_menikah', 'label' => 'Belum Menikah'],
        ['value' => 'menikah', 'label' => 'Menikah'],
        ['value' => 'janda_duda', 'label' => 'Janda/Duda']
    ];

    public function mount()
    {
        $this->status_options = StatusKaryawan::options();
        $this->kategori_options = KategoriKerja::options();
        $this->agama_options = Agama::options();
        $this->jk_options = Kelamin::options();
    }

    public function placeholder()
    {
        return view('components.skeleton');
    }

    public function domisili()
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

    function submit()
    {
        $this->form->validate();

        $submitting = $this->form->store();

        if ($submitting['status'] === 'sukses') {
            $this->dispatch('new-karyawan-created');

            $this->toast()
                ->success('Berhasil', 'Simpan data karyawan berhasil.')
                ->send();
        } else {
            $this->toast()
                ->error('Tidak Berhasil', 'Error' . $submitting['message'])
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.karyawan.add');
    }
}
