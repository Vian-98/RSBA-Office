<?php

namespace App\Livewire\Gaji\Rekap;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Jabatan;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Services\PayrollPeriodService;
use App\Services\PayrollRekapService;
use App\Services\PayrollNotificationService;

#[Title('Rekap Penggajian Bulanan')]
class Index extends Component
{
    use AuthorizesFromRoute;
    use Interactions;

    public string $periode = ''; // YYYY-MM

    // Payroll Configuration Parameters
    public $config_umk;
    public $config_potongan_telat;
    public $config_toleransi_telat;

    // Dynamic 25% UMK allocations list
    public array $allocations = [];

    // Finalisasi & SP3 Integration Form Properties
    public bool $isFinalisasiModalOpen = false;
    public string $finalisasiPeriode = '';
    public int $finalisasiKaryawanCount = 0;
    public float $finalisasiTotalPotongan = 0;
    public float $finalisasiTotalGajiBersih = 0;
    
    public string $formSp3Tgl = '';
    public string $formSp3Bayar = 'trf';
    public ?int $formSp3JabatanId = null;
    
    public array $mengetahuiOptions = [];

    // Modal state for Auto-Send Configuration
    public bool $isAutoSendModalOpen = false;
    public bool $autoSendEnabled = false;
    public string $autoSendDay = '25';
    public string $autoSendTime = '08:00';
    public int $autoSendChunkSize = 10;
    public int $autoSendDelaySeconds = 3;
    public ?array $autoSendLastRun = null;

    // Modal & Progress state for Instant Batch Sending
    public bool $isBatchSendModalOpen = false;
    public bool $isBatchSending = false;
    public int $batchTotalCount = 0;
    public int $batchProcessedCount = 0;
    public int $batchSuccessCount = 0;
    public int $batchFailedCount = 0;
    public string $currentSendingStatus = '';

    public function mount()
    {
        $this->periode = now()->format('Y-m');

        // Load payroll parameters from DB
        $this->config_umk = DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value') ?: 3000000;
        $this->config_potongan_telat = DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_kejadian')->value('value');
        if (is_null($this->config_potongan_telat)) {
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'potongan_telat_per_kejadian'], ['value' => '50000', 'created_at' => now(), 'updated_at' => now()]);
            $this->config_potongan_telat = 50000;
        }

        $this->config_toleransi_telat = DB::table('sdm_payroll_settings')->where('key', 'toleransi_telat_menit')->value('value');
        if (is_null($this->config_toleransi_telat)) {
            DB::table('sdm_payroll_settings')->insert(['key' => 'toleransi_telat_menit', 'value' => '0', 'created_at' => now(), 'updated_at' => now()]);
            $this->config_toleransi_telat = 0;
        }

        // Load 25% UMK allocations list
        $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
        foreach ($dbAllocations as $alloc) {
            $this->allocations[] = [
                'id' => $alloc->id,
                'nama' => $alloc->nama,
                'persen' => (double) $alloc->persen,
                'is_absensi' => (bool) $alloc->is_absensi,
            ];
        }

        // Fallback default allocations if empty
        if (empty($this->allocations)) {
            $this->allocations = [
                ['id' => null, 'nama' => 'Tunjangan Tetap', 'persen' => 80.0, 'is_absensi' => false],
                ['id' => null, 'nama' => 'Tunjangan Absensi', 'persen' => 20.0, 'is_absensi' => true],
            ];
        }

        // Load management Jabatans for SP3
        $this->mengetahuiOptions = Jabatan::whereHas('bagian', function ($query) {
            $query->where('group', 'manajemen');
        })->get()->map(function ($item) {
            return [
                'label' => $item->nama,
                'value' => $item->id
            ];
        })->toArray();
    }

    public function setPeriode(string $m)
    {
        $this->periode = $m;
    }

    public function addAllocation()
    {
        $this->allocations[] = [
            'id' => null,
            'nama' => '',
            'persen' => 0.0,
            'is_absensi' => false,
        ];
    }

    public function removeAllocation(int $index)
    {
        if (isset($this->allocations[$index])) {
            unset($this->allocations[$index]);
            $this->allocations = array_values($this->allocations);
        }
    }

    public function saveParameters()
    {
        if (is_string($this->config_umk)) {
            $this->config_umk = str_replace('.', '', $this->config_umk);
        }
        if (is_string($this->config_potongan_telat)) {
            $this->config_potongan_telat = str_replace('.', '', $this->config_potongan_telat);
        }

        $this->validate([
            'config_umk' => 'required|numeric|min:0',
            'config_potongan_telat' => 'required|numeric|min:0',
            'config_toleransi_telat' => 'required|integer|min:0',
            'allocations.*.nama' => 'required|string|max:255',
            'allocations.*.persen' => 'required|numeric|min:0|max:100',
        ], [
            'config_umk.required' => 'UMK wajib diisi.',
            'config_umk.numeric' => 'UMK harus berupa angka.',
            'config_umk.min' => 'UMK tidak boleh kurang dari 0.',
            'config_potongan_telat.required' => 'Potongan telat wajib diisi.',
            'config_potongan_telat.numeric' => 'Potongan telat harus berupa angka.',
            'config_potongan_telat.min' => 'Potongan telat tidak boleh kurang dari 0.',
            'config_toleransi_telat.required' => 'Toleransi keterlambatan wajib diisi.',
            'config_toleransi_telat.integer' => 'Toleransi keterlambatan harus berupa bilangan bulat.',
            'config_toleransi_telat.min' => 'Toleransi keterlambatan tidak boleh kurang dari 0.',
            'allocations.*.nama.required' => 'Nama alokasi tunjangan wajib diisi.',
            'allocations.*.persen.required' => 'Persentase wajib diisi.',
        ]);

        $totalPersen = 0.0;
        foreach ($this->allocations as $alloc) {
            $totalPersen += (double) $alloc['persen'];
        }

        if ($totalPersen !== 100.0) {
            $this->toast()->error('Gagal !', 'Total persentase alokasi tunjangan harus tepat 100% (saat ini ' . $totalPersen . '%).')->send();
            return;
        }

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'umk'], ['value' => $this->config_umk, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'potongan_telat_per_kejadian'], ['value' => $this->config_potongan_telat, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'toleransi_telat_menit'], ['value' => $this->config_toleransi_telat, 'updated_at' => now()]);

            $keptIds = [];
            foreach ($this->allocations as $alloc) {
                if (!empty($alloc['id'])) {
                    DB::table('sdm_payroll_allowance_allocations')
                        ->where('id', $alloc['id'])
                        ->update([
                            'nama' => $alloc['nama'],
                            'persen' => $alloc['persen'],
                            'is_absensi' => $alloc['is_absensi'] ? 1 : 0,
                            'updated_at' => now(),
                        ]);
                    $keptIds[] = $alloc['id'];
                } else {
                    $newId = DB::table('sdm_payroll_allowance_allocations')->insertGetId([
                        'nama' => $alloc['nama'],
                        'persen' => $alloc['persen'],
                        'is_absensi' => $alloc['is_absensi'] ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $keptIds[] = $newId;
                }
            }

            DB::table('sdm_payroll_allowance_allocations')
                ->whereNotIn('id', $keptIds)
                ->delete();

            DB::commit();

            $this->allocations = [];
            $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
            foreach ($dbAllocations as $alloc) {
                $this->allocations[] = [
                    'id' => $alloc->id,
                    'nama' => $alloc->nama,
                    'persen' => (double) $alloc->persen,
                    'is_absensi' => (bool) $alloc->is_absensi,
                ];
            }

            $this->dispatch('close-modal', id: 'modal-payroll-parameters');
            $this->toast()->success('Berhasil !', 'Parameter payroll & alokasi tunjangan berhasil disimpan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function render(PayrollRekapService $rekapService)
    {
        $this->authorizeFromRoute();

        $summary = $rekapService->getSummary($this->periode);
        $bagianBreakdown = $rekapService->getDepartmentBreakdown($summary['slips']);
        $trendMonths = $rekapService->getSixMonthTrend($this->periode);
        $insightText = $rekapService->generateInsight(
            $this->periode,
            $summary['jumlahKaryawan'],
            $summary['lastMonthNet'],
            $summary['percentChange']
        );

        $user = auth()->user();
        $isOnlyPajak = $user && $user->hasRole('Pajak') && !$user->hasRole('Staff-SDM') && !$user->hasRole('Super-Admin');
        $isSDM = $user && ($user->hasRole('Staff-SDM') || $user->hasRole('Super-Admin'));

        return view('livewire.gaji.rekap.index', [
            'totalGajiBersih' => $summary['totalGajiBersih'],
            'totalPotongan' => $summary['totalPotongan'],
            'potonganBreakdown' => $summary['potonganBreakdown'],
            'jumlahKaryawan' => $summary['jumlahKaryawan'],
            'percentChange' => $summary['percentChange'],
            'lastMonthNet' => $summary['lastMonthNet'],
            'estimasiBulanDepan' => $summary['estimasiBulanDepan'],
            'bagianBreakdown' => $bagianBreakdown,
            'trendMonths' => $trendMonths,
            'insightText' => $insightText,
            'isOnlyPajak' => $isOnlyPajak,
            'isSDM' => $isSDM,
        ]);
    }

    public function openFinalisasiModal(string $periode, int $count, float $potongan, float $gajiBersih)
    {
        $this->finalisasiPeriode = $periode;
        $this->finalisasiKaryawanCount = $count;
        $this->finalisasiTotalPotongan = $potongan;
        $this->finalisasiTotalGajiBersih = $gajiBersih;

        $this->formSp3Tgl = now()->format('Y-m-d');
        $this->formSp3Bayar = 'trf';
        
        $this->formSp3JabatanId = null;
        if (!empty($this->mengetahuiOptions)) {
            $dirOpt = collect($this->mengetahuiOptions)->first(function ($opt) {
                return str_contains(strtolower($opt['label']), 'direktur utama');
            });
            if ($dirOpt) {
                $this->formSp3JabatanId = $dirOpt['value'];
            } else {
                $this->formSp3JabatanId = $this->mengetahuiOptions[0]['value'];
            }
        }

        $this->isFinalisasiModalOpen = true;
    }

    public function closeFinalisasiModal()
    {
        $this->isFinalisasiModalOpen = false;
        $this->finalisasiPeriode = '';
        $this->finalisasiKaryawanCount = 0;
        $this->finalisasiTotalPotongan = 0;
        $this->finalisasiTotalGajiBersih = 0;
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

    public function submitFinalisasi(PayrollPeriodService $periodService)
    {
        $this->validate([
            'formSp3Tgl' => 'required|date',
            'formSp3Bayar' => 'required|in:tunai,trf,giro',
            'formSp3JabatanId' => 'required|exists:sdm_jabatan,id',
        ], [
            'formSp3Tgl.required' => 'Tanggal SP3 wajib diisi.',
            'formSp3Bayar.required' => 'Metode pembayaran wajib diisi.',
            'formSp3JabatanId.required' => 'Pejabat menyetujui wajib dipilih.',
        ]);

        try {
            $periodService->submitFinalisasi(
                $this->finalisasiPeriode,
                $this->formSp3Tgl,
                $this->formSp3Bayar,
                $this->formSp3JabatanId,
                $this->finalisasiKaryawanCount,
                $this->finalisasiTotalGajiBersih
            );

            $this->closeFinalisasiModal();
            $this->toast()->success('Berhasil !', 'Payroll periode ' . $this->finalisasiPeriode . ' berhasil disetujui & dikunci. Surat SP3 telah dikirim ke sistem persetujuan.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function unlockPeriode(string $periode, PayrollPeriodService $periodService)
    {
        try {
            $isSuperAdmin = (bool) auth()->user()?->hasRole('Super-Admin');
            $periodService->unlockPeriode($periode, $isSuperAdmin);
            $this->toast()->success('Berhasil !', 'Kunci payroll periode ' . $periode . ' berhasil dibuka. Status dikembalikan ke draft.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function openAutoSendModal(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();
        $settings = $notifService->getAutoSendSettings();

        $this->autoSendEnabled = $settings['autoSendEnabled'];
        $this->autoSendDay = $settings['autoSendDay'];
        $this->autoSendTime = $settings['autoSendTime'];
        $this->autoSendChunkSize = $settings['autoSendChunkSize'];
        $this->autoSendDelaySeconds = $settings['autoSendDelaySeconds'];
        $this->autoSendLastRun = $settings['autoSendLastRun'];
        
        $this->isAutoSendModalOpen = true;
    }

    public function closeAutoSendModal(): void
    {
        $this->isAutoSendModalOpen = false;
    }

    public function saveAutoSendSettings(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();

        $notifService->saveAutoSendSettings([
            'enabled' => $this->autoSendEnabled,
            'day' => $this->autoSendDay,
            'time' => $this->autoSendTime,
            'chunkSize' => $this->autoSendChunkSize,
            'delaySeconds' => $this->autoSendDelaySeconds,
        ]);

        $this->isAutoSendModalOpen = false;
        $this->toast()->success('Berhasil !', 'Pengaturan jadwal pengiriman otomatis slip gaji berhasil disimpan.')->send();
    }

    public function openBatchSendModal(): void
    {
        $this->authorizeFromRoute();

        $employees = Karyawan::whereNull('resign_at')->get();
        $this->batchTotalCount = $employees->count();
        $this->refreshBatchProgress(app(PayrollNotificationService::class));

        $this->isBatchSendModalOpen = true;
    }

    public function closeBatchSendModal(): void
    {
        $this->isBatchSendModalOpen = false;
        $this->isBatchSending = false;
    }

    public function sendEmail(int $karyawanId, PayrollNotificationService $notifService): void
    {
        $res = $notifService->sendSingleEmail($karyawanId, $this->periode);
        if ($res['status'] === 'success') {
            $this->toast()->success('Diproses !', $res['message'])->send();
        } elseif ($res['status'] === 'warning') {
            $this->toast()->warning('Peringatan !', $res['message'])->send();
        } else {
            $this->toast()->error('Gagal !', $res['message'])->send();
        }
    }

    public function sendSingleEmail(int $karyawanId, PayrollNotificationService $notifService): void
    {
        $this->sendEmail($karyawanId, $notifService);
    }

    public function dispatchBulkQueue(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();

        $dispatchedCount = $notifService->dispatchBulkQueue($this->periode);
        $this->isBatchSending = true;
        $this->refreshBatchProgress($notifService);

        if ($dispatchedCount > 0) {
            $this->toast()->success('Antrean Dimulai !', "{$dispatchedCount} slip gaji telah dimasukkan ke dalam antrean pengiriman background.")->send();
        } else {
            $this->toast()->info('Selesai', 'Semua slip gaji karyawan periode ini telah terkirim.')->send();
        }
    }

    public function refreshBatchProgress(PayrollNotificationService $notifService): void
    {
        $progress = $notifService->refreshBatchProgress($this->periode, $this->batchTotalCount);

        $this->batchSuccessCount = $progress['batchSuccessCount'];
        $this->batchFailedCount = $progress['batchFailedCount'];
        $this->batchProcessedCount = $progress['batchProcessedCount'];
        $this->isBatchSending = $progress['isBatchSending'];
        $this->currentSendingStatus = $progress['currentSendingStatus'];
    }
}
