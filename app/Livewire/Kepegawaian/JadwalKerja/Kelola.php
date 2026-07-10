<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Enums\StatusJadwalKerja;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Services\AturanJadwalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Throwable;
use TallStackUi\Traits\Interactions;

#[Title('Kelola Jadwal Kerja')]
class Kelola extends Component
{
    use Interactions;

    public JadwalKerja $jadwalKerja;
    public $state = []; // state[detail_id] = shift_id
    public $karyawans = [];
    public $dates = [];
    public $shiftOptions = [];
    public $isReadOnly = false;

    public function mount($id, AturanJadwalService $service)
    {
        $this->jadwalKerja = JadwalKerja::with([
            'ruangan',
            'details.karyawan',
            'details.shift'
        ])->findOrFail($id);

        // Populate valid shifts using service (includes jam override)
        $validShifts = $service->shiftValidUntukRuangan($this->jadwalKerja->ruangan_id);
        $this->shiftOptions = $validShifts->map(function ($rs) {
            $shift = $rs->shift;
            return [
                'id' => $shift->id,
                'kode' => $shift->kode,
                'warna' => $shift->warna ?? '#e2e8f0',
                'jam_masuk' => $rs->jam_masuk_efektif,
                'jam_keluar' => $rs->jam_keluar_efektif,
            ];
        })->toArray();

        $this->isReadOnly = $this->jadwalKerja->status === StatusJadwalKerja::LOCKED;

        // Populate dates for header
        $daysInMonth = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, 1)->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $this->dates[] = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, $d);
        }

        // Group details by Karyawan
        $grouped = $this->jadwalKerja->details->groupBy('karyawan_id');
        
        foreach ($grouped as $karyawanId => $details) {
            $karyawan = $details->first()->karyawan;
            $row = [
                'id' => $karyawan->id,
                'nama' => $karyawan->nama,
                'kategori' => $karyawan->kategori_kerja->nama(),
                'details' => []
            ];

            foreach ($details as $detail) {
                $day = Carbon::parse($detail->tanggal)->day;
                $row['details'][$day] = $detail;
                
                // Init state
                $this->state[$detail->id] = $detail->shift_id;
            }

            $this->karyawans[] = $row;
        }
    }

    public function save()
    {
        $this->authorize('kelola', $this->jadwalKerja);

        if ($this->isReadOnly) {
            $this->toast()->error('Gagal', 'Jadwal kerja ini sudah terkunci (locked).')->send();
            return;
        }

        try {
            DB::beginTransaction();
            $logCount = 0;

            foreach ($this->state as $detailId => $shiftId) {
                // Konversi empty string/null/0 ke null
                $shiftId = empty($shiftId) ? null : (int) $shiftId;
                
                $detail = JadwalKerjaDetail::find($detailId);
                
                // Cek apakah ada perubahan shift
                if ($detail->shift_id !== $shiftId) {
                    
                    \App\Models\Sdm\JadwalKerjaLog::create([
                        'jadwal_kerja_id' => $this->jadwalKerja->id,
                        'detail_id'       => $detail->id,
                        'karyawan_id'     => $detail->karyawan_id,
                        'shift_lama_id'   => $detail->shift_id,
                        'shift_baru_id'   => $shiftId,
                        'diubah_oleh'     => Auth::user()->karyawan_id ?? 1, // Fallback ke 1 jika user bukan karyawan
                    ]);

                    $detail->update([
                        'shift_id' => $shiftId
                    ]);
                    
                    $logCount++;
                }
            }

            DB::commit();
            if ($logCount > 0) {
                $this->toast()->success('Berhasil', "Jadwal kerja berhasil disimpan. $logCount perubahan dicatat.")->send();
            } else {
                $this->toast()->info('Tidak Ada Perubahan', 'Jadwal kerja disimpan tanpa ada perubahan.')->send();
            }

        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $th->getMessage())->send();
        }
    }

    public function publish()
    {
        $this->authorize('publish', $this->jadwalKerja);

        if ($this->jadwalKerja->status === StatusJadwalKerja::LOCKED) {
            $this->toast()->error('Gagal', 'Jadwal sudah terkunci.')->send();
            return;
        }

        $this->dialog()
            ->question('Publikasikan Jadwal?', 'Jadwal yang dipublikasikan dapat dilihat oleh pegawai dan pengajuan tukar shift dapat dilakukan.')
            ->confirm('Ya, Publikasikan', 'confirmPublish')
            ->cancel('Batal')
            ->send();
    }

    public function confirmPublish()
    {
        $this->save(); // Simpan draft terakhir

        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->toast()->success('Berhasil', 'Jadwal berhasil dipublikasikan!')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function render()
    {
        return view('livewire.kepegawaian.jadwal-kerja.kelola');
    }
}
