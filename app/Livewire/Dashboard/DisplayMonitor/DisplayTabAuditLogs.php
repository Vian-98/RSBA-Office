<?php

namespace App\Livewire\Dashboard\DisplayMonitor;

use App\Services\DmsMiddlewareClient;
use Livewire\Attributes\On;
use Livewire\Component;

class DisplayTabAuditLogs extends Component
{
    public array $auditLogs = [];

    public function mount(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }

    public function render()
    {
        return view('livewire.dashboard.display-monitor.display-tab-audit-logs');
    }

    public function isSuperAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->hasRole(['Super-Admin', 'SuperAdmin', 'superadmin']) || ($user->is_superadmin ?? false);
    }

    public function loadData(DmsMiddlewareClient $client): void
    {
        if (!$this->isSuperAdmin()) {
            $this->auditLogs = [];
            return;
        }

        $this->auditLogs = $client->getAuditLogs();
    }

    #[On('display-status-changed')]
    public function refreshData(DmsMiddlewareClient $client): void
    {
        $this->loadData($client);
    }
}
