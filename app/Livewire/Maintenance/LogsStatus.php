<?php

namespace App\Livewire\Maintenance;

use App\Models\Maintenance\Request as MaintenanceRequest;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class LogsStatus extends Component
{
    public ?object $request;
    public ?object $jadwal;
    public ?object $work;

    public function mount($assetId)
    {
        $this->request = MaintenanceRequest::with([
            'jadwal',
            'user_req.karyawan',
            'user_verif.karyawan',
            'jadwal.work'
        ])
            ->where('asset_id', $assetId)
            ->first();

        if (!$this->request) {
            $this->request = new MaintenanceRequest();
            $this->request->user_request = null;
            $this->request->user_verify = null;
            $this->request->status = 'belum ada permintaan';
            $this->request->ket_reject = 'Tidak ada permintaan untuk asset ini / asset ini adalah komponen.';
        }

        $this->jadwal = $this->request?->jadwal;
        $this->work = $this->jadwal?->work;
    }

    public function render()
    {
        return view('livewire.maintenance.logs-status');
    }
}
