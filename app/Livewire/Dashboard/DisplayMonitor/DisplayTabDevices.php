<?php

namespace App\Livewire\Dashboard\DisplayMonitor;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class DisplayTabDevices extends Component
{
    // Cached Data
    public array $devices = [];
    public array $wards = [];
    public array $rooms = [];
    public array $inpatientRooms = [];
    public array $polyclinics = [];

    // Register Device form
    public string $displayId = '';
    public string $deviceName = '';
    public string $ipAddress = '';

    // Edit Device form
    public string $editDisplayId = '';
    public string $editDeviceName = '';
    public string $editIpAddress = '';

    // Mapping form
    public string $selectedDeviceId = '';
    public string $targetType = 'ward_class';
    public string $targetId = '';

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        return view('livewire.dashboard.display-monitor.display-tab-devices');
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        $this->devices        = $client->getDisplays();
        $this->wards          = $client->getWards();
        $this->rooms          = $client->getRooms();
        $this->inpatientRooms = $client->getInpatientRooms();
        $this->polyclinics    = $client->getPolyclinics();
    }

    public function registerDevice(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'displayId'  => 'required|string|max:50',
            'deviceName' => 'required|string|max:255',
            'ipAddress'  => 'nullable|string|max:45',
        ]);

        try {
            $client->registerDisplay(strtoupper($this->displayId), $this->deviceName, $this->ipAddress);
            $this->dispatch('notify-success', message: "Perangkat monitor {$this->displayId} berhasil terdaftar!");
            $this->reset(['displayId', 'deviceName', 'ipAddress']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function editDevice(string $displayId, string $currentName, ?string $currentIp = null): void
    {
        $this->editDisplayId = $displayId;
        $this->editDeviceName = $currentName;
        $this->editIpAddress = $currentIp ?? '';
    }

    public function cancelEdit(): void
    {
        $this->reset(['editDisplayId', 'editDeviceName', 'editIpAddress']);
    }

    public function updateDevice(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'editDeviceName' => 'required|string|max:255',
            'editIpAddress'  => 'nullable|string|max:45',
        ]);

        try {
            $client->updateDisplay($this->editDisplayId, $this->editDeviceName, $this->editIpAddress);
            $this->dispatch('notify-success', message: "Data perangkat {$this->editDisplayId} berhasil diubah!");
            $this->reset(['editDisplayId', 'editDeviceName', 'editIpAddress']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function updateMapping(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'selectedDeviceId' => 'required|string',
            'targetType'       => 'required|string|in:ward_class,operating_room,ward_summary,inpatient_room,polyclinic',
            'targetId'         => 'required|string',
        ]);

        try {
            $client->updateMapping($this->selectedDeviceId, $this->targetType, $this->targetId);
            $this->dispatch('notify-success', message: "Mapping monitor berhasil diperbarui!");
            $this->reset(['selectedDeviceId', 'targetId']);
            $this->loadData($client);
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
