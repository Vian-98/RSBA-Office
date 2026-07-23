<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Enums\KategoriKerja;
use App\Enums\StatusKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;
use TallStackUi\Traits\Interactions;

class Generate extends Component
{
    use Interactions;

    public $ruangan_id;
    public $bulan;
    public $tahun;

    public function rules()
    {
        return [
            'ruangan_id' => 'required|exists:ruangan,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2024|max:2099',
        ];
    }

    public function mount()
    {
        $this->authorize('generate', JadwalKerja::class);
        $this->bulan = date('n');
        $this->tahun = date('Y');
    }

    public function submit()
    {
        $this->validate();

        $this->authorize('generate', JadwalKerja::class);

        $karyawanId = Auth::user()->karyawan_id;

        // Cek apakah jadwal sudah ada
        $exists = JadwalKerja::where('ruangan_id', $this->ruangan_id)
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->exists();

        if ($exists) {
            $this->toast()->error('Gagal', 'Jadwal kerja untuk ruangan dan periode tersebut sudah pernah dibuat.')->send();
            return;
        }

        $karyawans = Karyawan::where('ruangan_id', $this->ruangan_id)
            ->whereNull('resign_at')->get();

        $hasReguler = $karyawans->contains(function ($k) {
            return $k->kategori_kerja === KategoriKerja::REGULER;
        });

        $shiftReguler = null;
        if ($hasReguler) {
            // Cek apakah ada shift REGULER
            $shiftReguler = JadwalShift::where('kode', 'REGULER')->where('aktif', true)->first();
            if (!$shiftReguler) {
                $this->toast()->error('Gagal', 'Ruangan ini memiliki pegawai reguler, namun Master Shift dengan kode REGULER belum dibuat atau tidak aktif.')->send();
                return;
            }
        }

        try {
            DB::beginTransaction();

            $jadwalKerja = JadwalKerja::create([
                'ruangan_id' => $this->ruangan_id,
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'status' => 'draft',
                'dibuat_oleh' => $karyawanId,
            ]);

            $daysInMonth = Carbon::create($this->tahun, $this->bulan, 1)->daysInMonth;
            $startDate = Carbon::create($this->tahun, $this->bulan, 1)->format('Y-m-d');
            $endDate = Carbon::create($this->tahun, $this->bulan, $daysInMonth)->format('Y-m-d');

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
            
            $details = [];
            foreach ($karyawans as $karyawan) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = Carbon::create($this->tahun, $this->bulan, $d);
                    $dateStr = $date->format('Y-m-d');
                    
                    $shiftId = null;
                    if ($karyawan->kategori_kerja === KategoriKerja::REGULER && $shiftReguler) {
                        // Senin - Jumat (1 - 5)
                        if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                            $shiftId = $shiftReguler->id;
                        }
                    }

                    $statusKehadiran = 'belum_dicek';
                    $catatan = null;
                    $actualShiftId = $shiftId;

                    if (isset($cutiMap[$karyawan->id][$dateStr])) {
                        $statusKehadiran = $cutiMap[$karyawan->id][$dateStr]['status']->value;
                        $catatan = $cutiMap[$karyawan->id][$dateStr]['catatan'];
                        $actualShiftId = null; // No shift on leave days
                    }

                    $details[] = [
                        'jadwal_kerja_id' => $jadwalKerja->id,
                        'karyawan_id' => $karyawan->id,
                        'shift_id' => $actualShiftId,
                        'tanggal' => $dateStr,
                        'status_kehadiran' => $statusKehadiran,
                        'catatan' => $catatan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert
            foreach (array_chunk($details, 500) as $chunk) {
                JadwalKerjaDetail::insert($chunk);
            }

            DB::commit();

            $this->dispatch('jadwal-kerja-generated');
            $this->dispatch('close-modal', id: 'generate-jadwal-kerja');

            $this->toast()->success('Berhasil', 'Draf Jadwal Kerja berhasil di-generate.')->send();
            
            return redirect()->route('kepegawaian.jadwal-kerja.kelola', ['id' => $jadwalKerja->id]);

        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $bulanOptions = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => date('F', mktime(0, 0, 0, $m, 1))
        ])->toArray();

        $tahunOptions = collect(range(date('Y'), date('Y') + 2))->map(fn($y) => [
            'value' => $y,
            'label' => (string) $y
        ])->toArray();

        $user = Auth::user();
        $ruanganQuery = \App\Models\Ruangan::where('is_active', true);
        
        if ($user && !$user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            $ruanganId = $user->karyawan->ruangan_id ?? 0;
            $ruanganQuery->where('id', $ruanganId);
        }

        $ruanganOptions = $ruanganQuery->select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray();

        return view('livewire.kepegawaian.jadwal-kerja.generate', [
            'ruanganOptions' => $ruanganOptions,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
        ]);
    }
}
