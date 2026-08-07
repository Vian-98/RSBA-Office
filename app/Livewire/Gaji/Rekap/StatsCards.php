<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use App\Livewire\Gaji\Services\PayrollRekapService;

class StatsCards extends Component
{
    public string $periode = '';

    public function mount(string $periode = '')
    {
        $this->periode = $periode ?: now()->format('Y-m');
    }

    public function render()
    {
        $summary = app(PayrollRekapService::class)->getSummary($this->periode);

        return view('livewire.gaji.rekap.partials.stats-cards', [
            'totalGajiBersih'    => $summary['totalGajiBersih'] ?? 0,
            'totalPotongan'      => $summary['totalPotongan'] ?? 0,
            'jumlahKaryawan'     => $summary['jumlahKaryawan'] ?? 0,
            'lastMonthNet'       => $summary['lastMonthNet'] ?? 0,
            'percentChange'      => $summary['percentChange'] ?? 0,
            'estimasiBulanDepan' => $summary['estimasiBulanDepan'] ?? 0,
        ]);
    }
}
