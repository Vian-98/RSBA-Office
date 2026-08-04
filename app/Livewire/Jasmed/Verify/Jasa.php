<?php

namespace App\Livewire\Jasmed\Verify;

use App\Models\JmDokterJasa;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Jasa extends Component
{
    #[Reactive]
    public int $prosentaseId;

    public function getDokterJasa()
    {
        return JmDokterJasa::where('jm_prosentase_id', $this->prosentaseId)->get();
    }


    #[Computed]
    public function headers(): array
    {
        return [
            ['index' => 'dokter', 'label' => 'Nama Dokter'],
            ['index' => 'status_label', 'label' => 'Status'],
            ['index' => 'jumlah_visit', 'label' => 'Jumlah Visit'],
            ['index' => 'status_jasa', 'label' => 'Kelompok Jasa'],
            ['index' => 'jasa', 'label' => 'Jasa', 'format' => 'float']
        ];
    }

    #[Computed]
    public function rows(): array
    {
        return $this->getDokterJasa()->map(function ($item) {
            return [
                'dokter' => $item->dokter,
                'status_label' => $item->status_label,
                'jumlah_visit' => $item->jumlah,
                'status_jasa' => $item->status_jasa,
                'jasa' => $item->jasa
            ];
        })->toArray();
    }



    public function render()
    {
        return view('livewire.jasmed.verify.jasa');
    }
}
