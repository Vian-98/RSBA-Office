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

    public array $doktersList = [];
    public array $jadwalPengajuList = [];
    public array $jadwalPenggantiList = [];

    // Modal action attributes
    public ?int $selectedTukarId = null;
    public string $confirmAction = ''; // setuju_dokter, tolak_dokter, setuju_wadir, tolak_wadir
    public ?string $catatanWadir = '';
    public bool $showConfirmModal = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->isDokterOrApprover()) {
            abort(403, 'Akses ditolak. Halaman Tukar Shift Dokter hanya dapat diakses oleh Dokter atau Manajemen.');
        }

        if ($user->karyawan_id) {
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
        $user = Auth::user();
        if (!$user || !$user->isDokterOrApprover()) {
            abort(403, 'Akses ditolak. Halaman Tukar Shift Dokter hanya dapat diakses oleh Dokter atau Manajemen Medis/SDM.');
        }

        $canSelectDokterA = $user?->hasRole(['Super-Admin', 'Staff-SDM', 'Wakil-Direktur', 'Wadir-Medis-Keperawatan', 'Wadir-SDM-Umum', 'Koordinator-Dokter']);

        // Jika dokter biasa, kunci pengaju ke dirinya sendiri
        if (!$canSelectDokterA && $user?->karyawan_id) {
            $this->dokterPengajuId = $user->karyawan_id;
        }

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
        $user  = Auth::user();

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
                    if (!$user?->hasRole(['Wakil-Direktur', 'Super-Admin']) && !$user?->can('approve-jadwal-wadir')) {
                        abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk melakukan approval Wadir.');
                    }
                    $service->approveWadir($tukar, true, $user, $this->catatanWadir);
                    session()->flash('message', 'Pengajuan tukar jadwal disetujui Wadir. Shift kedua dokter telah otomatis bertukar.');
                    break;

                case 'tolak_wadir':
                    if (!$user?->hasRole(['Wakil-Direktur', 'Super-Admin']) && !$user?->can('approve-jadwal-wadir')) {
                        abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk melakukan approval Wadir.');
                    }
                    $service->approveWadir($tukar, false, $user, $this->catatanWadir);
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

        // List dokter terkelompok per Ruangan/Poli
        $doktersQuery = Karyawan::query()
            ->where(function ($q) {
                $q->whereHas('dokterRecord')
                  ->orWhereHas('user', function ($u) {
                      $u->whereHas('roles', fn($r) => $r->where('name', 'like', '%Dokter%'));
                  })
                  ->orWhere('gelar_depan', 'like', '%dr%')
                  ->orWhere('gelar_belakang', 'like', '%Sp%');
            })
            ->with('ruangan')
            ->orderBy('nama')
            ->get();

        if ($doktersQuery->isEmpty()) {
            $doktersQuery = Karyawan::with('ruangan')->orderBy('nama')->get();
        }

        $dokters = $doktersQuery->groupBy(function ($k) {
            return $k->ruangan->nama ?? 'Dokter Lainnya';
        });

        $this->doktersList = $doktersQuery->map(function ($d) {
            return [
                'id'      => $d->id,
                'nama'    => $d->full_nama,
                'nip'     => $d->nip,
                'ruangan' => $d->ruangan->nama ?? 'Dokter Lainnya',
            ];
        })->values()->toArray();

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

        $this->jadwalPengajuList = $jadwalPengaju->map(function ($j) {
            $tgl = \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d M Y');
            $shiftNama = $j->shift?->nama ?? 'Libur';
            $jam = ($j->shift?->jam_masuk ?? '-') . ' - ' . ($j->shift?->jam_keluar ?? '-');
            return [
                'id'      => $j->id,
                'label'   => "{$tgl} - {$shiftNama} ({$jam})",
                'tanggal' => $tgl,
                'shift'   => $shiftNama,
                'jam'     => $jam,
            ];
        })->values()->toArray();

        $this->jadwalPenggantiList = $jadwalPengganti->map(function ($j) {
            $tgl = \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d M Y');
            $shiftNama = $j->shift?->nama ?? 'Libur';
            $jam = ($j->shift?->jam_masuk ?? '-') . ' - ' . ($j->shift?->jam_keluar ?? '-');
            return [
                'id'      => $j->id,
                'label'   => "{$tgl} - {$shiftNama} ({$jam})",
                'tanggal' => $tgl,
                'shift'   => $shiftNama,
                'jam'     => $jam,
            ];
        })->values()->toArray();

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
            ->with(['dokterPengaju', 'dokterPengganti', 'jadwalDetailPengaju.shift', 'jadwalDetailPengganti.shift', 'disetujuiOleh.karyawan'])
            ->latest()
            ->paginate(15);

        $isWadir          = $user?->hasRole(['Wakil-Direktur', 'Super-Admin']) || $user?->can('approve-jadwal-wadir');
        $canSelectDokterA = $user?->hasRole(['Super-Admin', 'Staff-SDM', 'Wakil-Direktur', 'Kepala-Bidang']);

        return view('livewire.kepegawaian.jadwal-kerja.tukar-jadwal', [
            'dokters'              => $dokters,
            'doktersList'          => $this->doktersList,
            'jadwalPengaju'        => $jadwalPengaju,
            'jadwalPengganti'      => $jadwalPengganti,
            'jadwalPengajuList'    => $this->jadwalPengajuList,
            'jadwalPenggantiList'  => $this->jadwalPenggantiList,
            'listKonfirmasiSaya'   => $listKonfirmasiSaya,
            'listAntreanWadir'     => $listAntreanWadir,
            'listRiwayat'          => $listRiwayat,
            'isWadir'              => $isWadir,
            'canSelectDokterA'     => $canSelectDokterA,
        ]);
    }
}
