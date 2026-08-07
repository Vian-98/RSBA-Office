<?php

namespace App\Livewire\Dashboard\DisplayMonitor;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class DisplayTabInpatientRooms extends Component
{
    public array $inpatientRooms = [];

    // Inpatient Room form
    public string $editingRoomId = '';
    public string $roomCode = '';
    public string $roomName = '';
    public string $roomFloor = '';
    public string $roomBuilding = '';
    public int $roomBedTotal = 0;
    public int $roomBedOccupied = 0;

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        return view('livewire.dashboard.display-monitor.display-tab-inpatient-rooms');
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        $this->inpatientRooms = $client->getInpatientRooms();
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
                $this->dispatch('notify-success', message: "Ruangan {$this->roomName} berhasil diperbarui!");
            } else {
                $client->createInpatientRoom($payload);
                $this->dispatch('notify-success', message: "Ruangan {$this->roomName} berhasil dibuat!");
            }
            $this->resetRoomForm();
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
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
            $this->dispatch('notify-success', message: "Ruangan berhasil dihapus!");
            $this->loadData($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function resetRoomForm(): void
    {
        $this->reset(['editingRoomId', 'roomCode', 'roomName', 'roomFloor', 'roomBuilding', 'roomBedTotal', 'roomBedOccupied']);
    }

    #[On('display-status-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
