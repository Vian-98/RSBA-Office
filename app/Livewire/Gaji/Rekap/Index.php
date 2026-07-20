<?php

namespace App\Livewire\Gaji\Rekap;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Bagian;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use TallStackUi\Traits\Interactions;

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

    public function mount()
    {
        $this->periode = now()->format('Y-m');

        // Load payroll parameters from DB
        $this->config_umk = DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value');
        $this->config_potongan_telat = DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_menit')->value('value');

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
        $this->mengetahuiOptions = \App\Models\Sdm\Jabatan::whereHas('bagian', function ($query) {
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

        // Validate total percentage sum equals 100%
        $totalPersen = 0.0;
        foreach ($this->allocations as $alloc) {
            $totalPersen += (double) $alloc['persen'];
        }

        if ($totalPersen !== 100.0) {
            $this->toast()
                ->error('Gagal !', 'Total persentase alokasi tunjangan harus tepat 100% (saat ini ' . $totalPersen . '%).')
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // Save settings
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'umk'], ['value' => $this->config_umk, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'potongan_telat_per_menit'], ['value' => $this->config_potongan_telat, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'toleransi_telat_menit'], ['value' => $this->config_toleransi_telat, 'updated_at' => now()]);

            // Save allocations
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

            // Delete removed allocations
            DB::table('sdm_payroll_allowance_allocations')
                ->whereNotIn('id', $keptIds)
                ->delete();

            DB::commit();

            // Reload configurations to fetch fresh IDs
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

    public function render()
    {
        $this->authorizeFromRoute();

        // 1. Current Month's Slips
        $slips = DB::table('sdm_payroll_slips')
            ->where('periode', $this->periode)
            ->get();

        $totalGajiBersih = $slips->sum('gaji_bersih');
        
        // Total Potongan is total_potongan + potongan_pph21 + potongan_bank
        $totalPotongan = $slips->sum('total_potongan') + $slips->sum('potongan_pph21') + $slips->sum('potongan_bank');
        $jumlahKaryawan = $slips->count();

        // 2. Last Month's Net Salary & Percent Change
        $lastMonthPeriode = Carbon::parse($this->periode . '-01')->subMonth()->format('Y-m');
        $lastMonthNet = DB::table('sdm_payroll_slips')
            ->where('periode', $lastMonthPeriode)
            ->sum('gaji_bersih');

        $percentChange = 0;
        if ($lastMonthNet > 0) {
            $percentChange = (($totalGajiBersih - $lastMonthNet) / $lastMonthNet) * 100;
        }

        // 3. Next Month Estimate (Routine component execution)
        $estimasiBulanDepan = 0;
        foreach ($slips as $slip) {
            // Routine Earnings: Gaji Pokok, Tunjangan Tetap, Tunjangan Absensi, Tunjangan Jabatan, Tunjangan Shift, Tunjangan Radiologi, Tunjangan Lain
            $routineEarnings = (double) $slip->gaji_pokok + 
                (double) $slip->tunjangan_tetap + 
                (double) $slip->tunjangan_absensi + 
                (double) $slip->tunjangan_jabatan + 
                (double) $slip->tunjangan_shift + 
                (double) $slip->tunjangan_radiologi + 
                (double) $slip->tunjangan_lain;

            // Routine Deductions: BPJS Kesehatan, BPJS Ketenagakerjaan, PPh21, Potongan Bank
            $routineDeductions = (double) $slip->potongan_bpjs_kes + 
                (double) $slip->potongan_bpjs_tk + 
                (double) $slip->potongan_pph21 + 
                (double) $slip->potongan_bank;

            $estimasiBulanDepan += ($routineEarnings - $routineDeductions);
        }

        // 4. Department Breakdown
        $karyawanIds = $slips->pluck('karyawan_id')->unique()->toArray();
        $karyawans = Karyawan::with(['jabatan.bagian'])
            ->whereIn('id', $karyawanIds)
            ->get()
            ->keyBy('id');

        $bagianBreakdown = [];
        foreach ($slips as $slip) {
            $karyawan = $karyawans->get($slip->karyawan_id);
            $bagianNama = $karyawan && $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian 
                ? $karyawan->jabatan->first()->bagian->nama 
                : 'Umum';

            if (!isset($bagianBreakdown[$bagianNama])) {
                $bagianBreakdown[$bagianNama] = [
                    'total_gaji_bersih' => 0,
                    'karyawan_count' => 0,
                ];
            }
            $bagianBreakdown[$bagianNama]['total_gaji_bersih'] += $slip->gaji_bersih;
            $bagianBreakdown[$bagianNama]['karyawan_count'] += 1;
        }

        // Sort breakdown by salary expense descending
        uasort($bagianBreakdown, fn($a, $b) => $b['total_gaji_bersih'] <=> $a['total_gaji_bersih']);

        // 5. 6-Month Trend
        $currentDate = Carbon::parse($this->periode . '-01');
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $currentDate->copy()->subMonths($i)->format('Y-m');
        }

        $trendMonths = [];
        foreach ($months as $m) {
            $mSlips = DB::table('sdm_payroll_slips')->where('periode', $m)->get();
            $lock = DB::table('sdm_payroll_period_locks')->where('periode', $m)->first();
            $trendMonths[] = [
                'periode' => $m,
                'label' => Carbon::parse($m . '-01')->translatedFormat('F Y'),
                'total_gaji_bersih' => $mSlips->sum('gaji_bersih'),
                'total_potongan' => $mSlips->sum('total_potongan') + $mSlips->sum('potongan_pph21') + $mSlips->sum('potongan_bank'),
                'karyawan_count' => $mSlips->count(),
                'is_approved' => $lock ? (bool)$lock->is_approved : false,
                'status' => $lock->status ?? 'draft',
                'sp3_status' => DB::table('surat_sp3')->where('payroll_periode', $m)->value('status'),
            ];
        }

        // 6. Generate dynamic monthly insight text
        $insightText = "";
        if ($jumlahKaryawan === 0) {
            $insightText = "Belum ada data slip gaji yang dicatat untuk periode " . Carbon::parse($this->periode . '-01')->translatedFormat('F Y') . ". Silakan kelola gaji karyawan terlebih dahulu.";
        } else {
            if ($lastMonthNet > 0) {
                $absChange = abs(round($percentChange, 1));
                if ($percentChange > 0) {
                    $insightText = "Pengeluaran gaji bulan ini meningkat " . $absChange . "% dibandingkan bulan lalu (" . Carbon::parse($this->periode . '-01')->subMonth()->translatedFormat('F Y') . "). Hal ini dipengaruhi oleh penambahan slip gaji baru atau peningkatan jam lembur karyawan.";
                } elseif ($percentChange < 0) {
                    $insightText = "Pengeluaran gaji bulan ini menurun " . $absChange . "% dibandingkan bulan lalu (" . Carbon::parse($this->periode . '-01')->subMonth()->translatedFormat('F Y') . "). Hal ini menunjukkan adanya efisiensi biaya atau pengurangan jumlah potongan/lembur pada periode ini.";
                } else {
                    $insightText = "Pengeluaran gaji bulan ini sama persis dengan bulan lalu (" . Carbon::parse($this->periode . '-01')->subMonth()->translatedFormat('F Y') . "). Anggaran belanja pegawai terpantau stabil.";
                }
            } else {
                $insightText = "Bulan lalu (" . Carbon::parse($this->periode . '-01')->subMonth()->translatedFormat('F Y') . ") belum memiliki data penggajian. Ini adalah bulan awal rekapitulasi data penggajian yang tercatat di sistem.";
            }
        }

        $user = auth()->user();
        $isOnlyPajak = $user->hasRole('Pajak') && !$user->hasRole('Staff-SDM') && !$user->hasRole('Super-Admin');
        $isSDM = $user->hasRole('Staff-SDM') || $user->hasRole('Super-Admin');

        return view('livewire.gaji.rekap.index', [
            'totalGajiBersih' => $totalGajiBersih,
            'totalPotongan' => $totalPotongan,
            'jumlahKaryawan' => $jumlahKaryawan,
            'percentChange' => $percentChange,
            'lastMonthNet' => $lastMonthNet,
            'estimasiBulanDepan' => $estimasiBulanDepan,
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
            // Find Direktur Utama (DIR) or first available
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

    /**
     * SDM submits draft payroll to Pajak team for review.
     */
    public function submitToReviewPajak(string $periode)
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'draft') {
            $this->toast()->error('Gagal !', 'Periode ini sudah tidak dalam status draft.')->send();
            return;
        }

        DB::table('sdm_payroll_period_locks')->updateOrInsert(
            ['periode' => $periode],
            [
                'status' => 'review_pajak',
                'is_approved' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->toast()->success('Berhasil !', 'Payroll periode ' . $periode . ' telah dikirim ke Tim Pajak untuk direview.')->send();
    }

    /**
     * Pajak team approves & sends back to SDM for final approval.
     */
    public function approveByPajak(string $periode)
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'review_pajak') {
            $this->toast()->error('Gagal !', 'Periode ini tidak dalam status review pajak.')->send();
            return;
        }

        DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
            'status' => 'review_sdm',
            'updated_at' => now(),
        ]);

        $this->toast()->success('Berhasil !', 'Review pajak selesai. Payroll periode ' . $periode . ' telah dikembalikan ke SDM untuk finalisasi.')->send();
    }

    /**
     * Pajak team rejects and sends back to SDM draft.
     */
    public function rejectByPajak(string $periode)
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'review_pajak') {
            $this->toast()->error('Gagal !', 'Periode ini tidak dalam status review pajak.')->send();
            return;
        }

        DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
            'status' => 'draft',
            'updated_at' => now(),
        ]);

        $this->toast()->warning('Ditolak', 'Data gaji dikembalikan ke SDM untuk diperbaiki.')->send();
    }

    public function submitFinalisasi()
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

        // Ensure status is review_sdm before final approval
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $this->finalisasiPeriode)->first();
        if (!$lock || $lock->status !== 'review_sdm') {
            $this->toast()->error('Gagal !', 'Periode ini belum mendapat persetujuan dari Tim Pajak.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Lock period & set approved status
            DB::table('sdm_payroll_period_locks')->where('periode', $this->finalisasiPeriode)->update([
                'is_approved' => true,
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Generate automatic SP3 number
            $last = DB::table('surat_sp3')
                ->select('no')
                ->where('jabatan_id', $this->formSp3JabatanId)
                ->orderBy('id', 'desc')
                ->first();

            $jab = DB::table('sdm_jabatan')->where('id', $this->formSp3JabatanId)->first();
            $tanggal = date('d.m.Y', strtotime($this->formSp3Tgl));
            $no = 1;
            if ($last) {
                $fullNomor = explode('/', $last->no);
                $lastNomor = $fullNomor[0];
                $no = (int)$lastNomor + 1;
            }
            $noSurat = "{$no}/S4/SP.3/PBA-{$jab->kode_surat}/{$tanggal}";

            // 3. Create SP3
            $sp3Id = DB::table('surat_sp3')->insertGetId([
                'no' => $noSurat,
                'tahun' => date('Y', strtotime($this->formSp3Tgl)),
                'tgl' => $this->formSp3Tgl,
                'rekanan' => 'Gaji Karyawan',
                'bayar' => $this->formSp3Bayar,
                'keterangan' => 'Pembayaran Gaji Karyawan RSBA Periode ' . Carbon::parse($this->finalisasiPeriode . '-01')->translatedFormat('F Y'),
                'status' => 'pending',
                'payroll_periode' => $this->finalisasiPeriode,
                'jabatan_id' => $this->formSp3JabatanId,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create SP3 details
            DB::table('surat_sp3_details')->insert([
                'sp3_id' => $sp3Id,
                'keterangan' => 'Total Gaji Bersih Periode ' . Carbon::parse($this->finalisasiPeriode . '-01')->translatedFormat('F Y') . ' (' . $this->finalisasiKaryawanCount . ' Karyawan)',
                'nominal' => $this->finalisasiTotalGajiBersih,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->closeFinalisasiModal();
            $this->toast()->success('Berhasil !', 'Payroll periode ' . $this->finalisasiPeriode . ' berhasil disetujui & dikunci. Surat SP3 telah dikirim ke sistem persetujuan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function unlockPeriode(string $periode)
    {
        $isSuperAdmin = auth()->user()?->hasRole('Super-Admin');

        // Check associated SP3 status
        $sp3 = DB::table('surat_sp3')->where('payroll_periode', $periode)->first();
        if ($sp3) {
            if ($sp3->status === 'approved') {
                if (!$isSuperAdmin) {
                    $this->toast()->error('Akses Ditolak', 'Surat SP3 untuk periode ini telah disetujui Direksi. Hanya Super Admin yang dapat membuka kunci.')->send();
                    return;
                }
                // Super Admin can force-unlock — falls through to unlock logic below
            }
            // rejected: semua user dengan approve permission boleh buka kunci untuk revisi
        }

        DB::beginTransaction();
        try {
            // Reset lock status back to draft instead of deleting
            DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
                'status' => 'draft',
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => now(),
            ]);

            // Delete associated SP3 and its details if still pending
            if ($sp3) {
                DB::table('surat_sp3_details')->where('sp3_id', $sp3->id)->delete();
                DB::table('surat_sp3')->where('id', $sp3->id)->delete();
            }

            DB::commit();
            $this->toast()->success('Berhasil !', 'Kunci payroll periode ' . $periode . ' berhasil dibuka. Status dikembalikan ke draft.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }
}
