<?php

namespace App\Livewire\Maintenance;

use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Maintenance')]
#[Isolate]
class Index extends Component
{
    use AuthorizesFromRoute;

    #[Computed]
    public function getHasNewRequestProperty()
    {
        return MaintenanceRequest::withoutGlobalScopes()
            ->where('status', 'pending')
            ->exists();
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.maintenance.index');
    }
}
