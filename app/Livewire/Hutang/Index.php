<?php

namespace App\Livewire\Hutang;

use App\Traits\AuthorizesFromRoute;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;

#[Title('Hutang')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;

    public $periode, $due_date;
    public $jenis, $vendor;

    public $optionsFaktur = [
        ['label' => 'Alat Kesehatan', 'value' => 'alkes'],
        ['label' => 'BHP', 'value' => 'bhp'],
        ['label' => 'Umum', 'value' => 'umum'],
    ];

    public function mount()
    {
        $this->periode = date('Y-m-d');
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.hutang.index');
    }
}
