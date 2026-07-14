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

#[Title('Rekap Absensi')]
class Rekap extends Component
{
    use Interactions;
    use WithPagination;

    public $bulan;
    public $tahun;
    public $ruangan_id = null;
    public $karyawan_id = null;
    public $tanggal_spesifik = null;
    public $mode = 'bulanan'; // 'bulanan', 'harian'
    public $statusFilter = '';

    // Properties for Manual Correction
    public $editingRecordId = null;
    public $editStatus = '';
    public $editAbsenMasuk = '';
    public $editAbsenKeluar = '';
    public $editCatatan = '';
    public $showEditModal = false;

    // Reset pagination when filter updates
    public function updatedRuanganId() { $this->resetPage('dailyPage'); }
    public function updatedKaryawanId() { $this->resetPage('dailyPage'); }
    public function updatedTanggalSpesifik() { $this->resetPage('dailyPage'); }
    public function updatedMode() { $this->resetPage('dailyPage'); }
    public function updatedBulan() { $this->resetPage('dailyPage'); }
    public function updatedTahun() { $this->resetPage('dailyPage'); }
    public function updatedStatusFilter() { $this->resetPage('dailyPage'); }

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
        $record = JadwalKerjaDetail::findOrFail($this->editingRecordId);
        
        $absenMasuk = $this->editAbsenMasuk ? Carbon::parse($this->editAbsenMasuk)->format('Y-m-d H:i:s') : null;
        $absenKeluar = $this->editAbsenKeluar ? Carbon::parse($this->editAbsenKeluar)->format('Y-m-d H:i:s') : null;

        $record->update([
            'status_kehadiran' => $this->editStatus ?: 'belum_dicek',
            'absen_masuk_at' => $absenMasuk,
            'absen_keluar_at' => $absenKeluar,
            'catatan' => $this->editCatatan ?: null,
            'updated_by' => auth()->id() ?? 1,
        ]);

        $this->showEditModal = false;
        $this->toast()->success('Berhasil', 'Koreksi absensi berhasil disimpan.')->send();
    }

    public function mount()
    {
        $this->bulan = (int) date('m');
        $this->tahun = (int) date('Y');
    }

    public function render()
    {
        $user = auth()->user();
        $allowedRuanganIds = $user?->getRuanganKoordinatorIds(); // null = semua, [] = tidak ada

        // Filter ruangan dropdown berdasarkan akses koordinator
        $ruangans = $allowedRuanganIds !== null
            ? Ruangan::whereIn('id', $allowedRuanganIds)->orderBy('nama')->get()
            : Ruangan::orderBy('nama')->get();

        // Filter karyawan dropdown berdasarkan ruangan yang bisa diakses
        $karyawans = $allowedRuanganIds !== null
            ? Karyawan::whereIn('ruangan_id', $allowedRuanganIds)->orderBy('nama')->get()
            : Karyawan::orderBy('nama')->get();

        // 1. Build Base Query
        $baseQuery = JadwalKerjaDetail::query()
            ->whereNotNull('status_kehadiran');

        // Enforce ruangan scope for koordinator
        if ($allowedRuanganIds !== null) {
            if (empty($allowedRuanganIds)) {
                $baseQuery->whereRaw('0 = 1');
            } else {
                $baseQuery->whereHas('jadwalKerja', fn($q) => $q->whereIn('ruangan_id', $allowedRuanganIds));
            }
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

        // 2. Memory-efficient Overall Summary & Per-Employee Aggregation
        $summary = [
            'hadir' => 0,
            'terlambat' => 0,
            'menit_terlambat' => 0,
            'pulang_cepat' => 0,
            'menit_pulang_cepat' => 0,
            'tidak_hadir' => 0,
            'cuti' => 0,
            'izin' => 0,
            'perlu_verifikasi' => 0,
        ];

        $rekapRaw = (clone $baseQuery)
            ->select('karyawan_id', 'status_kehadiran', 'catatan')
            ->get();

        $rekapKaryawan = [];
        foreach ($rekapRaw as $row) {
            $kId = $row->karyawan_id;
            $statusVal = $row->status_kehadiran instanceof \App\Enums\StatusKehadiran 
                ? $row->status_kehadiran->value 
                : $row->status_kehadiran;

            if (isset($summary[$statusVal])) {
                $summary[$statusVal]++;
            }

            // Parse minutes from catatan
            $menit = 0;
            if ($statusVal === 'terlambat' && $row->catatan) {
                if (preg_match('/Terlambat (-?\d+) menit/i', $row->catatan, $matches)) {
                    $menit = abs((int) $matches[1]);
                    $summary['menit_terlambat'] += $menit;
                }
            } elseif ($statusVal === 'pulang_cepat' && $row->catatan) {
                if (preg_match('/Pulang cepat (-?\d+) menit/i', $row->catatan, $matches)) {
                    $menit = abs((int) $matches[1]);
                    $summary['menit_pulang_cepat'] += $menit;
                }
            }

            if (!isset($rekapKaryawan[$kId])) {
                $rekapKaryawan[$kId] = [
                    'hadir' => 0,
                    'terlambat' => 0,
                    'menit_terlambat' => 0,
                    'pulang_cepat' => 0,
                    'menit_pulang_cepat' => 0,
                    'tidak_hadir' => 0,
                    'cuti' => 0,
                    'izin' => 0,
                    'perlu_verifikasi' => 0,
                ];
            }

            if (isset($rekapKaryawan[$kId][$statusVal])) {
                $rekapKaryawan[$kId][$statusVal]++;
            }

            if ($statusVal === 'terlambat') {
                $rekapKaryawan[$kId]['menit_terlambat'] += $menit;
            } elseif ($statusVal === 'pulang_cepat') {
                $rekapKaryawan[$kId]['menit_pulang_cepat'] += $menit;
            }
        }

        // Eager-hydrate Karyawan models in a single query
        $karyawanIds = array_keys($rekapKaryawan);
        $karyawansMap = Karyawan::whereIn('id', $karyawanIds)->get()->keyBy('id');
        foreach ($rekapKaryawan as $kId => &$rk) {
            $rk['karyawan'] = $karyawansMap->get($kId);
        }
        unset($rk);

        // 4. Paginated Daily Records (limited to 15 per page to save memory)
        $recordsQuery = clone $baseQuery;
        if ($this->statusFilter) {
            $recordsQuery->where('status_kehadiran', $this->statusFilter);
        }

        $records = $recordsQuery
            ->with(['karyawan', 'shift', 'karyawan.ruangan', 'jadwalKerja', 'jadwalKerja.ruangan'])
            ->orderBy('tanggal', 'desc')
            ->paginate(15, ['*'], 'dailyPage');

        return view('livewire.kepegawaian.absensi.rekap', [
            'ruangans' => $ruangans,
            'karyawans' => $karyawans,
            'records' => $records,
            'summary' => $summary,
            'rekapKaryawan' => $rekapKaryawan
        ]);
    }
}
