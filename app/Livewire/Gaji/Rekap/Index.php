<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Traits\AuthorizesFromRoute;
use TallStackUi\Traits\Interactions;
use App\Livewire\Gaji\Services\PayrollRekapService;
use App\Livewire\Gaji\Services\PayrollPeriodService;
use App\Livewire\Gaji\Rekap\Concerns\HasPayrollParametersModal;
use App\Livewire\Gaji\Rekap\Concerns\HasPayrollFinalisasiModal;
use App\Livewire\Gaji\Rekap\Concerns\HasPayrollNotificationModals;

#[Title('Rekap Penggajian Bulanan')]
class Index extends Component
{
    use AuthorizesFromRoute;
    use Interactions;

    use HasPayrollParametersModal;
    use HasPayrollFinalisasiModal;
    use HasPayrollNotificationModals;

    public string $periode = ''; // YYYY-MM

    public function mount(): void
    {
        $this->periode = now()->format('Y-m');

        $this->mountHasPayrollParametersModal();
        $this->mountHasPayrollFinalisasiModal();
    }

    #[On('periode-changed')]
    public function setPeriode(string $periode): void
    {
        $this->periode = $periode;
    }

    public function render(PayrollRekapService $rekapService, PayrollPeriodService $periodService)
    {
        $this->authorizeFromRoute();

        $summary = $rekapService->getSummary($this->periode);
        $lockStatus = $periodService->getLockStatus($this->periode);

        $user = auth()->user();
        $isOnlyPajak = $user && $user->can('approve-kepegawaian-gaji-pajak') && !$user->can('approve-kepegawaian-gaji');
        $isSDM = $user && $user->can('approve-kepegawaian-gaji');

        return view('livewire.gaji.rekap.index', [
            'totalPotongan' => $summary['totalPotongan'],
            'potonganBreakdown' => $summary['potonganBreakdown'],
            'jumlahKaryawan' => $summary['jumlahKaryawan'],
            'isLocked' => $lockStatus['is_locked'],
            'isOnlyPajak' => $isOnlyPajak,
            'isSDM' => $isSDM,
        ]);
    }
}
