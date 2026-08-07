<?php

namespace App\Livewire\Gaji\Rekap;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Livewire\Gaji\Services\PayrollRekapService;

class ParameterCard extends Component
{
    public string $periode = '';
    public $config_umk;
    public $config_potongan_telat;
    public $config_toleransi_telat;
    public array $allocations = [];

    public function mount(string $periode = '')
    {
        $this->periode = $periode ?: now()->format('Y-m');

        $this->config_umk = DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value') ?: 3000000;
        $this->config_potongan_telat = DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_kejadian')->value('value') ?: 50000;
        $this->config_toleransi_telat = DB::table('sdm_payroll_settings')->where('key', 'toleransi_telat_menit')->value('value') ?: 0;

        $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
        foreach ($dbAllocations as $alloc) {
            $this->allocations[] = [
                'id' => $alloc->id,
                'nama' => $alloc->nama,
                'persen' => (double) $alloc->persen,
                'is_absensi' => (bool) $alloc->is_absensi,
            ];
        }

        if (empty($this->allocations)) {
            $this->allocations = [
                ['id' => null, 'nama' => 'Tunjangan Tetap', 'persen' => 80.0, 'is_absensi' => false],
                ['id' => null, 'nama' => 'Tunjangan Absensi', 'persen' => 20.0, 'is_absensi' => true],
            ];
        }
    }

    public function render()
    {
        $rekapService = app(PayrollRekapService::class);
        $summary = $rekapService->getSummary($this->periode);
        $insightText = $rekapService->generateInsight(
            $this->periode,
            $summary['jumlahKaryawan'] ?? 0,
            $summary['lastMonthNet'] ?? 0,
            $summary['percentChange'] ?? 0
        );

        return view('livewire.gaji.rekap.partials.parameter-card', [
            'insightText' => $insightText,
        ]);
    }
}
