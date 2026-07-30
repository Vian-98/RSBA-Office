<?php

namespace App\Livewire\Forms\Asset;

use App\Models\Maintenance\Work as MaintenanceWork;
use Livewire\Form;

class MaintcWorkForm extends Form
{
    public ?string $catatan = null;
    public ?float $total_biaya;
    public ?array $dokumentasi = [];

    public function endWork(MaintenanceWork $maintenanceWork, $keterangan, $dokumentasi, $total_biaya): MaintenanceWork
    {
        $maintenanceWork->update([
            'selesai' => now(),
            'selesai_by' => auth()->id(),
            'catatan' => $keterangan ?? null,
            'status' => 'done',
            'total_biaya' => $total_biaya, // sum total biaya dari komponen
            'dokumentasi' => $dokumentasi,
        ]);
        return $maintenanceWork;
    }
}
