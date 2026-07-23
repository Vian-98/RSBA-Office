<?php

namespace App\Livewire\Piutang;

use Carbon\Carbon;
use App\Models\JmPasien;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Piutang Klaim Pasien')]
class Index extends Component
{
    use AuthorizesFromRoute, WithPagination;

    public string $periode = '';
    public string $cabar = '';
    public string $search = '';

    public function mount(): void
    {
        $this->periode = Carbon::now()->format('Y-m');
    }

    public function updatedPeriode(): void
    {
        $this->resetPage();
    }

    public function updatedCabar(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function getSummary()
    {
        $periodeKey = $this->periode ?: Carbon::now()->format('Y-m');

        $query = JmPasien::where('tgl_checkout', 'like', $periodeKey . '%');

        $totalPiutang = (clone $query)->sum('klaim');
        $totalCair = (clone $query)->sum('disetujui');
        $totalPending = $totalPiutang - $totalCair;

        $piutangBPJS = JmPasien::where('cabar', 'bpjs')->where('tgl_checkout', 'like', $periodeKey . '%')->sum('klaim');
        $piutangJKMD = JmPasien::where('cabar', 'jkmd')->where('tgl_checkout', 'like', $periodeKey . '%')->sum('klaim');

        return [
            'totalPiutang' => 'Rp ' . number_format($totalPiutang, 0, ',', '.'),
            'totalCair' => 'Rp ' . number_format($totalCair, 0, ',', '.'),
            'totalPending' => 'Rp ' . number_format($totalPending, 0, ',', '.'),
            'piutangBPJS' => 'Rp ' . number_format($piutangBPJS, 0, ',', '.'),
            'piutangJKMD' => 'Rp ' . number_format($piutangJKMD, 0, ',', '.'),
        ];
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $periodeKey = $this->periode ?: Carbon::now()->format('Y-m');

        $piutangList = JmPasien::query()
            ->where('tgl_checkout', 'like', $periodeKey . '%')
            ->when($this->cabar, fn($q) => $q->where('cabar', $this->cabar))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama_pasien', 'like', '%' . $this->search . '%')
                        ->orWhere('mrn', 'like', '%' . $this->search . '%')
                        ->orWhere('sep', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('tgl_checkout')
            ->paginate(10);

        return view('livewire.piutang.index', [
            'summary' => $this->getSummary(),
            'piutangList' => $piutangList,
        ]);
    }
}
