<?php

namespace App\Livewire\Laporan\Kepegawaian;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Bagian;
use App\Models\Surat\SuratCuti;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Kepegawaian')]
class Index extends Component
{
    use AuthorizesFromRoute;

    public array $stats = [];
    public array $absensiStats = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadStatsAbsensi();
    }

    private function loadStats()
    {
        // 1. Total Karyawan
        $totalKaryawan = Karyawan::count();

        // 2. Gender distribution
        $male = Karyawan::where('jk', 'L')->count();
        $female = Karyawan::where('jk', 'P')->count();

        // 3. Status Kerja distribution
        $statusCounts = Karyawan::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count])
            ->toArray();

        // 4. Pendidikan distribution
        $educationCounts = \Illuminate\Support\Facades\DB::table('sdm_kary_pendidikan')
            ->selectRaw('tingkat, count(*) as count')
            ->groupBy('tingkat')
            ->pluck('count', 'tingkat')
            ->toArray();

        // 5. Bagian distribution
        $bagianStats = Bagian::withCount(['jabatans as karyawan_count' => function ($q) {
            $q->join('sdm_kary_jabatan', 'sdm_jabatan.id', '=', 'sdm_kary_jabatan.jabatan_id');
        }])->get();

        // 6. Active Leaves (Cuti)
        $activeCuti = SuratCuti::where('status', 'approved')
            ->whereDate('tgl_mulai', '<=', now())
            ->whereDate('tgl_akhir', '>=', now())
            ->count();

        $this->stats = [
            'total_karyawan' => $totalKaryawan,
            'male' => $male,
            'female' => $female,
            'status_tetap' => $statusCounts['tetap'] ?? 0,
            'status_kontrak' => $statusCounts['kontrak'] ?? 0,
            'status_magang' => $statusCounts['magang'] ?? 0,
            'status_mitra' => $statusCounts['mitra'] ?? 0,
            'status_bantuan' => $statusCounts['bantuan'] ?? 0,
            'active_cuti' => $activeCuti,
            'education' => $educationCounts,
            'bagian' => $bagianStats,
        ];
    }

    private function loadStatsAbsensi()
    {
        // Distribusi status kehadiran hari ini
        $today = date('Y-m-d');
        $kehadiranHariIni = JadwalKerjaDetail::where('tanggal', $today)
            ->selectRaw('status_kehadiran, count(*) as count')
            ->groupBy('status_kehadiran')
            ->pluck('count', 'status_kehadiran')
            ->toArray();
            
        $this->absensiStats = [
            'hadir' => $kehadiranHariIni['hadir'] ?? 0,
            'terlambat' => $kehadiranHariIni['terlambat'] ?? 0,
            'pulang_cepat' => $kehadiranHariIni['pulang_cepat'] ?? 0,
            'tidak_hadir' => $kehadiranHariIni['tidak_hadir'] ?? 0,
            'cuti' => $kehadiranHariIni['cuti'] ?? 0,
            'izin' => $kehadiranHariIni['izin'] ?? 0,
            'perlu_verifikasi' => $kehadiranHariIni['perlu_verifikasi'] ?? 0,
        ];
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.laporan.kepegawaian.index');
    }
}
