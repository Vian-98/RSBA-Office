<?php

namespace App\Livewire\Dashboard;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisplayMonitorAdmin extends Component
{
    // Tabs: dashboard | display-devices | content-data | audit-logs
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

    public function render(DmsMiddlewareClient $client)
    {
        $devices = $client->getDisplays();
        $wards = $client->getWards();
        $rooms = $client->getRooms();
        $auditLogs = $client->getAuditLogs();

        $totalMonitors = count($devices);
        $onlineMonitors = collect($devices)->where('status', 'online')->count();
        $offlineMonitors = collect($devices)->where('status', 'offline')->count();

        return view('livewire.dashboard.display-monitor-admin', [
            'devices' => $devices,
            'wards' => $wards,
            'rooms' => $rooms,
            'auditLogs' => $auditLogs,
            'totalMonitors' => $totalMonitors,
            'onlineMonitors' => $onlineMonitors,
            'offlineMonitors' => $offlineMonitors,
            'wardLastSync' => null, // Managed by middleware
            'orLastSync' => null,   // Managed by middleware
        ])->title('Display Monitor Admin Panel');
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function registerDevice(DmsMiddlewareClient $client)
    {
        $this->validate([
            'displayId' => 'required|string|max:50',
            'deviceName' => 'required|string|max:255',
        ]);

        try {
            $client->registerDisplay(strtoupper($this->displayId), $this->deviceName);
            $this->successMessage = "Perangkat monitor {$this->displayId} berhasil terdaftar!";
            $this->reset(['displayId', 'deviceName']);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function updateMapping(DmsMiddlewareClient $client)
    {
        $this->validate([
            'selectedDeviceId' => 'required|string',
            'targetType' => 'required|string|in:ward_class,operating_room',
            'targetId' => 'required|string',
        ]);

        try {
            $client->updateMapping($this->selectedDeviceId, $this->targetType, $this->targetId);
            $this->successMessage = "Mapping monitor berhasil diperbarui!";
            $this->reset(['selectedDeviceId', 'targetId']);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function syncWards(DmsMiddlewareClient $client)
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

    public function syncSchedules(DmsMiddlewareClient $client)
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
}
