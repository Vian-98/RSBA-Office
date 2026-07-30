<?php

namespace App\Livewire\Jasmed;

use Carbon\Carbon;
use App\Models\JmPasien;
use App\Models\Sdm\Karyawan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use TallStackUi\Traits\Interactions;

class Tunai extends Component
{
    use WithPagination, Interactions;

    public string $search = '';
    public string $periode = '';
    public string $layanan = ''; // rajal / ranap
    public ?int $dokterId = null;

    public int $perPage = 10;

    public function mount(): void
    {
        $this->periode = Carbon::now()->format('Y-m');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPeriode(): void
    {
        $this->resetPage();
    }

    public function updatingLayanan(): void
    {
        $this->resetPage();
    }

    public function updatingDokterId(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function getDokterOptions()
    {
        return Karyawan::whereHas('dokter')
            ->orWhere('gelar_depan', 'like', '%dr%')
            ->select('id', 'nama', 'gelar_depan', 'gelar_belakang')
            ->orderBy('nama')
            ->get();
    }

    #[Computed]
    public function getSummary()
    {
        $query = JmPasien::where('cabar', 'tunai')
            ->where('tgl_checkout', 'like', $this->periode . '%')
            ->when($this->layanan, fn($q) => $q->where('layanan', $this->layanan));

        $totalPasien = (clone $query)->count();
        $totalKlaim = (clone $query)->sum('klaim');
        $totalDisetujui = (clone $query)->where('disetujui', '>', 0)->sum('disetujui');
        $totalPending = (clone $query)->where('disetujui', 0)->sum('klaim');

        return [
            'totalPasien' => number_format($totalPasien, 0, ',', '.'),
            'totalKlaim' => 'Rp ' . number_format($totalKlaim, 0, ',', '.'),
            'totalDisetujui' => 'Rp ' . number_format($totalDisetujui, 0, ',', '.'),
            'totalPending' => 'Rp ' . number_format($totalPending, 0, ',', '.'),
        ];
    }

    public function render()
    {
        $pasien = JmPasien::query()
            ->where('cabar', 'tunai')
            ->where('tgl_checkout', 'like', $this->periode . '%')
            ->when($this->layanan, fn($q) => $q->where('layanan', $this->layanan))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama_pasien', 'like', '%' . $this->search . '%')
                        ->orWhere('mrn', 'like', '%' . $this->search . '%')
                        ->orWhere('sep', 'like', '%' . $this->search . '%');
                });
            })
            ->with(['rincian', 'dokter'])
            ->orderByDesc('tgl_checkout')
            ->paginate($this->perPage);

        return view('livewire.jasmed.tunai', [
            'pasien' => $pasien,
            'summary' => $this->getSummary(),
        ]);
    }
}
