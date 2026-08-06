<?php

namespace App\Livewire\Dashboard\DisplayMonitor;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class DisplayTabDashboard extends Component
{
    public array $devices = [];

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        $totalMonitors   = count($this->devices);
        $onlineMonitors  = collect($this->devices)->where('status', 'online')->count();
        $offlineMonitors = collect($this->devices)->where('status', 'offline')->count();

        return view('livewire.dashboard.display-monitor.display-tab-dashboard', [
            'totalMonitors'   => $totalMonitors,
            'onlineMonitors'  => $onlineMonitors,
            'offlineMonitors' => $offlineMonitors,
        ]);
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        $this->devices = $client->getDisplays();
    }

    public function syncWards(DmsMiddlewareClient $client): void
    {
        try {
            if ($client->syncWards()) {
                $this->dispatch('notify-success', message: 'Sinkronisasi ketersediaan kamar BPJS berhasil dipicu pada middleware.');
            } else {
                $this->dispatch('notify-error', message: 'Gagal memicu sinkronisasi kamar.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function syncSchedules(DmsMiddlewareClient $client): void
    {
        try {
            if ($client->syncSchedules()) {
                $this->dispatch('notify-success', message: 'Sinkronisasi jadwal operasi BPJS berhasil dipicu pada middleware.');
            } else {
                $this->dispatch('notify-error', message: 'Gagal memicu sinkronisasi jadwal operasi.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    #[On('display-status-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
