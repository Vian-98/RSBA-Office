<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Title('Koreksi Absensi')]
class Koreksi extends Component
{
    use Interactions;

    public $bulan;
    public $tahun;
    public $ruangan_id = null;
    public $karyawan_id = null;
    public $mode = 'bulanan';
    public $tanggal_spesifik = null;
    public $filter_status = null; // filter for showing only problematic records

    // Selected employee card expand state (karyawan_id => bool)
    public $expandedKaryawan = [];

    // Inline editing state (record_id => array of edits)
    public $inlineEdits = [];

    // Modal
    public $editingRecordId = null;
    public $editStatus = '';
    public $editAbsenMasuk = '';
    public $editAbsenKeluar = '';
    public $editCatatan = '';
    public $showEditModal = false;

    public function mount()
    {
        abort_unless(
            auth()->user()?->isSuperAdmin() || auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Akses Ditolak: Anda belum memiliki izin (view-kepegawaian-absensi) untuk mengakses Halaman Koreksi Absensi. Silakan hubungi bagian SDM/Kepegawaian.'
        );
        $this->bulan = (int) date('m');
        $this->tahun = (int) date('Y');
        $this->filter_status = 'perlu_verifikasi'; // default: show records needing verification
    }

    public function updatedRuanganId() { $this->expandedKaryawan = []; }
    public function updatedKaryawanId() { $this->expandedKaryawan = []; }
    public function updatedBulan() { $this->expandedKaryawan = []; }
    public function updatedTahun() { $this->expandedKaryawan = []; }

    public function toggleKaryawan($karyawanId)
    {
        if (isset($this->expandedKaryawan[$karyawanId])) {
            unset($this->expandedKaryawan[$karyawanId]);
        } else {
            $this->expandedKaryawan[$karyawanId] = true;
        }
    }

    public function expandAll()
    {
        // expand all loaded karyawan IDs
        // IDs are computed in render, so we trigger from view via dispatch
        $this->dispatch('expand-all');
    }

    public function editRecord($id)
    {
        $record = JadwalKerjaDetail::with(['karyawan', 'shift'])->findOrFail($id);
        $this->editingRecordId = $id;
        $this->editStatus = $record->status_kehadiran instanceof \App\Enums\StatusKehadiran
            ? $record->status_kehadiran->value
            : $record->status_kehadiran;

        $this->editAbsenMasuk = $record->absen_masuk_at
            ? Carbon::parse($record->absen_masuk_at)->format('Y-m-d\TH:i')
            : ($record->tanggal ? Carbon::parse($record->tanggal)->format('Y-m-d') . 'T' . (optional($record->shift)->jam_masuk ? substr($record->shift->jam_masuk, 0, 5) : '07:00') : '');

        $this->editAbsenKeluar = $record->absen_keluar_at
            ? Carbon::parse($record->absen_keluar_at)->format('Y-m-d\TH:i')
            : '';

        $this->editCatatan = $record->catatan ?? '';
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

        $absenMasukBaru  = $this->editAbsenMasuk  ? Carbon::parse($this->editAbsenMasuk)->format('Y-m-d H:i:s')  : null;
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
            'absen_masuk_at'   => $absenMasukBaru,
            'absen_keluar_at'  => $absenKeluarBaru,
            'catatan'          => $catatanBaru,
            'menit_terlambat'  => $menitTerlambat,
            'menit_pulang_cepat' => $menitPulangCepat,
            'menit_overtime'   => $menitOvertime,
            'updated_by'       => auth()->id() ?? 1,
        ]);

        $this->showEditModal = false;
        $this->expandedKaryawan[$record->karyawan_id] = true; // keep card open after save
        $this->toast()->success('Berhasil', 'Koreksi absensi berhasil disimpan dan di-log.')->send();
    }

    public function render()
    {
        $user = auth()->user();
        $allowedRuanganIds = $user?->getAccessibleRuanganIds('view'); // null = semua, array = ter-scope

        // Filter dropdown berdasarkan akses koordinator
        $ruangans  = $allowedRuanganIds !== null
            ? Ruangan::whereIn('id', $allowedRuanganIds)->orderBy('nama')->get()
            : Ruangan::orderBy('nama')->get();

        $karyawans = $allowedRuanganIds !== null
            ? Karyawan::whereIn('ruangan_id', $allowedRuanganIds)->orderBy('nama')->get()
            : Karyawan::orderBy('nama')->get();

        // Base query
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
            $date = $this->tanggal_spesifik ?: date('Y-m-d');
            $baseQuery->whereDate('tanggal', $date);
        }

        if ($this->ruangan_id) {
            $baseQuery->whereHas('jadwalKerja', fn($q) => $q->where('ruangan_id', $this->ruangan_id));
        }

        if ($this->karyawan_id) {
            $baseQuery->where('karyawan_id', $this->karyawan_id);
        }

        // Apply status filter
        if ($this->filter_status) {
            $baseQuery->where('status_kehadiran', $this->filter_status);
        }

        // Aggregate counts per employee using DB groupBy (memory efficient)
        $rekapRaw = (clone $baseQuery)
            ->select('karyawan_id', 'status_kehadiran', DB::raw('count(*) as total'))
            ->groupBy('karyawan_id', 'status_kehadiran')
            ->get();

        $rekapKaryawan = [];
        foreach ($rekapRaw as $row) {
            $kId      = $row->karyawan_id;
            $statusVal = $row->status_kehadiran instanceof \App\Enums\StatusKehadiran
                ? $row->status_kehadiran->value
                : $row->status_kehadiran;

            if (!isset($rekapKaryawan[$kId])) {
                $rekapKaryawan[$kId] = [
                    'hadir' => 0, 'terlambat' => 0, 'pulang_cepat' => 0,
                    'tidak_hadir' => 0, 'cuti' => 0, 'izin' => 0, 'perlu_verifikasi' => 0,
                    'total' => 0,
                ];
            }
            if (isset($rekapKaryawan[$kId][$statusVal])) {
                $rekapKaryawan[$kId][$statusVal] = (int) $row->total;
            }
            $rekapKaryawan[$kId]['total'] += (int) $row->total;
        }

        // Sort by perlu_verifikasi desc, then by name
        $karyawanIds = array_keys($rekapKaryawan);
        $karyawansMap = Karyawan::whereIn('id', $karyawanIds)->orderBy('nama')->get()->keyBy('id');

        foreach ($rekapKaryawan as $kId => &$rk) {
            $rk['karyawan'] = $karyawansMap->get($kId);
        }
        unset($rk);

        // Sort: perlu_verifikasi first
        uasort($rekapKaryawan, fn($a, $b) => $b['perlu_verifikasi'] <=> $a['perlu_verifikasi']);

        // Load detail records for expanded cards
        $detailRecords = [];
        foreach (array_keys($this->expandedKaryawan) as $expandedKid) {
            $detailRecords[$expandedKid] = (clone $baseQuery)
                ->where('karyawan_id', $expandedKid)
                ->with(['shift'])
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        // Overall totals
        $totalPerluVerifikasi = array_sum(array_column($rekapKaryawan, 'perlu_verifikasi'));

        return view('livewire.kepegawaian.absensi.koreksi', [
            'ruangans'             => $ruangans,
            'karyawans'            => $karyawans,
            'rekapKaryawan'        => $rekapKaryawan,
            'detailRecords'        => $detailRecords,
            'totalPerluVerifikasi' => $totalPerluVerifikasi,
        ]);
    }
}
