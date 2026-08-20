<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Livewire\Gaji\Services\PayrollRekapService;
use App\Livewire\Gaji\Services\PayrollPeriodService;
use TallStackUi\Traits\Interactions;

#[Lazy]
class HistoryTable extends Component
{
    use Interactions;

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

    public function submitToReviewPajak(string $periode, PayrollPeriodService $periodService)
    {
        try {
            $periodService->submitToReviewPajak($periode);
            $this->toast()->success('Berhasil !', 'Payroll periode ' . $periode . ' telah dikirim ke Tim Pajak untuk direview.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function approveByPajak(string $periode, PayrollPeriodService $periodService)
    {
        try {
            $periodService->approveByPajak($periode);
            $this->toast()->success('Berhasil !', 'Review pajak selesai. Payroll periode ' . $periode . ' telah dikembalikan ke SDM untuk finalisasi.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function rejectByPajak(string $periode, PayrollPeriodService $periodService)
    {
        try {
            $periodService->rejectByPajak($periode);
            $this->toast()->warning('Ditolak', 'Data gaji dikembalikan ke SDM untuk diperbaiki.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function unlockPeriode(string $periode, PayrollPeriodService $periodService)
    {
        try {
            $isSuperAdmin = (bool) auth()->user()?->can('unlock-payroll-approved');
            $periodService->unlockPeriode($periode, $isSuperAdmin);
            $this->toast()->success('Berhasil !', 'Kunci payroll periode ' . $periode . ' telah dibuka kembali.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function openFinalisasiModal(string $periode, int $count, float $potongan, float $gajiBersih)
    {
        $this->dispatch('trigger-open-finalisasi-modal', 
            periode: $periode, 
            count: $count, 
            potongan: $potongan, 
            gajiBersih: $gajiBersih
        );
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm animate-pulse space-y-4">
            <div class="h-6 w-56 bg-slate-200 rounded"></div>
            <div class="space-y-3">
                <div class="h-10 w-full bg-slate-100 rounded"></div>
                <div class="h-10 w-full bg-slate-100 rounded"></div>
                <div class="h-10 w-full bg-slate-100 rounded"></div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        $trendMonths = app(PayrollRekapService::class)->getSixMonthTrend($this->periode);

        $user = auth()->user();
        $isOnlyPajak = $user && $user->can('approve-kepegawaian-gaji-pajak') && !$user->can('approve-kepegawaian-gaji');
        $isSDM = $user && $user->can('approve-kepegawaian-gaji');

        return view('livewire.gaji.rekap.partials.history-table', [
            'trendMonths' => $trendMonths,
            'isSDM'       => $isSDM,
            'isOnlyPajak' => $isOnlyPajak,
        ]);
    }
}
