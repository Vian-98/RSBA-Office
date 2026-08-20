<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisplayMonitorAdmin extends Component
{
    // Tabs: dashboard | devices | data | logs | inpatient_rooms | server_room
    public string $activeTab = 'dashboard';

    // Success/error alerts
    public string $successMessage = '';
    public string $errorMessage = '';

    public function isSuperAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->traitHasPermissionTo('super-admin-bypass') || ($user->is_superadmin ?? false);
    }

    public function canAccessServerRoom(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $this->isSuperAdmin()
            || $user->can('view-server-room-monitoring');
    }

    public function render()
    {
        return view('livewire.dashboard.display-monitor-admin', [
            'isSuperAdmin'        => $this->isSuperAdmin(),
            'canAccessServerRoom' => $this->canAccessServerRoom(),
        ])->title('Display Monitor Admin Panel');
    }

    public function setTab(string $tab): void
    {
        if ($tab === 'logs' && !$this->isSuperAdmin()) {
            $this->errorMessage = 'Akses ditolak. Tab Log Audit hanya dapat diakses oleh Super-Admin.';
            return;
        }

        if ($tab === 'server_room' && !$this->canAccessServerRoom()) {
            $this->errorMessage = 'Akses ditolak. Tab Monitoring Ruang Server memerlukan izin khusus (view-server-room-monitoring).';
            return;
        }

        $this->activeTab = $tab;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage   = '';
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
