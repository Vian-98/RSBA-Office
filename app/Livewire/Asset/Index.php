<?php

namespace App\Livewire\Asset;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Asset')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;

    protected function authorizeFromRoute(): void
    {
        $permission = $this->buildPermission();
        
        $user = auth()->user();
        if ($user?->can($permission)) {
            return;
        }
        
        if ($user?->karyawan?->ruangan_id) {
            return;
        }
        
        abort(403, "Tidak memiliki akses: {$permission}");
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.asset.index');
    }
}
