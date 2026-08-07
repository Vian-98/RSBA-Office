<?php

namespace App\Livewire\Dashboard\Poli;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class PoliTabAntrian extends Component
{
    // Form & Cached Data
    public array $polyclinics = [];
    public array $queueItems = [];
    public array $queueAvailableDoctors = [];

    public string $queuePoliId = '';
    public string $queueDoctorId = '';
    public string $patientName = '';

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->polyclinics = $client->getPolyclinics();
    }

    public function render()
    {
        return view('livewire.dashboard.poli.poli-tab-antrian');
    }

    public function updatedQueuePoliId(DmsMiddlewareClient $client): void
    {
        $this->queueDoctorId = '';
        $this->loadQueue($client);
    }

    public function loadQueue(DmsMiddlewareClient $client): void
    {
        if (!$this->queuePoliId) {
            $this->queueItems = [];
            $this->queueAvailableDoctors = [];
            return;
        }

        $this->queueAvailableDoctors = $client->getPolyclinicDoctors($this->queuePoliId);
        $doctorId = $this->queueDoctorId ?: null;
        $this->queueItems = $client->getPolyclinicQueue($this->queuePoliId, $doctorId);
    }

    public function addPatient(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'queuePoliId' => 'required|string',
            'queueDoctorId' => 'required|string',
            'patientName' => 'required|string|max:255',
        ]);

        try {
            $client->addPatientToQueue($this->queuePoliId, [
                'doctor_id' => $this->queueDoctorId,
                'patient_name' => $this->patientName,
            ]);
            $this->dispatch('notify-success', message: "Pasien {$this->patientName} berhasil ditambahkan ke antrian!");
            $this->patientName = '';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function callPatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'dilayani');
            $this->dispatch('notify-success', message: 'Pasien berhasil dipanggil!');
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function completePatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'selesai');
            $this->dispatch('notify-success', message: 'Pasien selesai dilayani!');
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function skipPatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'terlewat');
            $this->dispatch('notify-success', message: 'Pasien dilewati!');
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function requeuePatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->requeuePatient($this->queuePoliId, $id);
            $this->dispatch('notify-success', message: 'Pasien berhasil dipanggil ulang (turun 2 posisi)!');
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function changeQueueStatus(DmsMiddlewareClient $client, string $id, string $newStatus): void
    {
        $item = collect($this->queueItems)->firstWhere('id', $id);
        if (!$item) return;

        $currentStatus = $item['status'];
        if ($currentStatus === $newStatus) return;

        try {
            $client->updateQueueStatus($this->queuePoliId, $id, $newStatus);
            $statusLabels = [
                'menunggu' => 'menunggu',
                'dilayani' => 'sedang dilayani',
                'selesai' => 'selesai dilayani',
                'terlewat' => 'dilewati',
            ];
            $label = $statusLabels[$newStatus] ?? $newStatus;
            $this->dispatch('notify-success', message: "Status pasien berhasil diubah menjadi '{$label}'!");
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function deleteQueueItem(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deleteQueueEntry($this->queuePoliId, $id);
            $this->dispatch('notify-success', message: 'Antrian berhasil dihapus!');
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    #[On('refresh-poli-data')]
    #[On('poli-data-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->polyclinics = $client->getPolyclinics();
        if ($this->queuePoliId) {
            $this->loadQueue($client);
        }
    }
}
