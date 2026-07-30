<?php

namespace App\Livewire\Gudang;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Gudang')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;



    #[On('submit-approval-beli-request')]
    public function refreshBadge()
    {
        // re-render the component to update the badge count
    }

    public function render()
    {
        $this->authorizeFromRoute();
        
        $permintaanCount = \App\Models\Gudang\PembelianRequest::where('status', 'pending')->count();
        
        return view('livewire.gudang.index', compact('permintaanCount'));
    }
}
