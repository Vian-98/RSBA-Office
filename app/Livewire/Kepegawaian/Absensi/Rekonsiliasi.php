<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Services\KalkulasiKehadiranService;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\DB;

class Rekonsiliasi extends Component
{
    use WithPagination, Interactions;

    public $batchId;
    public $log;
    public $search = '';
    public $filterStatus = 'single_punch'; // Default: Tampilkan Single Punch (Belum Ada Pasangan)
    public $sortBy = 'tanggal';
    public $sortDirection = 'asc';

    // Modal Edit/Revisi
    public $editingStagingId = null;
    public $editNamaMentah = '';
    public $editTanggal = '';
    public $editClockIn = '';
    public $editClockOut = '';
    public $editKaryawanId = null;
    public $editCatatan = '';

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->log = AbsensiImportLog::findOrFail($batchId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    public function toggleSort($column = 'tanggal')
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openEditModal($stagingId)
    {
        $staging = AbsensiStaging::find($stagingId);
        if (!$staging) return;

        $this->editingStagingId = $staging->id;
        $this->editNamaMentah    = $staging->nama_mentah;
        $this->editTanggal       = \Carbon\Carbon::parse($staging->tanggal)->format('d/m/Y');
        $this->editClockIn       = $staging->clock_in_aktual ?? '';
        $this->editClockOut      = $staging->clock_out_aktual ?? '';
        $this->editKaryawanId    = $staging->karyawan_id;
        $this->editCatatan       = $staging->catatan_mesin ?? '';

        $this->dispatch('open-modal', id: 'modal-revisi-absensi');
    }

    public function simpanRevisi()
    {
        if (!$this->editingStagingId) return;

        $staging = AbsensiStaging::find($this->editingStagingId);
        if ($staging) {
            $statusMatching = $this->editKaryawanId ? 'matched' : $staging->status_matching;

            $clockIn  = !empty($this->editClockIn) ? trim($this->editClockIn) : null;
            $clockOut = !empty($this->editClockOut) ? trim($this->editClockOut) : null;

            $catatanBaru = "Direvisi manual SDM";
            if ($this->editCatatan && $this->editCatatan !== 'Direvisi manual SDM') {
                $catatanBaru .= " ({$this->editCatatan})";
            }

            $staging->update([
                'clock_in_aktual'  => $clockIn,
                'clock_out_aktual' => $clockOut,
                'karyawan_id'      => $this->editKaryawanId,
                'status_matching'  => $statusMatching,
                'catatan_mesin'    => $catatanBaru,
            ]);

            $this->updateLogCounters();
            $this->dispatch('close-modal', id: 'modal-revisi-absensi');
            $this->toast()->success('Sukses', 'Data absensi berhasil direvisi.')->send();
        }
    }

    public function tautkanManual($stagingId, $karyawanId)
    {
        if (!$karyawanId) return;

        $staging = AbsensiStaging::find($stagingId);
        if ($staging) {
            $staging->update([
                'karyawan_id' => $karyawanId,
                'status_matching' => 'matched'
            ]);

            $this->updateLogCounters();
            $this->toast()->success('Berhasil', 'Karyawan berhasil ditautkan.')->send();
        }
    }

    public function abaikanBaris($stagingId)
    {
        $staging = AbsensiStaging::find($stagingId);
        if ($staging) {
            $staging->update([
                'karyawan_id' => null,
                'status_matching' => 'diabaikan'
            ]);

            $this->updateLogCounters();
            $this->toast()->info('Diabaikan', 'Baris ditandai untuk diabaikan.')->send();
        }
    }

    private function updateLogCounters()
    {
        $matched = AbsensiStaging::where('import_batch_id', $this->batchId)->where('status_matching', 'matched')->count();
        $unmatched = AbsensiStaging::where('import_batch_id', $this->batchId)->whereIn('status_matching', ['unmatched', 'ambiguous'])->count();

        $this->log->update([
            'baris_matched' => $matched,
            'baris_unmatched' => $unmatched
        ]);
    }

    public function commitKeJadwal()
    {
        set_time_limit(180);

        $stagings = AbsensiStaging::where('import_batch_id', $this->batchId)
            ->where('status_matching', 'matched')
            ->get();

        if ($stagings->isEmpty()) {
            $this->toast()->warning('Perhatian', 'Tidak ada data matched untuk di-commit.')->send();
            return;
        }

        $karyawanIds = $stagings->pluck('karyawan_id')->filter()->unique()->toArray();
        $startDate = $stagings->min('tanggal');
        $endDate = $stagings->max('tanggal');

        if ($startDate) {
            $carbonStart = \Carbon\Carbon::parse($startDate);
            $month = $carbonStart->month;
            $year = $carbonStart->year;
            foreach ($karyawanIds as $kId) {
                if ($kId) {
                    \App\Models\Sdm\JadwalKerja::ensureEmployeeDetailsExist($kId, $month, $year);
                }
            }
        }

        $details = JadwalKerjaDetail::whereIn('karyawan_id', $karyawanIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('jadwalKerja')
            ->get()
            ->groupBy(function($item) {
                $tanggalStr = $item->tanggal instanceof \Carbon\Carbon
                    ? $item->tanggal->format('Y-m-d')
                    : substr($item->tanggal, 0, 10);
                return $item->karyawan_id . '_' . $tanggalStr;
            });

        $service = new KalkulasiKehadiranService();
        $berhasil = 0;

        DB::beginTransaction();
        try {
            foreach ($stagings as $staging) {
                $key = $staging->karyawan_id . '_' . $staging->tanggal->format('Y-m-d');
                $detail = isset($details[$key]) ? $details[$key]->first() : null;

                if ($detail) {
                    $ruanganId = $detail->jadwalKerja ? $detail->jadwalKerja->ruangan_id : null;

                    $hasil = $service->hitungStatus(
                        $staging->karyawan_id,
                        $staging->tanggal->format('Y-m-d'),
                        $staging->clock_in_aktual,
                        $staging->clock_out_aktual,
                        $detail->shift_id,
                        $ruanganId
                    );

                    $masukAt = null;
                    if ($staging->clock_in_aktual && preg_match('/^\d{1,2}:\d{2}/', trim($staging->clock_in_aktual))) {
                         $masukAt = $staging->tanggal->format('Y-m-d') . ' ' . trim($staging->clock_in_aktual);
                    }

                    $keluarAt = null;
                    if ($staging->clock_out_aktual && preg_match('/^\d{1,2}:\d{2}/', trim($staging->clock_out_aktual))) {
                        $outDate = $staging->tanggal->format('Y-m-d');
                        if ($detail->shift && $detail->shift->lintas_hari) {
                            $jamNum = (int) substr(trim($staging->clock_out_aktual), 0, 2);
                            if ($jamNum < 15) {
                                $outDate = $staging->tanggal->copy()->addDay()->format('Y-m-d');
                            }
                        }
                        $keluarAt = $outDate . ' ' . trim($staging->clock_out_aktual);
                    }

                    $detail->update([
                        'absen_masuk_at' => $masukAt,
                        'absen_keluar_at' => $keluarAt,
                        'status_kehadiran' => $hasil['status'],
                        'catatan' => $hasil['catatan'],
                    ]);

                    $staging->update([
                        'detail_terkirim_id' => $detail->id
                    ]);

                    $berhasil++;
                }
            }

            $this->log->update([
                'status' => 'dikunci',
                'dikunci_at' => now(),
            ]);

            DB::commit();
            $this->toast()
                ->success('Sukses', "$berhasil data berhasil di-commit ke Jadwal Kerja.")
                ->flash()
                ->send();

            return redirect()->route('kepegawaian.absensi.index');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast()->error('Error', 'Gagal commit data: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $query = AbsensiStaging::where('import_batch_id', $this->batchId);

        // Hitung statistik terpisah untuk banner top cards
        $countSinglePunch = AbsensiStaging::where('import_batch_id', $this->batchId)->where('catatan_mesin', 'like', '%SINGLE_PUNCH%')->count();
        $countExtraPunch  = AbsensiStaging::where('import_batch_id', $this->batchId)->where('catatan_mesin', 'like', '%EXTRA_PUNCH%')->count();
        $countKonflikJadwal = AbsensiStaging::where('import_batch_id', $this->batchId)->where('catatan_mesin', 'like', '%KONFLIK_JADWAL_VS_TAP%')->count();

        if ($this->filterStatus === 'single_punch') {
            $query->where('catatan_mesin', 'like', '%SINGLE_PUNCH%');
        } elseif ($this->filterStatus === 'extra_punch') {
            $query->where('catatan_mesin', 'like', '%EXTRA_PUNCH%');
        } elseif ($this->filterStatus === 'konflik_jadwal') {
            $query->where('catatan_mesin', 'like', '%KONFLIK_JADWAL_VS_TAP%');
        } elseif ($this->filterStatus === 'problematic') {
            $query->where(function ($q) {
                $q->whereIn('status_matching', ['unmatched', 'ambiguous'])
                  ->orWhere('catatan_mesin', 'like', '%SINGLE_PUNCH%')
                  ->orWhere('catatan_mesin', 'like', '%EXTRA_PUNCH%');
            });
        } elseif ($this->filterStatus === 'anomali') {
            $query->whereNotNull('catatan_mesin');
        } elseif ($this->filterStatus !== 'all') {
            $query->where('status_matching', $this->filterStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('employee_id_mentah', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_mentah', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection)
              ->orderBy('nama_mentah', 'asc')
              ->orderBy('id', 'asc');

        return view('livewire.kepegawaian.absensi.rekonsiliasi', [
            'stagings'           => $query->paginate(20),
            'karyawans'          => Karyawan::select('id', 'nama', 'nip')->orderBy('nama')->get(),
            'countSinglePunch'   => $countSinglePunch,
            'countExtraPunch'    => $countExtraPunch,
            'countKonflikJadwal' => $countKonflikJadwal,
        ]);
    }
}
