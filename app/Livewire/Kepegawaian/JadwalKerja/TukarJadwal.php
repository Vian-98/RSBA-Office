<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Enums\StatusTukarJadwal;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\TukarJadwalDokter;
use App\Services\TukarJadwalDokterService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Tukar Jadwal Dokter - RSBA Office')]
class TukarJadwal extends Component
{
    use WithPagination;

    public string $activeTab = 'pengajuan'; // pengajuan, konfirmasi, wadir, riwayat

    // Form attributes
    public ?int $dokterPengajuId = null;
    public ?int $jadwalDetailPengajuId = null;
    public ?int $dokterPenggantiId = null;
    public ?int $jadwalDetailPenggantiId = null;
    public ?string $alasan = '';

    // Modal action attributes
    public ?int $selectedTukarId = null;
    public string $confirmAction = ''; // setuju_dokter, tolak_dokter, setuju_wadir, tolak_wadir
    public ?string $catatanWadir = '';
    public bool $showConfirmModal = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->karyawan_id) {
            $this->dokterPengajuId = $user->karyawan_id;
        }

        // Auto tab selection based on role / pending items
        if ($user->hasRole('Wakil-Direktur') || $user->hasPermissionTo('approve-jadwal-wadir')) {
            $this->activeTab = 'wadir';
        }
    }

    public function updatedDokterPengajuId()
    {
        $this->jadwalDetailPengajuId = null;
    }

    public function updatedDokterPenggantiId()
    {
        $this->jadwalDetailPenggantiId = null;
    }

    public function submitPengajuan(TukarJadwalDokterService $service)
    {
        $this->validate([
            'dokterPengajuId'          => 'required|exists:sdm_karyawan,id',
            'jadwalDetailPengajuId'   => 'required|exists:sdm_jadwal_kerja_detail,id',
            'dokterPenggantiId'        => 'required|exists:sdm_karyawan,id|different:dokterPengajuId',
            'jadwalDetailPenggantiId' => 'required|exists:sdm_jadwal_kerja_detail,id',
            'alasan'                   => 'required|string|max:500',
        ], [
            'dokterPenggantiId.different' => 'Dokter pengganti harus berbeda dari dokter pengaju.',
        ]);

        try {
            $dokterA = Karyawan::findOrFail($this->dokterPengajuId);
            $dokterB = Karyawan::findOrFail($this->dokterPenggantiId);

            $service->ajukanTukar(
                $dokterA,
                $this->jadwalDetailPengajuId,
                $dokterB,
                $this->jadwalDetailPenggantiId,
                $this->alasan
            );

            session()->flash('message', 'Pengajuan tukar jadwal berhasil dibuat dan menunggu konfirmasi Dokter B.');
            $this->reset(['jadwalDetailPengajuId', 'dokterPenggantiId', 'jadwalDetailPenggantiId', 'alasan']);
            $this->activeTab = 'riwayat';
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openConfirmModal(int $id, string $action)
    {
        $this->selectedTukarId = $id;
        $this->confirmAction   = $action;
        $this->catatanWadir    = '';
        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->selectedTukarId  = null;
        $this->confirmAction    = '';
        $this->catatanWadir     = '';
    }

    public function processConfirm(TukarJadwalDokterService $service)
    {
        if (!$this->selectedTukarId) return;

        $tukar = TukarJadwalDokter::findOrFail($this->selectedTukarId);

        try {
            switch ($this->confirmAction) {
                case 'setuju_dokter':
                    $service->konfirmasiDokter($tukar, true);
                    session()->flash('message', 'Pengajuan tukar jadwal telah Anda setujui dan diteruskan ke Wadir.');
                    break;

                case 'tolak_dokter':
                    $service->konfirmasiDokter($tukar, false);
                    session()->flash('message', 'Pengajuan tukar jadwal telah Anda tolak.');
                    break;

                case 'setuju_wadir':
                    $service->approveWadir($tukar, true, Auth::user(), $this->catatanWadir);
                    session()->flash('message', 'Pengajuan tukar jadwal disetujui Wadir. Shift kedua dokter telah otomatis bertukar.');
                    break;

                case 'tolak_wadir':
                    $service->approveWadir($tukar, false, Auth::user(), $this->catatanWadir);
                    session()->flash('message', 'Pengajuan tukar jadwal ditolak Wadir.');
                    break;
            }

            $this->closeConfirmModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $myKaryawanId = $user?->karyawan_id;

        // List dokter
        $dokters = Karyawan::query()
            ->where(function ($q) {
                $q->whereHas('user', function ($u) {
                    $u->whereHas('roles', fn($r) => $r->where('name', 'like', '%Dokter%'));
                })
                ->orWhere('gelar_depan', 'like', '%dr%')
                ->orWhere('gelar_belakang', 'like', '%Sp%');
            })
            ->orderBy('nama')
            ->get();

        if ($dokters->isEmpty()) {
            $dokters = Karyawan::orderBy('nama')->get();
        }

        // List jadwal detail untuk dropdown
        $jadwalPengaju = $this->dokterPengajuId
            ? JadwalKerjaDetail::where('karyawan_id', $this->dokterPengajuId)
                ->with('shift', 'jadwalKerja')
                ->orderBy('tanggal', 'desc')
                ->limit(30)
                ->get()
            : collect();

        $jadwalPengganti = $this->dokterPenggantiId
            ? JadwalKerjaDetail::where('karyawan_id', $this->dokterPenggantiId)
                ->with('shift', 'jadwalKerja')
                ->orderBy('tanggal', 'desc')
                ->limit(30)
                ->get()
            : collect();

        // Data list per tab
        $listKonfirmasiSaya = TukarJadwalDokter::query()
            ->when($myKaryawanId, fn($q) => $q->where('dokter_pengganti_id', $myKaryawanId))
            ->where('status', StatusTukarJadwal::MENUNGGU_KONFIRMASI_DOKTER)
            ->with(['dokterPengaju', 'dokterPengganti', 'jadwalDetailPengaju.shift', 'jadwalDetailPengganti.shift'])
            ->latest()
            ->get();

        $listAntreanWadir = TukarJadwalDokter::query()
            ->where('status', StatusTukarJadwal::MENUNGGU_WADIR)
            ->with(['dokterPengaju', 'dokterPengganti', 'jadwalDetailPengaju.shift', 'jadwalDetailPengganti.shift'])
            ->latest()
            ->get();

        $listRiwayat = TukarJadwalDokter::query()
            ->with(['dokterPengaju', 'dokterPengganti', 'jadwalDetailPengaju.shift', 'jadwalDetailPengganti.shift', 'disetujuiOleh'])
            ->latest()
            ->paginate(15);

        return view('livewire.kepegawaian.jadwal-kerja.tukar-jadwal', [
            'dokters'            => $dokters,
            'jadwalPengaju'      => $jadwalPengaju,
            'jadwalPengganti'    => $jadwalPengganti,
            'listKonfirmasiSaya' => $listKonfirmasiSaya,
            'listAntreanWadir'   => $listAntreanWadir,
            'listRiwayat'        => $listRiwayat,
        ]);
    }
}
