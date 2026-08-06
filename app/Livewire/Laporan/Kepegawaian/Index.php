<?php

namespace App\Livewire\Laporan\Kepegawaian;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Bagian;
use App\Models\Surat\SuratCuti;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKepegawaianExport;
use Illuminate\Support\Facades\DB;

#[Title('Laporan Kepegawaian')]
class Index extends Component
{
    use AuthorizesFromRoute;
    use WithPagination;

    #[Url(history: true)]
    public string $activeTab = 'overview'; // 'overview', 'detail', 'bagian', 'kehadiran'

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $selectedBagian = '';

    #[Url(history: true)]
    public string $selectedStatus = '';

    #[Url(history: true)]
    public string $selectedJk = '';

    #[Url(history: true)]
    public string $selectedPendidikan = '';

    public int $perPage = 15;

    public array $stats = [];
    public array $absensiStats = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadStatsAbsensi();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedBagian()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSelectedJk()
    {
        $this->resetPage();
    }

    public function updatingSelectedPendidikan()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedBagian', 'selectedStatus', 'selectedJk', 'selectedPendidikan']);
        $this->resetPage();
    }

    private function getFilteredKaryawanQuery()
    {
        $query = Karyawan::with(['latestJabatan.jabatan.bagian', 'ruangan']);

        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if (!empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        if (!empty($this->selectedJk)) {
            $query->where('jk', $this->selectedJk);
        }

        if (!empty($this->selectedBagian)) {
            $bagianId = $this->selectedBagian;
            $query->whereHas('latestJabatan.jabatan', function ($q) use ($bagianId) {
                $q->where('bagian_id', $bagianId);
            });
        }

        if (!empty($this->selectedPendidikan)) {
            $pend = $this->selectedPendidikan;
            $query->whereExists(function ($sub) use ($pend) {
                $sub->select(DB::raw(1))
                    ->from('sdm_kary_pendidikan')
                    ->whereColumn('sdm_kary_pendidikan.karyawan_id', 'sdm_karyawan.id')
                    ->where('sdm_kary_pendidikan.tingkat', $pend);
            });
        }

        return $query;
    }

    public function getKaryawansProperty()
    {
        return $this->getFilteredKaryawanQuery()
            ->orderBy('nama')
            ->paginate($this->perPage);
    }

    public function getBagianListProperty()
    {
        return Bagian::orderBy('nama')->get();
    }

    public function getBagianBreakdownProperty()
    {
        $bagians = Bagian::get();
        $totalKaryawan = max(1, Karyawan::count());

        return $bagians->map(function ($bagian) use ($totalKaryawan) {
            $karyawans = Karyawan::whereHas('latestJabatan.jabatan', function ($q) use ($bagian) {
                $q->where('bagian_id', $bagian->id);
            })->get();

            $total = $karyawans->count();
            $male = $karyawans->where('jk', 'L')->count();
            $female = $karyawans->where('jk', 'P')->count();
            $tetap = $karyawans->filter(fn($k) => (is_object($k->status) ? $k->status->value : $k->status) === 'tetap')->count();
            $kontrak = $karyawans->filter(fn($k) => (is_object($k->status) ? $k->status->value : $k->status) === 'kontrak')->count();
            $lainnya = $total - ($tetap + $kontrak);

            return [
                'id' => $bagian->id,
                'nama' => $bagian->nama,
                'total' => $total,
                'male' => $male,
                'female' => $female,
                'tetap' => $tetap,
                'kontrak' => $kontrak,
                'lainnya' => $lainnya,
                'percentage' => round(($total / $totalKaryawan) * 100, 1),
            ];
        })->sortByDesc('total')->values();
    }

    public function getKehadiranHariIniListProperty()
    {
        $today = date('Y-m-d');
        return JadwalKerjaDetail::with(['jadwalKerja.karyawan.latestJabatan.jabatan', 'shift'])
            ->where('tanggal', $today)
            ->when(!empty($this->search), function ($q) {
                $search = trim($this->search);
                $q->whereHas('jadwalKerja.karyawan', function ($kq) use ($search) {
                    $kq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->paginate($this->perPage);
    }

    private function loadStats()
    {
        $totalKaryawan = Karyawan::count();
        $male = Karyawan::where('jk', 'L')->count();
        $female = Karyawan::where('jk', 'P')->count();

        $statusCounts = Karyawan::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [is_object($item->status) ? $item->status->value : $item->status => $item->count])
            ->toArray();

        $educationCounts = DB::table('sdm_kary_pendidikan')
            ->selectRaw('tingkat, count(*) as count')
            ->groupBy('tingkat')
            ->pluck('count', 'tingkat')
            ->toArray();

        $bagianStats = Bagian::withCount(['jabatans as karyawan_count' => function ($q) {
            $q->join('sdm_kary_jabatan', 'sdm_jabatan.id', '=', 'sdm_kary_jabatan.jabatan_id');
        }])->get();

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

    public function exportExcel()
    {
        $query = $this->getFilteredKaryawanQuery();
        $filename = 'Laporan_Kepegawaian_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new LaporanKepegawaianExport($query), $filename);
    }

    public function exportCsv()
    {
        $records = $this->getFilteredKaryawanQuery()->get();
        $filename = 'Laporan_Kepegawaian_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'NIP',
                'NIK',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Agama',
                'Status Kepegawaian',
                'Bagian / Unit',
                'Jabatan Saat Ini',
                'Masa Kerja',
                'Usia',
                'Tanggal Masuk',
                'No HP',
                'Alamat Domisili',
            ]);

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->nip,
                    $row->nik,
                    $row->full_nama,
                    $row->jk === 'L' ? 'Laki-laki' : 'Perempuan',
                    ucfirst($row->agama ?? '-'),
                    is_object($row->status) ? $row->status->nama() : ucfirst($row->status ?? '-'),
                    $row->latestJabatan?->jabatan?->bagian?->nama ?? '-',
                    $row->latestJabatan?->jabatan?->nama ?? '-',
                    $row->masakerja ?? '-',
                    $row->usia ?? '-',
                    $row->tgl_masuk ?? '-',
                    $row->hp ?? '-',
                    $row->dom_alamat ?? $row->alamat ?? '-',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportBagianCsv()
    {
        $breakdown = $this->getBagianBreakdownProperty();
        $filename = 'Laporan_Rekap_Bagian_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($breakdown) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Nama Bagian / Departemen',
                'Total Karyawan',
                'Laki-laki',
                'Perempuan',
                'Status Tetap',
                'Status Kontrak',
                'Status Lainnya',
                'Persentase Dari Total (%)',
            ]);

            foreach ($breakdown as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row['nama'],
                    $row['total'],
                    $row['male'],
                    $row['female'],
                    $row['tetap'],
                    $row['kontrak'],
                    $row['lainnya'],
                    $row['percentage'] . '%',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
