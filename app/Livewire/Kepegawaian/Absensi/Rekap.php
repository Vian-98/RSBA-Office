<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

use Livewire\Attributes\Lazy;

#[Lazy]
#[Title('Rekap Absensi')]
class Rekap extends Component
{
    use Interactions;
    use WithPagination;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="h-10 bg-slate-200 rounded-lg"></div>
                <div class="h-10 bg-slate-200 rounded-lg"></div>
                <div class="h-10 bg-slate-200 rounded-lg"></div>
                <div class="h-10 bg-slate-200 rounded-lg"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-24 bg-slate-100 rounded-xl border border-slate-200"></div>
                <div class="h-24 bg-slate-100 rounded-xl border border-slate-200"></div>
                <div class="h-24 bg-slate-100 rounded-xl border border-slate-200"></div>
            </div>
            <div class="h-80 bg-slate-100 rounded-xl border border-slate-200"></div>
        </div>
        HTML;
    }

    public $bulan;
    public $tahun;
    public $ruangan_id = null;
    public $karyawan_id = null;
    public $tanggal_spesifik = null;
    public $mode = 'bulanan'; // 'bulanan', 'harian'
    public $statusFilter = '';
    public $perPage = 15;

    // Properties for Overtime Detail Modal
    public $showOtModal = false;
    public $selectedOtKaryawan = '';
    public $selectedOtDetails = [];
    public $selectedOtFormatted = '';

    // Properties for Audit Log History Modal
    public $showHistoryModal = false;
    public $historyLogs = [];
    public $historyRecordInfo = '';

    // Properties for Edit/Correction Modal
    public $showEditModal = false;
    public $editingRecordId = null;
    public $editStatus = '';
    public $editAbsenMasuk = '';
    public $editAbsenKeluar = '';
    public $editCatatan = '';

    // Properties for Global Audit Log Modal
    public $showGlobalHistoryModal = false;
    public $historySearch = '';

    public function openGlobalHistoryModal()
    {
        $this->showGlobalHistoryModal = true;
    }

    public function closeGlobalHistoryModal()
    {
        $this->showGlobalHistoryModal = false;
    }

    public function editRecord($id)
    {
        $record = JadwalKerjaDetail::findOrFail($id);
        $this->editingRecordId = $id;
        $this->editStatus = $record->status_kehadiran instanceof \App\Enums\StatusKehadiran 
            ? $record->status_kehadiran->value 
            : $record->status_kehadiran;
        
        $this->editAbsenMasuk = $record->absen_masuk_at 
            ? Carbon::parse($record->absen_masuk_at)->format('Y-m-d\TH:i') 
            : '';
        $this->editAbsenKeluar = $record->absen_keluar_at 
            ? Carbon::parse($record->absen_keluar_at)->format('Y-m-d\TH:i') 
            : '';
            
        $this->editCatatan = $record->catatan;
        $this->showEditModal = true;
    }

    public function saveCorrection()
    {
        $record = JadwalKerjaDetail::with('shift')->findOrFail($this->editingRecordId);
        
        $absenMasukLama = $record->absen_masuk_at;
        $absenKeluarLama = $record->absen_keluar_at;
        $statusLama = $record->status_kehadiran instanceof \App\Enums\StatusKehadiran 
            ? $record->status_kehadiran->value 
            : (string) $record->status_kehadiran;
        $catatanLama = $record->catatan;

        $absenMasukBaru = $this->editAbsenMasuk ? Carbon::parse($this->editAbsenMasuk)->format('Y-m-d H:i:s') : null;
        $absenKeluarBaru = $this->editAbsenKeluar ? Carbon::parse($this->editAbsenKeluar)->format('Y-m-d H:i:s') : null;
        $statusBaru = $this->editStatus ?: 'belum_dicek';
        $catatanBaru = $this->editCatatan ?: null;

        $menitTerlambat = 0;
        $menitPulangCepat = 0;
        $menitOvertime = 0;

        if ($statusBaru === 'terlambat' && $catatanBaru && preg_match('/Terlambat (-?\d+) menit/i', $catatanBaru, $m)) {
            $menitTerlambat = abs((int) $m[1]);
        }
        if ($statusBaru === 'pulang_cepat' && $catatanBaru && preg_match('/Pulang cepat (-?\d+) menit/i', $catatanBaru, $m)) {
            $menitPulangCepat = abs((int) $m[1]);
        }

        if ($absenMasukBaru && $absenKeluarBaru) {
            $masuk = Carbon::parse($absenMasukBaru);
            $keluar = Carbon::parse($absenKeluarBaru);
            if ($record->shift && $record->shift->jam_keluar) {
                $jamKeluar = Carbon::parse($record->shift->jam_keluar);
                $targetCheckout = Carbon::parse(Carbon::parse($record->tanggal)->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                if ($record->shift->lintas_hari || $jamKeluar->lt(Carbon::parse($record->shift->jam_masuk))) {
                    $targetCheckout->addDay();
                }
                if ($keluar->gt($targetCheckout)) {
                    $menitOvertime = abs($keluar->diffInMinutes($targetCheckout));
                }
            } else {
                $menitOvertime = abs($keluar->diffInMinutes($masuk));
            }
        }

        // Record Audit Log
        \App\Models\Sdm\AbsensiKoreksiLog::create([
            'detail_id' => $record->id,
            'karyawan_id' => $record->karyawan_id,
            'tanggal' => $record->tanggal,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'absen_masuk_lama' => $absenMasukLama,
            'absen_masuk_baru' => $absenMasukBaru,
            'absen_keluar_lama' => $absenKeluarLama,
            'absen_keluar_baru' => $absenKeluarBaru,
            'catatan_lama' => $catatanLama,
            'catatan_baru' => $catatanBaru,
            'user_id' => auth()->id() ?? 1,
        ]);

        $record->update([
            'status_kehadiran' => $statusBaru,
            'absen_masuk_at' => $absenMasukBaru,
            'absen_keluar_at' => $absenKeluarBaru,
            'catatan' => $catatanBaru,
            'menit_terlambat' => $menitTerlambat,
            'menit_pulang_cepat' => $menitPulangCepat,
            'menit_overtime' => $menitOvertime,
            'updated_by' => auth()->id() ?? 1,
        ]);

        $this->showEditModal = false;
        $this->toast()->success('Berhasil', 'Koreksi absensi berhasil disimpan dan di-log.')->send();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk mengakses Halaman Rekap Absensi.'
        );
        $this->bulan = $this->bulan ?: (int) date('m');
        $this->tahun = $this->tahun ?: (int) date('Y');
    }

    public function render()
    {
        $user = auth()->user();
        $allowedRuanganIds = $user ? $user->getRuanganKoordinatorIds() : [];

        // 1. Build Base Detail Query
        $baseQuery = JadwalKerjaDetail::query();
        if ($allowedRuanganIds !== null) {
            $baseQuery->whereHas('jadwalKerja', function ($q) use ($allowedRuanganIds) {
                $q->whereIn('ruangan_id', $allowedRuanganIds);
            });
        }

        if ($this->mode === 'bulanan') {
            $baseQuery->whereMonth('tanggal', $this->bulan)
                      ->whereYear('tanggal', $this->tahun);
        } else {
            if ($this->tanggal_spesifik) {
                $baseQuery->whereDate('tanggal', $this->tanggal_spesifik);
            } else {
                $baseQuery->whereDate('tanggal', date('Y-m-d'));
            }
        }

        if ($this->ruangan_id) {
            $baseQuery->whereHas('jadwalKerja', function ($q) {
                $q->where('ruangan_id', $this->ruangan_id);
            });
        }

        if ($this->karyawan_id) {
            $baseQuery->where('karyawan_id', $this->karyawan_id);
        }

        // 2. Fetch the paginated Karyawan list
        $karyawanQuery = Karyawan::query();
        if ($this->karyawan_id) {
            $karyawanQuery->where('id', $this->karyawan_id);
        }
        if ($this->ruangan_id) {
            $karyawanQuery->where('ruangan_id', $this->ruangan_id);
        }
        if ($allowedRuanganIds !== null) {
            $karyawanQuery->whereIn('ruangan_id', $allowedRuanganIds);
        }

        $perPageCount = min(100, max(5, (int) $this->perPage));

        $paginatedKaryawans = $karyawanQuery->orderBy('nama')
            ->paginate($perPageCount, ['*'], 'rekapKaryawanPage');

        if ($paginatedKaryawans->currentPage() > 1 && $paginatedKaryawans->currentPage() > $paginatedKaryawans->lastPage()) {
            $this->setPage(max(1, $paginatedKaryawans->lastPage()), 'rekapKaryawanPage');
            $paginatedKaryawans = $karyawanQuery->orderBy('nama')
                ->paginate($perPageCount, ['*'], 'rekapKaryawanPage');
        }

        $currentPageKaryawanIds = $paginatedKaryawans->pluck('id')->toArray();

        // 3. Overall Summary Calculations (for the top cards via high performance SQL aggregation)
        $summaryQuery = (clone $baseQuery)
            ->leftJoin('sdm_jadwal_shift', 'sdm_jadwal_kerja_detail.shift_id', '=', 'sdm_jadwal_shift.id');

        $summaryAgg = (clone $summaryQuery)
            ->selectRaw("
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'pulang_cepat' THEN 1 ELSE 0 END) as pulang_cepat,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'cuti' THEN 1 ELSE 0 END) as cuti,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN sdm_jadwal_kerja_detail.status_kehadiran = 'perlu_verifikasi' THEN 1 ELSE 0 END) as perlu_verifikasi,
                SUM(COALESCE(sdm_jadwal_kerja_detail.menit_terlambat, 0)) as menit_terlambat,
                SUM(COALESCE(sdm_jadwal_kerja_detail.menit_pulang_cepat, 0)) as menit_pulang_cepat,
                SUM(COALESCE(sdm_jadwal_kerja_detail.menit_overtime, 0)) as total_overtime_menit
            ")
            ->first();

        $summary = [
            'hadir' => (int) ($summaryAgg->hadir ?? 0),
            'terlambat' => (int) ($summaryAgg->terlambat ?? 0),
            'menit_terlambat' => (int) ($summaryAgg->menit_terlambat ?? 0),
            'pulang_cepat' => (int) ($summaryAgg->pulang_cepat ?? 0),
            'menit_pulang_cepat' => (int) ($summaryAgg->menit_pulang_cepat ?? 0),
            'tidak_hadir' => (int) ($summaryAgg->tidak_hadir ?? 0),
            'cuti' => (int) ($summaryAgg->cuti ?? 0),
            'izin' => (int) ($summaryAgg->izin ?? 0),
            'perlu_verifikasi' => (int) ($summaryAgg->perlu_verifikasi ?? 0),
            'total_overtime_menit' => (int) ($summaryAgg->total_overtime_menit ?? 0),
        ];

        // Fallback for minute metrics if older records do not have column values prefilled
        if (($summary['terlambat'] > 0 && $summary['menit_terlambat'] === 0) || ($summary['pulang_cepat'] > 0 && $summary['menit_pulang_cepat'] === 0)) {
            $uncalculatedRows = (clone $summaryQuery)
                ->whereIn('sdm_jadwal_kerja_detail.status_kehadiran', ['terlambat', 'pulang_cepat'])
                ->select(['sdm_jadwal_kerja_detail.status_kehadiran', 'sdm_jadwal_kerja_detail.catatan'])
                ->get();
            foreach ($uncalculatedRows as $row) {
                if ($row->status_kehadiran === 'terlambat' && $row->catatan && preg_match('/Terlambat (-?\d+) menit/i', $row->catatan, $m)) {
                    $summary['menit_terlambat'] += abs((int)$m[1]);
                } elseif ($row->status_kehadiran === 'pulang_cepat' && $row->catatan && preg_match('/Pulang cepat (-?\d+) menit/i', $row->catatan, $m)) {
                    $summary['menit_pulang_cepat'] += abs((int)$m[1]);
                }
            }
        }

        // 4. Detailed summary specifically for current page Karyawans
        $rekapRaw = (clone $baseQuery)
            ->whereIn('karyawan_id', $currentPageKaryawanIds)
            ->leftJoin('sdm_jadwal_shift', 'sdm_jadwal_kerja_detail.shift_id', '=', 'sdm_jadwal_shift.id')
            ->select([
                'sdm_jadwal_kerja_detail.id',
                'sdm_jadwal_kerja_detail.karyawan_id',
                'sdm_jadwal_kerja_detail.status_kehadiran',
                'sdm_jadwal_kerja_detail.catatan',
                'sdm_jadwal_kerja_detail.absen_masuk_at',
                'sdm_jadwal_kerja_detail.absen_keluar_at',
                'sdm_jadwal_kerja_detail.shift_id',
                'sdm_jadwal_kerja_detail.tanggal',
                'sdm_jadwal_shift.jam_masuk as shift_jam_masuk',
                'sdm_jadwal_shift.jam_keluar as shift_jam_keluar',
                'sdm_jadwal_shift.lintas_hari as shift_lintas_hari'
            ])
            ->toBase()
            ->get();

        $rekapKaryawan = [];
        // Pre-initialize rekap array for all paginated employees to keep order
        foreach ($paginatedKaryawans as $kar) {
            $rekapKaryawan[$kar->id] = [
                'karyawan' => $kar,
                'hadir' => 0,
                'terlambat' => 0,
                'menit_terlambat' => 0,
                'pulang_cepat' => 0,
                'menit_pulang_cepat' => 0,
                'tidak_hadir' => 0,
                'cuti' => 0,
                'izin' => 0,
                'perlu_verifikasi' => 0,
                'total_overtime_menit' => 0,
                'overtime_details' => [],
            ];
        }

        foreach ($rekapRaw as $row) {
            $kId = $row->karyawan_id;
            $tanggalObj = Carbon::parse($row->tanggal);
            $statusVal = $row->status_kehadiran;

            if (!isset($rekapKaryawan[$kId])) {
                continue;
            }

            if (isset($rekapKaryawan[$kId][$statusVal])) {
                $rekapKaryawan[$kId][$statusVal]++;
            }

            // Parse minutes from catatan
            $menit = 0;
            if ($statusVal === 'terlambat' && $row->catatan) {
                if (preg_match('/Terlambat (-?\d+) menit/i', $row->catatan, $matches)) {
                    $menit = abs((int) $matches[1]);
                }
            } elseif ($statusVal === 'pulang_cepat' && $row->catatan) {
                if (preg_match('/Pulang cepat (-?\d+) menit/i', $row->catatan, $matches)) {
                    $menit = abs((int) $matches[1]);
                }
            }

            if ($statusVal === 'terlambat') {
                $rekapKaryawan[$kId]['menit_terlambat'] += $menit;
            } elseif ($statusVal === 'pulang_cepat') {
                $rekapKaryawan[$kId]['menit_pulang_cepat'] += $menit;
            }

            // Overtime Calculation
            $overtimeMenit = 0;
            $overtimeKeterangan = '';
            if ($row->absen_masuk_at && $row->absen_keluar_at) {
                $masuk = Carbon::parse($row->absen_masuk_at);
                $keluar = Carbon::parse($row->absen_keluar_at);

                if ($row->shift_id && $row->shift_jam_keluar) {
                    $jamKeluar = Carbon::parse($row->shift_jam_keluar);
                    $targetCheckout = Carbon::parse($tanggalObj->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                    if ($row->shift_lintas_hari || $jamKeluar->lt(Carbon::parse($row->shift_jam_masuk))) {
                        $targetCheckout->addDay();
                    }
                    if ($keluar->gt($targetCheckout)) {
                        $overtimeMenit = abs($keluar->diffInMinutes($targetCheckout));
                        $overtimeKeterangan = "Pulang terlambat";
                    }
                } else {
                    $overtimeMenit = abs($keluar->diffInMinutes($masuk));
                    $overtimeKeterangan = "Tugas hari Libur/OFF";
                }
            }

            if ($overtimeMenit > 0) {
                $rekapKaryawan[$kId]['total_overtime_menit'] += $overtimeMenit;
                $rekapKaryawan[$kId]['overtime_details'][] = [
                    'tanggal' => $tanggalObj->translatedFormat('d M Y'),
                    'menit' => $overtimeMenit,
                    'keterangan' => $overtimeKeterangan
                ];
            }
        }

        // 5. Paginated Daily Records (limited to 15 per page to save memory)
        $recordsQuery = clone $baseQuery;
        if ($this->statusFilter) {
            $recordsQuery->where('status_kehadiran', $this->statusFilter);
        }

        $records = $recordsQuery
            ->with(['karyawan', 'shift', 'karyawan.ruangan', 'jadwalKerja', 'jadwalKerja.ruangan'])
            ->orderBy('tanggal', 'desc')
            ->paginate(15, ['*'], 'dailyPage');

        if ($records->currentPage() > 1 && $records->currentPage() > $records->lastPage()) {
            $this->setPage(max(1, $records->lastPage()), 'dailyPage');
            $records = $recordsQuery
                ->with(['karyawan', 'shift', 'karyawan.ruangan', 'jadwalKerja', 'jadwalKerja.ruangan'])
                ->orderBy('tanggal', 'desc')
                ->paginate(15, ['*'], 'dailyPage');
        }

        // 6. Paginated Global Audit Log History
        $globalHistoryLogs = null;
        if ($this->showGlobalHistoryModal) {
            $historyQuery = \App\Models\Sdm\AbsensiKoreksiLog::with(['karyawan', 'user.karyawan', 'detail'])
                ->orderBy('created_at', 'desc');

            if ($this->mode === 'bulanan') {
                $historyQuery->whereMonth('tanggal', $this->bulan)
                             ->whereYear('tanggal', $this->tahun);
            } else {
                if ($this->tanggal_spesifik) {
                    $historyQuery->whereDate('tanggal', $this->tanggal_spesifik);
                }
            }

            if ($this->ruangan_id) {
                $historyQuery->whereHas('karyawan', function ($q) {
                    $q->where('ruangan_id', $this->ruangan_id);
                });
            }

            if ($this->karyawan_id) {
                $historyQuery->where('karyawan_id', $this->karyawan_id);
            }

            if ($this->historySearch) {
                $s = trim($this->historySearch);
                $historyQuery->where(function ($q) use ($s) {
                    $q->whereHas('karyawan', function ($k) use ($s) {
                        $k->where('nama', 'like', "%{$s}%")
                          ->orWhere('gelar_depan', 'like', "%{$s}%")
                          ->orWhere('gelar_belakang', 'like', "%{$s}%")
                          ->orWhere('pin_absen', 'like', "%{$s}%");
                    })
                    ->orWhereHas('user.karyawan', function ($uk) use ($s) {
                        $uk->where('nama', 'like', "%{$s}%")
                           ->orWhere('gelar_depan', 'like', "%{$s}%")
                           ->orWhere('gelar_belakang', 'like', "%{$s}%");
                    })
                    ->orWhere('status_lama', 'like', "%{$s}%")
                    ->orWhere('status_baru', 'like', "%{$s}%")
                    ->orWhere('catatan_baru', 'like', "%{$s}%");
                });
            }

            $globalHistoryLogs = $historyQuery->paginate(15, ['*'], 'historyLogPage');

            if ($globalHistoryLogs->currentPage() > 1 && $globalHistoryLogs->currentPage() > $globalHistoryLogs->lastPage()) {
                $this->setPage(max(1, $globalHistoryLogs->lastPage()), 'historyLogPage');
                $globalHistoryLogs = $historyQuery->paginate(15, ['*'], 'historyLogPage');
            }
        }

        return view('livewire.kepegawaian.absensi.rekap', [
            'rekapKaryawan' => $rekapKaryawan,
            'summary' => $summary,
            'paginatedKaryawans' => $paginatedKaryawans,
            'records' => $records,
            'globalHistoryLogs' => $globalHistoryLogs,
        ]);
    }
}
