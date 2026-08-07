<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use App\Livewire\Gaji\Services\PayrollRekapService;

class BagianBreakdown extends Component
{
    public string $periode = '';

    public function mount(string $periode = '')
    {
        $this->periode = $periode ?: now()->format('Y-m');
    }

    public function render()
    {
        $rekapService = app(PayrollRekapService::class);
        $summary = $rekapService->getSummary($this->periode);
        $breakdown = $rekapService->getDepartmentBreakdown($summary['slips'] ?? collect());

        return view('livewire.gaji.rekap.partials.bagian-breakdown', [
            'bagianBreakdown' => $breakdown,
        ]);
    }
}
