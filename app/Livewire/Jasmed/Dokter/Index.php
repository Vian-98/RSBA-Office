<?php

namespace App\Livewire\Jasmed\Dokter;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Index extends Component
{
    public string $tab = '';

    public $pasien = '';

    public $searchOptions = [
        ['value' => 'no_rekmedis', 'label' => 'No Rekmedis'],
        ['value' => 'nama_pasien', 'label' => 'Nama Pasien'],
        ['value' => 'sep', 'label' => 'SEP'],
    ];

    public $cabarOptions = [
        ['value' => 'bpjs', 'label' => 'BPJS'],
        ['value' => 'jkmd', 'label' => 'JKMD'],
        ['value' => 'tunai', 'label' => 'Tunai']
    ];


    public $tgl_checkout;
    public string $cabar = 'bpjs';
    public string $search_option = 'no_rekmedis';
    public string $cari = '';
    public bool $is_no_klaim = false;
    public bool $has_no_dokter = false;


    public function mount(String $cabar): void
    {
        $this->cabar = $cabar;
    }

    public function rules(): array
    {
        return [
            'tgl_checkout' => 'required',
            'search_option' => 'required',
        ];
    }

    public function cariVisite(): void
    {
        $this->validate();
    }


    public function cariAnastesi(): void
    {
        $this->validate();
    }

    public function render()
    {
        return view('livewire.jasmed.dokter.index');
    }
}
