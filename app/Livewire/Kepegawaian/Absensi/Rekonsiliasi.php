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
    public $filterStatus = 'all';

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->log = AbsensiImportLog::findOrFail($batchId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
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

        // Optimization: Fetch all details in one query and group by karyawan_id . '_' . tanggal
        $karyawanIds = $stagings->pluck('karyawan_id')->unique()->toArray();
        $startDate = $stagings->min('tanggal');
        $endDate = $stagings->max('tanggal');

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
            $this->toast()->success('Sukses', "$berhasil data berhasil di-commit ke Jadwal Kerja.")->send();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast()->error('Error', 'Gagal commit data: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $query = AbsensiStaging::where('import_batch_id', $this->batchId);

        if ($this->filterStatus !== 'all') {
            $query->where('status_matching', $this->filterStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('employee_id_mentah', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_mentah', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.kepegawaian.absensi.rekonsiliasi', [
            'stagings' => $query->paginate(20),
            'karyawans' => Karyawan::select('id', 'nama', 'nip')->orderBy('nama')->get()
        ]);
    }
}
