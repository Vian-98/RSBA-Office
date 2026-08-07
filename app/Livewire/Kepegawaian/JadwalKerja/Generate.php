<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Enums\KategoriKerja;
use App\Enums\StatusKaryawan;
use App\Services\AturanJadwalService;
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
    public $bagian_id;
    public $bulan;
    public $tahun;

    public function rules()
    {
        return [
            'ruangan_id' => 'required|exists:ruangan,id',
            'bagian_id' => 'nullable|exists:bagian,id',
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

    public function updatedRuanganId(): void
    {
        $this->bagian_id = null;
    }

    public function submit(AturanJadwalService $aturanJadwalService)
    {
        $this->validate();

        $this->authorize('generate', JadwalKerja::class);

        $karyawanId = Auth::user()->karyawan_id;

        $user = Auth::user();
        $isKoorDokter = $user?->isKoordinatorDokter() ?? false;
        $isKoorKaryawan = $user?->isKoordinatorKaryawan() ?? false;
        $tipeJadwal = $isKoorDokter ? 'dokter' : 'karyawan';

        // Cek apakah jadwal sudah ada
        $exists = JadwalKerja::where('ruangan_id', $this->ruangan_id)
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->where('tipe', $tipeJadwal)
            ->exists();

        if ($exists) {
            $this->toast()->error('Gagal', 'Jadwal kerja untuk ruangan, periode, dan kelompok ini sudah pernah dibuat.')->send();
            return;
        }

        $karyawansQuery = Karyawan::where('ruangan_id', $this->ruangan_id)
            ->whereNull('resign_at');

        if ($isKoorDokter) {
            $karyawansQuery->whereHas('dokterRecord');
        } elseif ($isKoorKaryawan) {
            $karyawansQuery->whereDoesntHave('dokterRecord');
        }

        $karyawans = $karyawansQuery->get();

        $resolvedBagianId = JadwalKerja::resolveBagianIdForKaryawanIds(
            $karyawans->pluck('id'),
            (int) $this->ruangan_id
        );

        if ($this->bagian_id) {
            $candidateBagianIds = $karyawans->map(fn ($karyawan) => $karyawan->active_bagian_id)->filter()->unique();
            if ($candidateBagianIds->isNotEmpty() && !$candidateBagianIds->contains((int) $this->bagian_id)) {
                $this->toast()->error('Gagal', 'Bagian yang dipilih tidak sesuai dengan penugasan aktif pegawai di ruangan ini.')->send();
                return;
            }
            $resolvedBagianId = (int) $this->bagian_id;
        }

        $candidateBagianIds = $karyawans->map(fn ($karyawan) => $karyawan->active_bagian_id)->filter()->unique();
        if ($candidateBagianIds->count() > 1 && !$this->bagian_id) {
            $this->toast()->error('Gagal', 'Pegawai di ruangan ini berasal dari beberapa bagian. Pilih Bagian Jadwal terlebih dahulu.')->send();
            return;
        }

        if (!$resolvedBagianId) {
            $this->toast()->error('Gagal', 'Jadwal belum memiliki Bagian. Lengkapi penugasan pegawai atau mapping legacy ruangan terlebih dahulu.')->send();
            return;
        }

        $hasReguler = $karyawans->contains(function ($k) {
            return $k->kategori_kerja === KategoriKerja::REGULER;
        });

        $shiftReguler = null;
        if ($hasReguler) {
            // REGULER harus termasuk shift yang berlaku untuk Bagian jadwal.
            $shiftReguler = $aturanJadwalService
                ->shiftValidUntukRuangan((int) $this->ruangan_id, $resolvedBagianId)
                ->first(fn ($ruanganShift) => $ruanganShift->shift?->kode === 'REGULER')
                ?->shift;

            if (!$shiftReguler) {
                $this->toast()->error('Gagal', 'Shift REGULER belum diizinkan untuk Bagian pada jadwal ini. Atur Bagian pada Master Shift REGULER terlebih dahulu.')->send();
                return;
            }
        }

        try {
            DB::beginTransaction();

            $jadwalKerja = JadwalKerja::create([
                'ruangan_id' => $this->ruangan_id,
                'bagian_id' => $resolvedBagianId,
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'tipe' => $tipeJadwal,
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
        
        if ($user && !$user->can('add-kepegawaian-jadwal-kerja') && !$user->can('edit-kepegawaian-jadwal-kerja')) {
            $ruanganIds = $user->getRuanganKoordinatorIds() ?? [];
            if ($user->karyawan?->ruangan_id) {
                $ruanganIds[] = $user->karyawan->ruangan_id;
            }
            $ruanganQuery->whereIn('id', array_unique($ruanganIds));
        }

        $ruanganOptions = $ruanganQuery->select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray();

        $bagianOptions = Bagian::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->map(fn ($item) => ['value' => $item->id, 'label' => $item->nama])
            ->toArray();

        $requiresBagianSelection = false;
        if ($this->ruangan_id) {
            $previewKaryawans = Karyawan::with('jabatan')
                ->where('ruangan_id', $this->ruangan_id)
                ->whereNull('resign_at')
                ->get();

            $requiresBagianSelection = $previewKaryawans
                ->map(fn ($karyawan) => $karyawan->active_bagian_id)
                ->filter()
                ->unique()
                ->count() > 1;
        }

        return view('livewire.kepegawaian.jadwal-kerja.generate', [
            'ruanganOptions' => $ruanganOptions,
            'bagianOptions' => $bagianOptions,
            'requiresBagianSelection' => $requiresBagianSelection,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
        ]);
    }
}
