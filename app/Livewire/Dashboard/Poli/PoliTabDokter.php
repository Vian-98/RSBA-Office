<?php

namespace App\Livewire\Dashboard\Poli;

use App\Models\Sdm\Dokter as MasterDokter;
use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PoliTabDokter extends Component
{
    use WithFileUploads;

    // Form & Cached Data
    public array $masterDoctors = [];
    public array $polyclinics = [];
    public array $doctors = [];

    public string $selectedPoliId = '';
    public string $editingDoctorId = '';
    public string $selectedMasterDoctorId = '';
    public string $doctorName = '';
    public $doctorPhoto = null;
    public string $doctorSpecialty = '';
    public string $doctorCode = '';
    public int $doctorSortOrder = 0;
    public bool $doctorIsActive = true;

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadMasterDoctors();
        $this->polyclinics = $client->getPolyclinics();
    }

    public function render()
    {
        return view('livewire.dashboard.poli.poli-tab-dokter');
    }

    public function loadMasterDoctors(): void
    {
        $this->masterDoctors = MasterDokter::with(['karyawan', 'spesialis'])
            ->get()
            ->map(function ($doc) {
                $karyawanName = $doc->karyawan?->full_nama ?? $doc->karyawan?->nama ?? 'Dokter tanpa nama';
                $spesialisName = $doc->spesialis?->nama ?? $doc->spesialis?->spesialisasi ?? 'Dokter Umum';
                $nip = $doc->karyawan?->nip ?? $doc->karyawan?->nik ?? 'DOC-' . $doc->id;

                return [
                    'id' => (string) $doc->id,
                    'name' => $karyawanName,
                    'specialty' => $spesialisName,
                    'code' => $nip,
                ];
            })->toArray();
    }

    public function loadDoctors(DmsMiddlewareClient $client): void
    {
        if (!$this->selectedPoliId) {
            $this->doctors = [];
            return;
        }

        $this->doctors = $client->getPolyclinicDoctors($this->selectedPoliId);
    }

    public function updatedSelectedMasterDoctorId(string $value): void
    {
        $doc = collect($this->masterDoctors)->firstWhere('id', $value);
        if ($doc) {
            $this->doctorName = $doc['name'];
            $this->doctorSpecialty = $doc['specialty'];
            $this->doctorCode = $doc['code'];
        }
    }

    public function saveDoctor(DmsMiddlewareClient $client): void
    {
        $this->validate([
            'selectedPoliId' => 'required|string',
            'selectedMasterDoctorId' => 'required|string',
            'doctorSortOrder' => 'required|integer|min:0',
        ]);

        try {
            $masterDoc = collect($this->masterDoctors)->firstWhere('id', $this->selectedMasterDoctorId);
            $name = $masterDoc['name'] ?? $this->doctorName;
            $specialty = $masterDoc['specialty'] ?? $this->doctorSpecialty;
            $code = $masterDoc['code'] ?? $this->doctorCode;

            $data = [
                'name' => $name,
                'specialty' => $specialty,
                'doctor_code' => $code,
                'master_doctor_uuid' => $this->selectedMasterDoctorId,
                'is_active' => $this->doctorIsActive,
                'sort_order' => $this->doctorSortOrder,
            ];

            if ($this->doctorPhoto) {
                $data['photo'] = $this->doctorPhoto;
            }

            if ($this->editingDoctorId) {
                $client->updatePolyclinicDoctor($this->selectedPoliId, $this->editingDoctorId, $data);
                $this->dispatch('notify-success', message: "Dokter {$name} berhasil diperbarui!");
            } else {
                $client->createPolyclinicDoctor($this->selectedPoliId, $data);
                $this->dispatch('notify-success', message: "Dokter {$name} berhasil ditambahkan dari Master SDM!");
            }

            $this->resetDoctorForm();
            $this->polyclinics = $client->getPolyclinics();
            $this->loadDoctors($client);
            $this->dispatch('poli-data-changed');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
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
            $this->selectedMasterDoctorId = $doctor['master_doctor_uuid'] ?? '';
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
                $this->polyclinics = $client->getPolyclinics();
                $this->loadDoctors($client);
                $this->dispatch('poli-data-changed');
            } catch (\Exception $e) {
                $this->dispatch('notify-error', message: $e->getMessage());
            }
        }
    }

    public function deleteDoctor(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deletePolyclinicDoctor($this->selectedPoliId, $id);
            $this->dispatch('notify-success', message: 'Dokter berhasil dihapus!');
            $this->polyclinics = $client->getPolyclinics();
            $this->loadDoctors($client);
            $this->dispatch('poli-data-changed');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function resetDoctorForm(): void
    {
        $this->reset(['editingDoctorId', 'selectedMasterDoctorId', 'doctorName', 'doctorPhoto', 'doctorSpecialty', 'doctorCode', 'doctorSortOrder']);
        $this->doctorIsActive = true;
    }

    #[On('refresh-poli-data')]
    #[On('poli-data-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadMasterDoctors();
        $this->polyclinics = $client->getPolyclinics();
        if ($this->selectedPoliId) {
            $this->loadDoctors($client);
        }
    }
}
