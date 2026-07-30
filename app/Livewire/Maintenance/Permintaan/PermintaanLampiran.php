<?php

namespace App\Livewire\Maintenance\Permintaan;

use Livewire\Attributes\Lazy;
use Livewire\Component;

class PermintaanLampiran extends Component
{
    public ?array $lampirans;

    public function mount($lampiranRequest)
    {
        $this->lampirans = $lampiranRequest;
    }

    public function render()
    {
        return view('livewire.maintenance.permintaan.permintaan-lampiran');
    }
}
