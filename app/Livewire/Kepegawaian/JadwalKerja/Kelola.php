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
    public $cutiDates = [];

    public function mount($id, AturanJadwalService $service)
    {
        $this->jadwalKerja = JadwalKerja::with([
            'ruangan',
            'details.karyawan',
            'details.shift'
        ])->findOrFail($id);

        $karyawanIds = $this->jadwalKerja->details->pluck('karyawan_id')->unique()->toArray();
        $approvedCutis = \App\Models\Surat\SuratCuti::whereIn('karyawan_id', $karyawanIds)
            ->where('status', 'approved')
            ->get();

        foreach ($approvedCutis as $sc) {
            $dates = json_decode($sc->tgl_cuti, true);
            if (is_array($dates)) {
                foreach ($dates as $d) {
                    $this->cutiDates["{$sc->karyawan_id}-{$d}"] = $sc->no_surat;
                }
            }
        }

        $user = Auth::user();
        $canView = false;
        $canManage = false;

        if ($user) {
            if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
                $canView = true;
                $canManage = true;
            } else {
                $ownRuanganId = $user->karyawan?->ruangan_id;
                $ruanganIds = $user->isKoordinator() ? ($user->getRuanganKoordinatorIds() ?? []) : [];
                
                // Cek hak melihat
                if ($this->jadwalKerja->ruangan_id === $ownRuanganId || in_array($this->jadwalKerja->ruangan_id, $ruanganIds)) {
                    $canView = true;
                }
                
                // Cek hak mengelola (edit)
                if (in_array($this->jadwalKerja->ruangan_id, $ruanganIds)) {
                    $canManage = true;
                }
            }
        }

        abort_unless($canView, 403, 'Anda tidak memiliki akses ke jadwal ruangan ini.');

        // Populate valid shifts using service (includes jam override)
        $validShifts = $service->shiftValidUntukRuangan($this->jadwalKerja->ruangan_id);
        $this->shiftOptions = $validShifts->map(function ($rs) {
            $shift = $rs->shift;
            return [
                'id' => $shift->id,
                'kode' => $shift->kode,
                'nama' => $shift->nama,
                'warna' => $shift->warna ?? '#e2e8f0',
                'jam_masuk' => $rs->jam_masuk_efektif,
                'jam_keluar' => $rs->jam_keluar_efektif,
            ];
        })->toArray();

        $this->isReadOnly = $this->jadwalKerja->status === StatusJadwalKerja::LOCKED || !$canManage;

        // Populate dates for header
        $daysInMonth = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, 1)->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $this->dates[] = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, $d);
        }

        $this->syncDetails($daysInMonth);

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
                $day = $detail->tanggal->day;
                $row['details'][$day] = $detail;
                
                // Init state
                $this->state[$detail->id] = $detail->shift_id;
            }

            $this->karyawans[] = $row;
        }
    }

    private function syncDetails($daysInMonth)
    {
        $karyawansInRoom = \App\Models\Sdm\Karyawan::where('ruangan_id', $this->jadwalKerja->ruangan_id)
            ->whereNull('resign_at')
            ->get();
            
        $existingDetails = $this->jadwalKerja->details;
        
        // Build a fast lookup map of existing details (karyawan_id => array of dates)
        $existingMap = [];
        foreach ($existingDetails as $detail) {
            $dateStr = $detail->tanggal->format('Y-m-d');
            $existingMap[$detail->karyawan_id][$dateStr] = true;
        }

        $startDate = $this->dates[0]->format('Y-m-d');
        $endDate = $this->dates[$daysInMonth - 1]->format('Y-m-d');

        // Fetch approved cuti dates
        $approvedCutis = \App\Models\Surat\SuratCuti::where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tgl_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tgl_akhir', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->where('tgl_mulai', '<=', $startDate)
                          ->where('tgl_akhir', '>=', $endDate);
                      });
            })
            ->get();

        $cutiMap = [];
        foreach ($approvedCutis as $sc) {
            $dates = json_decode($sc->tgl_cuti, true);
            if (is_array($dates)) {
                foreach ($dates as $d) {
                    $cutiMap[$sc->karyawan_id][$d] = [
                        'status' => (int)$sc->urgensi_id === 4 ? \App\Enums\StatusKehadiran::IZIN : \App\Enums\StatusKehadiran::CUTI,
                        'catatan' => $sc->jenis?->nama . ' resmi (' . $sc->no_surat . ')'
                    ];
                }
            }
        }
        
        $detailsToInsert = [];
        
        foreach ($karyawansInRoom as $karyawan) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = $this->dates[$d - 1]->format('Y-m-d');
                $exists = isset($existingMap[$karyawan->id][$dateStr]);
                
                if (!$exists) {
                    $statusKehadiran = 'belum_dicek';
                    $catatan = null;

                    if (isset($cutiMap[$karyawan->id][$dateStr])) {
                        $statusKehadiran = $cutiMap[$karyawan->id][$dateStr]['status']->value;
                        $catatan = $cutiMap[$karyawan->id][$dateStr]['catatan'];
                    }

                    $detailsToInsert[] = [
                        'jadwal_kerja_id' => $this->jadwalKerja->id,
                        'karyawan_id' => $karyawan->id,
                        'shift_id' => null,
                        'tanggal' => $dateStr,
                        'status_kehadiran' => $statusKehadiran,
                        'catatan' => $catatan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        if (!empty($detailsToInsert)) {
            foreach (array_chunk($detailsToInsert, 500) as $chunk) {
                JadwalKerjaDetail::insert($chunk);
            }
            $this->jadwalKerja->load(['details.karyawan', 'details.shift']);
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
                $detail = JadwalKerjaDetail::find($detailId);
                
                // Force shift to null if they are on approved cuti
                $dateStr = $detail->tanggal->format('Y-m-d');
                $isCuti = isset($this->cutiDates["{$detail->karyawan_id}-{$dateStr}"]);
                if ($isCuti) {
                    $shiftId = null;
                } else {
                    // Konversi empty string/null/0 ke null
                    $shiftId = empty($shiftId) ? null : (int) $shiftId;
                }
                
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
