<?php

namespace App\Livewire\Dashboard;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class PoliAdmin extends Component
{
    use WithFileUploads;

    // Active tab: poli | doctors | queue
    public string $activeTab = 'poli';

    // Success/error alerts
    public string $successMessage = '';
    public string $errorMessage = '';

    // Cached data
    public array $polyclinics = [];
    public array $doctors = [];
    public array $queueItems = [];

    // Poli form
    public string $editingPoliId = '';
    public string $poliCode = '';
    public string $poliName = '';

    // Doctor form
    public string $selectedPoliId = '';
    public string $editingDoctorId = '';
    public string $doctorName = '';
    public $doctorPhoto = null; // Livewire file upload
    public string $doctorSpecialty = '';
    public int $doctorSortOrder = 0;
    public bool $doctorIsActive = true;

    // Queue form
    public string $queuePoliId = '';
    public string $queueDoctorId = '';
    public string $patientName = '';

    /**
     * Load initial data.
     */
    public function mount(DmsMiddlewareClient $client): void
    {
        $this->polyclinics = $client->getPolyclinics();
    }

    public function render()
    {
        return view('livewire.dashboard.poli-admin')->title('Manajemen Poliklinik');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    // ──── Poli CRUD ─────────────────────────────────────────────────────────────

    public function savePoli(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'poliCode' => 'required|string|max:50',
            'poliName' => 'required|string|max:255',
        ]);

        try {
            $data = [
                'code' => strtoupper($this->poliCode),
                'name' => $this->poliName,
            ];

            if ($this->editingPoliId) {
                $client->updatePolyclinic($this->editingPoliId, $data);
                $this->successMessage = "Poliklinik {$this->poliName} berhasil diperbarui!";
            } else {
                $client->createPolyclinic($data);
                $this->successMessage = "Poliklinik {$this->poliName} berhasil ditambahkan!";
            }

            $this->resetPoliForm();
            $this->polyclinics = $client->getPolyclinics();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function editPoli(string $id): void
    {
        $poli = collect($this->polyclinics)->firstWhere('id', $id);
        if ($poli) {
            $this->editingPoliId = $poli['id'];
            $this->poliCode = $poli['code'];
            $this->poliName = $poli['name'];
        }
    }

    public function deletePoli(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deletePolyclinic($id);
            $this->successMessage = 'Poliklinik berhasil dihapus!';
            $this->polyclinics = $client->getPolyclinics();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetPoliForm(): void
    {
        $this->reset(['editingPoliId', 'poliCode', 'poliName']);
    }

    // ──── Doctor CRUD ───────────────────────────────────────────────────────────

    public function loadDoctors(DmsMiddlewareClient $client): void
    {
        if (!$this->selectedPoliId) {
            $this->doctors = [];
            return;
        }

        $this->doctors = $client->getPolyclinicDoctors($this->selectedPoliId);
    }

    public function saveDoctor(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'selectedPoliId' => 'required|string',
            'doctorName' => 'required|string|max:255',
            'doctorPhoto' => 'nullable|image|max:2048',
            'doctorSpecialty' => 'nullable|string|max:255',
            'doctorSortOrder' => 'required|integer|min:0',
        ]);

        try {
            $data = [
                'name' => $this->doctorName,
                'specialty' => $this->doctorSpecialty ?: null,
                'is_active' => $this->doctorIsActive,
                'sort_order' => $this->doctorSortOrder,
            ];

            if ($this->doctorPhoto) {
                $data['photo'] = $this->doctorPhoto;
            }

            if ($this->editingDoctorId) {
                $client->updatePolyclinicDoctor($this->selectedPoliId, $this->editingDoctorId, $data);
                $this->successMessage = "Dokter {$this->doctorName} berhasil diperbarui!";
            } else {
                $client->createPolyclinicDoctor($this->selectedPoliId, $data);
                $this->successMessage = "Dokter {$this->doctorName} berhasil ditambahkan!";
            }

            $this->resetDoctorForm();
            $this->loadDoctors($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function editDoctor(string $id): void
    {
        $doctor = collect($this->doctors)->firstWhere('id', $id);
        if ($doctor) {
            $this->editingDoctorId = $doctor['id'];
            $this->doctorName = $doctor['name'];
            $this->doctorSpecialty = $doctor['specialty'] ?? '';
            $this->doctorSortOrder = $doctor['sort_order'] ?? 0;
            $this->doctorIsActive = $doctor['is_active'] ?? true;
        }
    }

    public function toggleDoctorActive(DmsMiddlewareClient $client, string $id): void
    {
        $doctor = collect($this->doctors)->firstWhere('id', $id);
        if ($doctor) {
            try {
                $client->updatePolyclinicDoctor($this->selectedPoliId, $id, [
                    'is_active' => !$doctor['is_active'],
                ]);
                $this->loadDoctors($client);
            } catch (\Exception $e) {
                $this->errorMessage = $e->getMessage();
            }
        }
    }

    public function deleteDoctor(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deletePolyclinicDoctor($this->selectedPoliId, $id);
            $this->successMessage = 'Dokter berhasil dihapus!';
            $this->loadDoctors($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetDoctorForm(): void
    {
        $this->reset(['editingDoctorId', 'doctorName', 'doctorPhoto', 'doctorSpecialty', 'doctorSortOrder']);
        $this->doctorIsActive = true;
    }

    // ──── Queue Management ──────────────────────────────────────────────────────

    public function loadQueue(DmsMiddlewareClient $client): void
    {
        if (!$this->queuePoliId) {
            $this->queueItems = [];
            return;
        }

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
            $this->successMessage = "Pasien {$this->patientName} berhasil ditambahkan ke antrian!";
            $this->patientName = '';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function callPatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'dilayani');
            $this->successMessage = 'Pasien berhasil dipanggil!';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function completePatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'selesai');
            $this->successMessage = 'Pasien selesai dilayani!';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function skipPatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->updateQueueStatus($this->queuePoliId, $id, 'terlewat');
            $this->successMessage = 'Pasien dilewati!';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function requeuePatient(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->requeuePatient($this->queuePoliId, $id);
            $this->successMessage = 'Pasien berhasil dipanggil ulang (turun 2 posisi)!';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function changeQueueStatus(DmsMiddlewareClient $client, string $id, string $newStatus): void
    {
        $item = collect($this->queueItems)->firstWhere('id', $id);
        if (!$item) return;

        $currentStatus = $item['status'];
        if ($currentStatus === $newStatus) return;

        try {
            $this->successMessage = '';
            $this->errorMessage = '';

            if ($currentStatus === 'terlewat' && $newStatus === 'menunggu') {
                $client->requeuePatient($this->queuePoliId, $id);
                $this->successMessage = 'Pasien berhasil dipanggil ulang (turun 2 posisi)!';
            } else {
                $client->updateQueueStatus($this->queuePoliId, $id, $newStatus);
                $statusLabels = [
                    'menunggu' => 'menunggu',
                    'dilayani' => 'sedang dilayani',
                    'selesai' => 'selesai dilayani',
                    'terlewat' => 'dilewati',
                ];
                $label = $statusLabels[$newStatus] ?? $newStatus;
                $this->successMessage = "Status pasien berhasil diubah menjadi '{$label}'!";
            }
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function deleteQueueItem(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deleteQueueEntry($this->queuePoliId, $id);
            $this->successMessage = 'Antrian berhasil dihapus!';
            $this->loadQueue($client);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Refresh data on demand (e.g., from JS polling).
     */
    #[On('poli-data-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->polyclinics = $client->getPolyclinics();
        if ($this->selectedPoliId) {
            $this->loadDoctors($client);
        }
        if ($this->queuePoliId) {
            $this->loadQueue($client);
        }
    }
}
