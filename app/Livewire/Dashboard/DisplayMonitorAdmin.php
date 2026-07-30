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
    public string $ipAddress = '';

    // Mapping form
    public string $selectedDeviceId = '';
    public string $targetType = 'ward_class';
    public string $targetId = '';

    // Success/error alerts
    public string $successMessage = '';
    public string $errorMessage = '';

    // Cached API data — loaded once on mount(), refreshed only on demand
    public array $devices        = [];
    public array $wards          = [];
    public array $rooms          = [];
    public array $inpatientRooms = [];
    public array $polyclinics    = [];
    public array $auditLogs      = [];

    // Inpatient Room form
    public string $editingRoomId = '';
    public string $roomCode = '';
    public string $roomName = '';
    public string $roomFloor = '';
    public string $roomBuilding = '';
    public int $roomBedTotal = 0;
    public int $roomBedOccupied = 0;

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
        $this->devices        = $client->getDisplays();
        $this->wards          = $client->getWards();
        $this->rooms          = $client->getRooms();
        $this->inpatientRooms = $client->getInpatientRooms();
        $this->polyclinics    = $client->getPolyclinics();
        $this->auditLogs      = $client->getAuditLogs();
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
            'inpatientRooms'  => $this->inpatientRooms,
            'polyclinics'     => $this->polyclinics,
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
    public string $editIpAddress = '';

    public function registerDevice(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'displayId'  => 'required|string|max:50',
            'deviceName' => 'required|string|max:255',
            'ipAddress'  => 'nullable|string|max:45',
        ]);

        try {
            $client->registerDisplay(strtoupper($this->displayId), $this->deviceName, $this->ipAddress);
            $this->successMessage = "Perangkat monitor {$this->displayId} berhasil terdaftar!";
            $this->reset(['displayId', 'deviceName', 'ipAddress']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
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
            $this->successMessage = "Data perangkat {$this->editDisplayId} berhasil diubah!";
            $this->reset(['editDisplayId', 'editDeviceName', 'editIpAddress']);
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
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

    public function saveInpatientRoom(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'roomCode' => 'required|string|max:50',
            'roomName' => 'required|string|max:255',
            'roomFloor' => 'required|string|max:50',
            'roomBuilding' => 'required|string|max:255',
            'roomBedTotal' => 'required|integer|min:0',
            'roomBedOccupied' => 'required|integer|min:0',
        ]);

        $available = max(0, $this->roomBedTotal - $this->roomBedOccupied);

        $payload = [
            'room_code' => strtoupper($this->roomCode),
            'name' => $this->roomName,
            'floor' => $this->roomFloor,
            'building' => $this->roomBuilding,
            'bed_total' => $this->roomBedTotal,
            'bed_occupied' => $this->roomBedOccupied,
            'bed_available' => $available,
        ];

        try {
            if ($this->editingRoomId) {
                $client->updateInpatientRoom($this->editingRoomId, $payload);
                $this->successMessage = "Ruangan {$this->roomName} berhasil diperbarui!";
            } else {
                $client->createInpatientRoom($payload);
                $this->successMessage = "Ruangan {$this->roomName} berhasil dibuat!";
            }
            $this->resetRoomForm();
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function editInpatientRoom(string $id): void
    {
        $room = collect($this->inpatientRooms)->firstWhere('id', $id);
        if ($room) {
            $this->editingRoomId = $room['id'];
            $this->roomCode = $room['room_code'];
            $this->roomName = $room['name'];
            $this->roomFloor = $room['floor'];
            $this->roomBuilding = $room['building'];
            $this->roomBedTotal = $room['bed_total'];
            $this->roomBedOccupied = $room['bed_occupied'];
        }
    }

    public function deleteInpatientRoom(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deleteInpatientRoom($id);
            $this->successMessage = "Ruangan berhasil dihapus!";
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetRoomForm(): void
    {
        $this->reset(['editingRoomId', 'roomCode', 'roomName', 'roomFloor', 'roomBuilding', 'roomBedTotal', 'roomBedOccupied']);
    }
}
