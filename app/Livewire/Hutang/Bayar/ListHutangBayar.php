<?php

namespace App\Livewire\Hutang\Bayar;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ListHutangBayar extends Component
{

    public function mount($id) {}

    #[Computed]
    public function headers(): array
    {
        return [
            ['index' => 'tanggal', 'label' => 'Tanggal'],
            ['index' => 'oleh', 'label' => 'Oleh'],
            ['index' => 'jumlah', 'label' => 'Jumlah'],
        ];
    }

    #[Computed]
    public function rows(): array
    {
        return [];
    }

    public function render()
    {
        return view('livewire.hutang.bayar.list-hutang-bayar');
    }
}
