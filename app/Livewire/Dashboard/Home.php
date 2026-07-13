<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use App\Models\Master\Supplier;
use App\Models\Master\Barang;
use App\Models\Gudang\Pembelian;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratSp3;
use App\Models\Maintenance\Jadwal;
use App\Models\Assets\AssetBarang;
use App\Enums\StatusApproval;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

#[Title('Dashboard')]
#[Lazy]
class Home extends Component
{
    public array $stats = [];
    public array $rekapAbsen = [];
    public array $recentCuti = [];
    public array $recentPurchases = [];
    public array $recentMaintenance = [];
    public array $recentSp3 = [];
    public string $userRoleName = 'Guest';
    public string $userName = '';
    public $selectedBulan;
    public $selectedTahun;

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->userName = optional($user->karyawan)->nama ?? $user->email;
            $this->userRoleName = $user->roles->first()?->name ?? 'Guest';

            $this->selectedBulan = (int) now()->format('n');
            $this->selectedTahun = (int) now()->format('Y');

            // Load personal attendance if user is linked to a Karyawan
            if ($user->karyawan_id) {
                $this->loadEmployeeRekap($user->karyawan_id);
            }

            try {
                if ($user->hasRole('Super-Admin')) {
                    $this->loadSuperAdminData();
                } elseif ($user->hasRole('Staff-SDM')) {
                    $this->loadSdmData();
                } elseif ($user->hasRole('Bagian-Umum')) {
                    $this->loadUmumData();
                } elseif ($user->hasRole('Keuangan')) {
                    $this->loadKeuanganData();
                } elseif ($user->isKoordinator()) {
                    $this->loadKoordinatorData();
                } else {
                    $this->loadGuestData();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function loadSuperAdminData()
    {
        $this->stats = [
            'karyawan_count' => Karyawan::count(),
            'user_count' => User::count(),
            'barang_count' => Barang::count(),
            'supplier_count' => Supplier::count(),
            'pending_cuti' => SuratCuti::whereIn('status', [StatusApproval::PENDING, StatusApproval::WAITING])->count(),
            'maintenance_count' => Jadwal::count(),
            'asset_count' => AssetBarang::count(),
            'total_hutang' => Pembelian::whereIn('status_pembayaran', ['tempo', ''])->orWhereNull('status_pembayaran')->sum('total'),
        ];

        $this->recentCuti = SuratCuti::with('karyawan')->latest()->take(5)->get()->toArray();
        $this->recentPurchases = Pembelian::with('supplier')->latest()->take(5)->get()->toArray();
        $this->recentMaintenance = Jadwal::with('asset')->latest()->take(5)->get()->toArray();
    }

    private function loadSdmData()
    {
        $this->stats = [
            'karyawan_count' => Karyawan::count(),
            'pending_cuti' => SuratCuti::whereIn('status', [StatusApproval::PENDING, StatusApproval::WAITING])->count(),
            'sp3_count' => SuratSp3::count(),
        ];

        $this->recentCuti = SuratCuti::with('karyawan')->latest()->take(5)->get()->toArray();
        $this->recentSp3 = SuratSp3::with('penyetuju')->latest()->take(5)->get()->toArray();
    }

    private function loadUmumData()
    {
        $this->stats = [
            'barang_count' => Barang::count(),
            'supplier_count' => Supplier::count(),
            'asset_count' => AssetBarang::count(),
            'maintenance_count' => Jadwal::count(),
        ];

        $this->recentPurchases = Pembelian::with('supplier')->latest()->take(5)->get()->toArray();
        $this->recentMaintenance = Jadwal::with('asset')->latest()->take(5)->get()->toArray();
    }

    private function loadKeuanganData()
    {
        $this->stats = [
            'total_hutang' => Pembelian::whereIn('status_pembayaran', ['tempo', ''])->orWhereNull('status_pembayaran')->sum('total'),
            'total_pembayaran' => Pembelian::where('status_pembayaran', 'lunas')->sum('total'),
            'pembelian_count' => Pembelian::count(),
        ];

        $this->recentPurchases = Pembelian::with('supplier')->latest()->take(5)->get()->toArray();
    }

    private function loadKoordinatorData()
    {
        $ruanganIds = Auth::user()->getRuanganKoordinatorIds();

        if ($ruanganIds && count($ruanganIds) > 0) {
            $ruanganNames = \App\Models\Ruangan::whereIn('id', $ruanganIds)->pluck('nama')->toArray();
            $namaRuangan = implode(', ', $ruanganNames);
            
            $this->stats = [
                'ruangan_nama' => $namaRuangan,
                'karyawan_count' => Karyawan::whereIn('ruangan_id', $ruanganIds)->count(),
                'pending_cuti' => SuratCuti::whereIn('status', [StatusApproval::PENDING, StatusApproval::WAITING])
                    ->whereHas('karyawan', function($q) use ($ruanganIds) {
                        $q->whereIn('ruangan_id', $ruanganIds);
                    })->count(),
            ];

            $this->recentCuti = SuratCuti::with('karyawan')
                ->whereHas('karyawan', function($q) use ($ruanganIds) {
                    $q->whereIn('ruangan_id', $ruanganIds);
                })
                ->latest()
                ->take(5)
                ->get()
                ->toArray();
        } else {
            $this->stats = [
                'ruangan_nama' => 'Tidak ada ruangan',
                'karyawan_count' => 0,
                'pending_cuti' => 0,
            ];
            $this->recentCuti = [];
        }
    }

    public function updatedSelectedBulan()
    {
        $user = Auth::user();
        if ($user && $user->karyawan_id) {
            $this->loadEmployeeRekap($user->karyawan_id);
        }
    }

    public function updatedSelectedTahun()
    {
        $user = Auth::user();
        if ($user && $user->karyawan_id) {
            $this->loadEmployeeRekap($user->karyawan_id);
        }
    }

    private function loadEmployeeRekap($karyawanId)
    {
        $currentMonth = $this->selectedBulan;
        $currentYear = $this->selectedTahun;

        $details = \App\Models\Sdm\JadwalKerjaDetail::where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $currentMonth)
            ->whereYear('tanggal', $currentYear)
            ->get();

        $totalJadwal = $details->count();
        $totalHadir = 0;
        $totalTerlambat = 0;
        $menitTerlambat = 0;
        $totalPulangCepat = 0;
        $menitPulangCepat = 0;
        $menitLembur = 0;
        $totalCutiIzin = 0;
        $totalTidakHadir = 0;
        $totalBelumDicek = 0;

        foreach ($details as $d) {
            $status = $d->status_kehadiran instanceof \App\Enums\StatusKehadiran 
                ? $d->status_kehadiran 
                : \App\Enums\StatusKehadiran::tryFrom($d->status_kehadiran);

            // Hitung Lembur (Overtime) per menit jika shift dan absen_keluar_at terisi
            if ($d->shift && $d->absen_keluar_at) {
                $jamKeluar = $d->shift->jam_keluar;
                $tglKeluar = Carbon::parse($d->tanggal);
                if ($d->shift->lintas_hari) {
                    $tglKeluar->addDay();
                }
                $scheduledOut = Carbon::parse($tglKeluar->format('Y-m-d') . ' ' . $jamKeluar);
                $actualOut = Carbon::parse($d->absen_keluar_at);

                if ($actualOut->greaterThan($scheduledOut)) {
                    $menitLembur += abs($actualOut->diffInMinutes($scheduledOut));
                }
            }

            if (!$status) {
                $totalBelumDicek++;
                continue;
            }

            switch ($status) {
                case \App\Enums\StatusKehadiran::HADIR:
                    $totalHadir++;
                    break;
                case \App\Enums\StatusKehadiran::TERLAMBAT:
                    $totalTerlambat++;
                    if (preg_match('/Terlambat (-?\d+) menit/i', $d->catatan, $matches)) {
                        $menitTerlambat += abs((int) $matches[1]);
                    }
                    break;
                case \App\Enums\StatusKehadiran::PULANG_CEPAT:
                    $totalPulangCepat++;
                    if (preg_match('/Pulang cepat (-?\d+) menit/i', $d->catatan, $matches)) {
                        $menitPulangCepat += abs((int) $matches[1]);
                    }
                    break;
                case \App\Enums\StatusKehadiran::CUTI:
                case \App\Enums\StatusKehadiran::IZIN:
                    $totalCutiIzin++;
                    break;
                case \App\Enums\StatusKehadiran::TIDAK_HADIR:
                    $totalTidakHadir++;
                    break;
                default:
                    $totalBelumDicek++;
                    break;
            }
        }

        $totalSudahLewat = $totalHadir + $totalTerlambat + $totalPulangCepat + $totalCutiIzin + $totalTidakHadir;
        $persenKehadiran = $totalSudahLewat > 0 
            ? round((($totalHadir + $totalTerlambat + $totalPulangCepat) / $totalSudahLewat) * 100) 
            : 100;

        // Mendapatkan nama bulan lokalisasi Indonesia
        $dateObj = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
        $bulanNama = $dateObj->translatedFormat('F Y');

        $this->rekapAbsen = [
            'bulan_nama' => $bulanNama,
            'total_jadwal' => $totalJadwal,
            'hadir' => $totalHadir,
            'terlambat' => $totalTerlambat,
            'menit_terlambat' => $menitTerlambat,
            'pulang_cepat' => $totalPulangCepat,
            'menit_pulang_cepat' => $menitPulangCepat,
            'menit_lembur' => $menitLembur,
            'cuti_izin' => $totalCutiIzin,
            'tidak_hadir' => $totalTidakHadir,
            'belum_dicek' => $totalBelumDicek,
            'persen_kehadiran' => $persenKehadiran,
        ];
    }

    private function loadGuestData()
    {
        $this->stats = [];
    }

    public function render()
    {
        return view('livewire.dashboard.home');
    }
}
