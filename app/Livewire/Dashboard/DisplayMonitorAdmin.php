<?php

namespace App\Livewire\Dashboard;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisplayMonitorAdmin extends Component
{
    // Tabs: dashboard | devices | data | logs
    public string $activeTab = 'dashboard';

    // Device form
    public string $displayId = '';
    public string $deviceName = '';

    // Mapping form
    public string $selectedDeviceId = '';
    public string $targetType = 'ward_class';
    public string $targetId = '';

    // Success/error alerts
    public string $successMessage = '';
    public string $errorMessage = '';

    // Cached API data — loaded once on mount(), refreshed only on demand
    public array $devices   = [];
    public array $wards     = [];
    public array $rooms     = [];
    public array $auditLogs = [];

    /**
     * Load all data from Middleware once when the component first mounts.
     */
    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    /**
     * Fetch fresh data from Middleware and update cached properties.
     * Called by mount() on first render and by refreshData() on demand.
     */
    protected function loadData(DmsMiddlewareClient $client): void
    {
        $this->devices   = $client->getDisplays();
        $this->wards     = $client->getWards();
        $this->rooms     = $client->getRooms();
        $this->auditLogs = $client->getAuditLogs();
    }

    /**
     * render() is now instant — just passes cached properties to the view.
     * No API calls happen here.
     */
    public function render()
    {
        $totalMonitors   = count($this->devices);
        $onlineMonitors  = collect($this->devices)->where('status', 'online')->count();
        $offlineMonitors = collect($this->devices)->where('status', 'offline')->count();

        return view('livewire.dashboard.display-monitor-admin', [
            'devices'         => $this->devices,
            'wards'           => $this->wards,
            'rooms'           => $this->rooms,
            'auditLogs'       => $this->auditLogs,
            'totalMonitors'   => $totalMonitors,
            'onlineMonitors'  => $onlineMonitors,
            'offlineMonitors' => $offlineMonitors,
            'wardLastSync'    => null,
            'orLastSync'      => null,
        ])->title('Display Monitor Admin Panel');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage   = '';
    }

    // Edit form
    public string $editDisplayId = '';
    public string $editDeviceName = '';

    public function registerDevice(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'displayId'  => 'required|string|max:50',
            'deviceName' => 'required|string|max:255',
        ]);

        try {
            $client->registerDisplay(strtoupper($this->displayId), $this->deviceName);
            $this->successMessage = "Perangkat monitor {$this->displayId} berhasil terdaftar!";
            $this->reset(['displayId', 'deviceName']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function editDevice(string $displayId, string $currentName): void
    {
        $this->editDisplayId = $displayId;
        $this->editDeviceName = $currentName;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editDisplayId', 'editDeviceName']);
    }

    public function updateDevice(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'editDeviceName' => 'required|string|max:255',
        ]);

        try {
            $client->updateDisplay($this->editDisplayId, $this->editDeviceName);
            $this->successMessage = "Nama perangkat {$this->editDisplayId} berhasil diubah!";
            $this->reset(['editDisplayId', 'editDeviceName']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function updateMapping(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'selectedDeviceId' => 'required|string',
            'targetType'       => 'required|string|in:ward_class,operating_room',
            'targetId'         => 'required|string',
        ]);

        try {
            $client->updateMapping($this->selectedDeviceId, $this->targetType, $this->targetId);
            $this->successMessage = "Mapping monitor berhasil diperbarui!";
            $this->reset(['selectedDeviceId', 'targetId']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function syncWards(DmsMiddlewareClient $client): void
    {
        try {
            if ($client->syncWards()) {
                $this->successMessage = 'Sinkronisasi ketersediaan kamar BPJS berhasil dipicu pada middleware.';
            } else {
                $this->errorMessage = 'Gagal memicu sinkronisasi kamar.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function syncSchedules(DmsMiddlewareClient $client): void
    {
        try {
            if ($client->syncSchedules()) {
                $this->successMessage = 'Sinkronisasi jadwal operasi BPJS berhasil dipicu pada middleware.';
            } else {
                $this->errorMessage = 'Gagal memicu sinkronisasi jadwal operasi.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Refresh data from Middleware on demand. Called by:
     * 1. JavaScript setInterval via window.livewire.dispatch('display-status-changed')
     * 2. Future WebSocket integration
     */
    #[On('display-status-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
