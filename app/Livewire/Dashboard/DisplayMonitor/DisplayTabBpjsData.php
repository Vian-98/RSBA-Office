<?php

namespace App\Livewire\Dashboard\DisplayMonitor;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class DisplayTabBpjsData extends Component
{
    public array $wards = [];
    public array $rooms = [];

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        return view('livewire.dashboard.display-monitor.display-tab-bpjs-data');
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        $this->wards = $client->getWards();
        $this->rooms = $client->getRooms();
    }

    #[On('display-status-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
