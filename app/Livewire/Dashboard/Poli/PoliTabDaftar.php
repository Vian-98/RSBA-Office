<?php

namespace App\Livewire\Dashboard\Poli;

use App\Models\Ruangan;
use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class PoliTabDaftar extends Component
{
    // Form & Cached Data
    public array $masterRuangans = [];
    public array $polyclinics = [];

    public string $editingPoliId = '';
    public string $selectedRuanganId = '';
    public string $poliCode = '';
    public string $poliName = '';

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        return view('livewire.dashboard.poli.poli-tab-daftar');
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        $this->masterRuangans = Ruangan::where('is_active', 1)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => (string) $r->id,
                    'name' => $r->nama,
                    'code' => 'POLI-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $r->nama), 0, 8)),
                ];
            })->toArray();

        $this->polyclinics = $client->getPolyclinics();
    }

    public function updatedSelectedRuanganId(string $value): void
    {
        $ruangan = collect($this->masterRuangans)->firstWhere('id', $value);
        if ($ruangan) {
            $this->poliName = $ruangan['name'];
            $this->poliCode = $ruangan['code'];
        }
    }

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
                'ruangan_code' => $this->selectedRuanganId ?: null,
            ];

            if ($this->editingPoliId) {
                $client->updatePolyclinic($this->editingPoliId, $data);
                $this->dispatch('notify-success', message: "Poliklinik {$this->poliName} berhasil diperbarui!");
            } else {
                $client->createPolyclinic($data);
                $this->dispatch('notify-success', message: "Poliklinik {$this->poliName} berhasil ditambahkan dari Master Ruangan!");
            }

            $this->resetPoliForm();
            $this->polyclinics = $client->getPolyclinics();
            $this->dispatch('poli-data-changed');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function editPoli(string $id): void
    {
        $poli = collect($this->polyclinics)->firstWhere('id', $id);
        if ($poli) {
            $this->editingPoliId = $poli['id'];
            $this->poliCode = $poli['code'];
            $this->poliName = $poli['name'];
            $this->selectedRuanganId = $poli['ruangan_code'] ?? '';
        }
    }

    public function deletePoli(DmsMiddlewareClient $client, string $id): void
    {
        try {
            $client->deletePolyclinic($id);
            $this->dispatch('notify-success', message: 'Poliklinik berhasil dihapus!');
            $this->polyclinics = $client->getPolyclinics();
            $this->dispatch('poli-data-changed');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: $e->getMessage());
        }
    }

    public function resetPoliForm(): void
    {
        $this->reset(['editingPoliId', 'selectedRuanganId', 'poliCode', 'poliName']);
    }

    #[On('refresh-poli-data')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
