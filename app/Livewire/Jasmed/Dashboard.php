<?php

namespace App\Livewire\Jasmed;

use Carbon\Carbon;
use App\Models\JmJasa;
use Livewire\Component;
use App\Models\JmPasien;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class Dashboard extends Component
{

    public string $periode;
    public $layanan;
    public array $layanan_opt = [
        ['label' => 'Rajal', 'value' => 'rajal'],
        ['label' => 'Ranap', 'value' => 'ranap'],
    ];

    public $cabar;
    public array $cabar_opt = [
        ['label' => 'BPJS', 'value' => 'bpjs'],
        ['label' => 'Tunai', 'value' => 'tunai'],
        ['label' => 'JKMD', 'value' => 'jkmd']
    ];


    public $batch;
    public $batchOptions = [
        ['label' => '1', 'value' => 1],
        ['label' => '2', 'value' => 2],
        ['label' => '3', 'value' => 3]
    ];

    public $pasienDiajukan = 0;
    public $pasienDisetujui = 0;
    public $pasienPending = 0;

    public $klaimDiajukan = 0;
    public $klaimDisetujui = 0;
    public $klaimPending = 0;

    public $prosentaseDisetujui = 0;

    public $total_jasa = 0;

    // Menampilkan content detail
    public $content;

    public function detail($route)
    {
        $this->content = $route;
    }

    function mount()
    {
        $this->periode = Carbon::now()->format('Y-m');
    }

    function updated($propertyName)
    {
        $this->updateDashboard();
    }

    function updateDashboard()
    {
        $layanan = $this->layanan;
        $cabar = $this->cabar;
        $periode = $this->periode;
        $batch = $this->batch;


        // Stats Pasien
        // $this->getStatsPasien(periode: $periode, layanan: $layanan, cabar: $cabar);


        // Stats Klaim
        // $this->getStatsKlaim(periode: $periode, layanan: $layanan, cabar: $cabar);

        // Stats Jasa
        // $this->getStatsJasa(periode: $periode, layanan: $layanan, cabar: $cabar);
    }


    #[Computed]
    public function getStats(): array
    {
        $baseQuery = JmPasien::where('tgl_checkout', 'like', "$this->periode%")
            ->when(
                $this->layanan,
                fn($query) => $query->where('layanan', $this->layanan)
            )
            ->when(
                $this->cabar,
                fn($query) => $query->where('cabar', $this->cabar)
            )
            ->when(
                $this->batch,
                fn($query) => $query->where('batch', $this->batch)
            );


        // Diajukan
        $totalDiajukan = (clone $baseQuery)->sum('klaim');
        $pasienDiajukan = (clone $baseQuery)->count();


        // Disetujui
        $queryDisetujui  = (clone $baseQuery)->where('disetujui', '>', 0);
        $totalDisetujui = (clone $queryDisetujui)->sum('disetujui');
        $pasienDisetujui = (clone $queryDisetujui)->count();


        // Pending
        $queryPending = (clone $baseQuery)->where('disetujui', 0);
        $totalPending = (clone $queryPending)->sum('klaim');
        $pasienPending = (clone $queryPending)->count();



        return [
            'totalDiajukan' => $this->rupiah($totalDiajukan),
            'pasienDiajukan' => $this->rupiah($pasienDiajukan),
            'totalDisetujui' => $this->rupiah($totalDisetujui),
            'pasienDisetujui' => $this->rupiah($pasienDisetujui),
            'persenteseDisetujui' =>  round(
                ((
                    $pasienDisetujui  / ($pasienDiajukan ? $pasienDiajukan : 1)
                ) * 100),
                2
            ) . " %",
            'totalPending' => $this->rupiah($totalPending),
            'pasienPending' => $this->rupiah($pasienPending),
        ];
    }

    private function rupiah($value): string
    {
        return number_format($value, 0, ',', '.');
    }

    #[Computed]
    public function getStatsJasa()
    {
        $periode = $this->periode;
        $layanan = $this->layanan;
        $cabar = $this->cabar;
        $batch = $this->batch;

        // total jasa
        $jasa = JmJasa::whereHas(
            'prosentase.pasien', //relation jmJasa => JmProsentase => JmPasien
            function ($query) use ($periode, $layanan, $cabar, $batch) {
                $query //Query to relations JmPasien (as above)
                    ->where('tgl_checkout', 'like', "$periode%")
                    ->when(
                        $layanan,
                        fn($query) => $query->where('layanan', $layanan)
                    )
                    ->when(
                        $cabar,
                        fn($query) => $query->where('cabar', $cabar)
                    )
                    ->when(
                        $batch,
                        fn($query) => $query->where('batch', $batch)
                    )
                ;
            }
        )
            ->sum('jasa');
        return number_format($jasa, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.jasmed.dashboard');
    }
}
