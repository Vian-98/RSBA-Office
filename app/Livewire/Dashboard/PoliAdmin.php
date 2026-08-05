<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PoliAdmin extends Component
{
    // Active tab: poli | doctors | queue
    public string $activeTab = 'poli';

    // Success/error alerts
    public string $successMessage = '';
    public string $errorMessage = '';

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

    /**
     * Manual Sync Master Data on-demand.
     */
    public function syncMasterData(): void
    {
        $this->dispatch('refresh-poli-data');
        $this->successMessage = 'Master Data SDM Dokter & Ruangan Poliklinik berhasil disinkronisasi!';
    }

    #[On('notify-success')]
    public function handleNotifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->errorMessage = '';
    }

    #[On('notify-error')]
    public function handleNotifyError(string $message): void
    {
        $this->errorMessage = $message;
        $this->successMessage = '';
    }
}
