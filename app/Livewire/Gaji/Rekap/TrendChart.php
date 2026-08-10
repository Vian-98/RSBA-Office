<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Livewire\Gaji\Services\PayrollRekapService;

#[Lazy]
class TrendChart extends Component
{
    public string $periode = '';

    public function mount(string $periode = '')
    {
        $this->periode = $periode ?: now()->format('Y-m');
    }

    public function setPeriode(string $periode): void
    {
        $this->periode = $periode;
        $this->dispatch('periode-changed', periode: $periode);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm animate-pulse">
            <div class="h-6 w-48 bg-slate-200 rounded mb-4"></div>
            <div class="h-48 w-full bg-slate-100 rounded-xl flex items-center justify-center text-xs text-slate-400">
                Memuat Grafik Tren Payroll...
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        $trendMonths = app(PayrollRekapService::class)->getSixMonthTrend($this->periode);

        return view('livewire.gaji.rekap.partials.trend-chart', [
            'trendMonths' => $trendMonths,
        ]);
    }
}
