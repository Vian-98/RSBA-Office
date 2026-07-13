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

#[Title('Rekap Absensi')]
class Rekap extends Component
{
    use Interactions;

    public $bulan;
    public $tahun;
    public $ruangan_id = null;
    public $karyawan_id = null;
    public $tanggal_spesifik = null;
    public $mode = 'bulanan'; // 'bulanan', 'harian'

    // Properties for Manual Correction
    public $editingRecordId = null;
    public $editStatus = '';
    public $editAbsenMasuk = '';
    public $editAbsenKeluar = '';
    public $editCatatan = '';
    public $showEditModal = false;

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
        
        $record->update([
            'status_kehadiran' => $this->editStatus ?: 'belum_dicek',
            'absen_masuk_at' => $this->editAbsenMasuk ?: null,
            'absen_keluar_at' => $this->editAbsenKeluar ?: null,
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
        $ruangans = Ruangan::orderBy('nama')->get();
        $karyawans = Karyawan::orderBy('nama')->get();

        $query = JadwalKerjaDetail::query()
            ->with(['karyawan', 'shift', 'karyawan.ruangan', 'jadwalKerja', 'jadwalKerja.ruangan'])
            ->whereNotNull('status_kehadiran');

        if ($this->mode === 'bulanan') {
            $query->whereMonth('tanggal', $this->bulan)
                  ->whereYear('tanggal', $this->tahun);
        } else {
            if ($this->tanggal_spesifik) {
                $query->whereDate('tanggal', $this->tanggal_spesifik);
            } else {
                $query->whereDate('tanggal', date('Y-m-d'));
            }
        }

        if ($this->ruangan_id) {
            $query->whereHas('jadwalKerja', function ($q) {
                $q->where('ruangan_id', $this->ruangan_id);
            });
        }

        if ($this->karyawan_id) {
            $query->where('karyawan_id', $this->karyawan_id);
        }

        $records = $query->orderBy('tanggal', 'desc')->get();

        // Aggregation logic
        $summary = [
            'hadir' => 0,
            'terlambat' => 0,
            'pulang_cepat' => 0,
            'tidak_hadir' => 0,
            'cuti' => 0,
            'izin' => 0,
            'perlu_verifikasi' => 0,
        ];

        // Also aggregate per Karyawan for the table view
        $rekapKaryawan = [];

        foreach ($records as $r) {
            $val = $r->status_kehadiran->value ?? $r->status_kehadiran;
            if (isset($summary[$val])) {
                $summary[$val]++;
            }

            if (!isset($rekapKaryawan[$r->karyawan_id])) {
                $rekapKaryawan[$r->karyawan_id] = [
                    'karyawan' => $r->karyawan,
                    'hadir' => 0,
                    'terlambat' => 0,
                    'pulang_cepat' => 0,
                    'tidak_hadir' => 0,
                    'cuti' => 0,
                    'izin' => 0,
                    'perlu_verifikasi' => 0,
                ];
            }
            if (isset($rekapKaryawan[$r->karyawan_id][$val])) {
                $rekapKaryawan[$r->karyawan_id][$val]++;
            }
        }

        return view('livewire.kepegawaian.absensi.rekap', [
            'ruangans' => $ruangans,
            'karyawans' => $karyawans,
            'records' => $records,
            'summary' => $summary,
            'rekapKaryawan' => $rekapKaryawan
        ]);
    }
}
