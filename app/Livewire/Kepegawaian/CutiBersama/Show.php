<?php

namespace App\Livewire\Kepegawaian\CutiBersama;

use App\Models\Sdm\CutiBersama;
use App\Services\BatalkanCutiBersamaService;
use App\Services\SimulasiCutiBersamaService;
use App\Services\TerapkanCutiBersamaService;
use App\Traits\AuthorizesFromRoute;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Title('Detail & Simulasi Cuti Bersama')]
class Show extends Component
{
    use AuthorizesFromRoute;
    use Interactions;
    use WithPagination;

    public int $id;
    public ?array $simulasiData = null;
    public $searchPegawai = '';
    public $filterKategori = 'semua'; // semua, potong, piket, roster, belum_ada
    public $filterPartisipasi = 'semua'; // semua, ikut, dikecualikan
    public int $perPage = 15;

    public $searchProyeksi = '';
    public $filterProyeksi = 'semua'; // semua, defisit, aman
    public int $perPageProyeksi = 15;

    public function updatedSearchPegawai()
    {
        $this->resetPage();
    }

    public function updatedFilterKategori()
    {
        $this->resetPage();
    }

    public function updatedFilterPartisipasi()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearchProyeksi()
    {
        $this->resetPage('proyeksiPage');
    }

    public function updatedFilterProyeksi()
    {
        $this->resetPage('proyeksiPage');
    }

    public function updatedPerPageProyeksi()
    {
        $this->resetPage('proyeksiPage');
    }

    public function mount($id)
    {
        $this->id = $id;
        $this->loadSimulasi();
    }

    public function loadSimulasi()
    {
        $cutiBersama = CutiBersama::with(['tanggal', 'jenisCuti', 'partisipasiKaryawan'])->findOrFail($this->id);
        $simulasiService = app(SimulasiCutiBersamaService::class);
        $this->simulasiData = $simulasiService->simulasikan($cutiBersama);

        if ($cutiBersama->status === 'draft') {
            $cutiBersama->update(['status' => 'disimulasikan']);
        }
    }

    public function setOverrideStatus(int $karyawanId, string $status)
    {
        $isIkut = ($status !== 'dikecualikan');

        \App\Models\Sdm\CutiBersamaKaryawan::updateOrCreate(
            ['cuti_bersama_id' => $this->id, 'karyawan_id' => $karyawanId],
            [
                'is_ikut' => $isIkut,
                'override_status' => $status,
            ]
        );

        $this->toast()->success('Pengaturan status pegawai berhasil disimpan.')->send();
        $this->loadSimulasi();
    }

    public function togglePartisipasi($karyawanId)
    {
        $existing = \App\Models\Sdm\CutiBersamaKaryawan::where('cuti_bersama_id', $this->id)
            ->where('karyawan_id', $karyawanId)
            ->first();

        if ($existing && !$existing->is_ikut) {
            $existing->update([
                'is_ikut' => true,
                'override_status' => 'auto',
            ]);
        } else {
            \App\Models\Sdm\CutiBersamaKaryawan::updateOrCreate(
                ['cuti_bersama_id' => $this->id, 'karyawan_id' => $karyawanId],
                [
                    'is_ikut' => false,
                    'override_status' => 'dikecualikan',
                ]
            );
        }

        $this->toast()->success('Status partisipasi pegawai berhasil diperbarui.')->send();
        $this->loadSimulasi();
    }

    public function setBulkOverrideStatus(string $status)
    {
        $filtered = $this->getFilteredKaryawanIds();
        if (empty($filtered)) return;

        $isIkut = ($status !== 'dikecualikan');

        foreach ($filtered as $kId) {
            \App\Models\Sdm\CutiBersamaKaryawan::updateOrCreate(
                ['cuti_bersama_id' => $this->id, 'karyawan_id' => $kId],
                [
                    'is_ikut' => $isIkut,
                    'override_status' => $status,
                ]
            );
        }

        $this->toast()->success(count($filtered) . ' Pegawai berhasil diperbarui ke status: ' . strtoupper($status))->send();
        $this->loadSimulasi();
    }

    public function selectAllIkut()
    {
        $this->setBulkOverrideStatus('auto');
    }

    public function selectAllTidakIkut()
    {
        $this->setBulkOverrideStatus('dikecualikan');
    }

    protected function getFilteredKaryawanIds(): array
    {
        $details = collect($this->simulasiData['details'] ?? []);
        $search = $this->searchPegawai;

        if (!empty($search)) {
            $details = $details->filter(fn($item) => stripos($item['karyawan_nama'], $search) !== false);
        }

        return $details->pluck('karyawan_id')->unique()->values()->toArray();
    }

    public function terapkan()
    {
        $cutiBersama = CutiBersama::findOrFail($this->id);

        if ($cutiBersama->status === 'diterapkan') {
            $this->toast()->warning('Event ini sudah terpasang/diterapkan.')->send();
            return;
        }

        try {
            $terapkanService = app(TerapkanCutiBersamaService::class);
            $terapkanService->terapkan($cutiBersama, auth()->id());

            $this->toast()->success('Event Cuti Bersama berhasil DITERAPKAN. Record surat_cuti & jadwal telah diperbarui.')->send();
            $this->loadSimulasi();
        } catch (Throwable $e) {
            $this->toast()->error('Gagal menerapkan Cuti Bersama: ' . $e->getMessage())->send();
        }
    }

    public function batalkan()
    {
        $cutiBersama = CutiBersama::findOrFail($this->id);

        try {
            $batalkanService = app(BatalkanCutiBersamaService::class);
            $batalkanService->batalkan($cutiBersama, auth()->id());

            $this->toast()->success('Event Cuti Bersama berhasil DIBATALKAN. Record surat_cuti & jadwal telah dipulihkan.')->send();
            $this->loadSimulasi();
        } catch (Throwable $e) {
            $this->toast()->error($e->getMessage())->send();
        }
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $cutiBersama = CutiBersama::with(['jenisCuti', 'tanggal', 'diprosesOleh'])->findOrFail($this->id);

        $search = $this->searchPegawai;
        $filter = $this->filterKategori;
        $partisipasi = $this->filterPartisipasi;

        $filteredDetails = collect($this->simulasiData['details'] ?? [])
            ->filter(function ($item) use ($search, $filter, $partisipasi) {
                if (!empty($search) && stripos($item['karyawan_nama'], $search) === false) {
                    return false;
                }

                if ($partisipasi === 'ikut' && !($item['is_ikut'] ?? true)) return false;
                if ($partisipasi === 'dikecualikan' && ($item['is_ikut'] ?? true)) return false;

                if ($filter === 'potong') return $item['status_aksi'] === 'DIPOTONG_CUTI';
                if ($filter === 'piket') return in_array($item['status_aksi'], ['TERJADWAL_PIKET', 'HADIR_PIKET', 'TETAP_HADIR']);
                if ($filter === 'roster') return $item['status_aksi'] === 'LIBUR_ROSTER';
                if ($filter === 'belum_ada') return $item['status_aksi'] === 'JADWAL_BELUM_ADA';
                if ($filter === 'dikecualikan') return $item['status_aksi'] === 'DIKECUALIKAN';

                return true;
            });

        $groupedDetails = $filteredDetails->groupBy('karyawan_id')->map(function ($items) {
            $first = $items->first();
            $totalHariDipotong = $items->where('status_aksi', 'DIPOTONG_CUTI')->count();
            $totalHariPiket = $items->whereIn('status_aksi', ['TERJADWAL_PIKET', 'HADIR_PIKET', 'TETAP_HADIR'])->count();
            $totalHariRoster = $items->where('status_aksi', 'LIBUR_ROSTER')->count();
            $totalHariDikecualikan = $items->where('status_aksi', 'DIKECUALIKAN')->count();

            return [
                'karyawan_id' => $first['karyawan_id'],
                'karyawan_nama' => $first['karyawan_nama'],
                'kategori_kerja' => $first['kategori_kerja'],
                'is_ikut' => $first['is_ikut'] ?? true,
                'override_status' => $first['override_status'] ?? 'auto',
                'total_hari_dipotong' => $totalHariDipotong,
                'total_hari_piket' => $totalHariPiket,
                'total_hari_roster' => $totalHariRoster,
                'total_hari_dikecualikan' => $totalHariDikecualikan,
                'dates' => $items->values()->toArray(),
            ];
        })->values();

        $page = $this->getPage();
        $totalGrouped = $groupedDetails->count();
        $paginatedGrouped = new LengthAwarePaginator(
            $groupedDetails->forPage($page, $this->perPage)->values(),
            $totalGrouped,
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $searchProyeksi = $this->searchProyeksi;
        $filterProyeksi = $this->filterProyeksi;

        $filteredProyeksi = collect($this->simulasiData['karyawan_summary'] ?? [])
            ->filter(function ($k) use ($searchProyeksi, $filterProyeksi) {
                if ($k['hari_terpotong'] <= 0) {
                    return false;
                }

                if (!empty($searchProyeksi) && stripos($k['nama'], $searchProyeksi) === false) {
                    return false;
                }

                if ($filterProyeksi === 'defisit' && !$k['is_minus']) {
                    return false;
                }

                if ($filterProyeksi === 'aman' && $k['is_minus']) {
                    return false;
                }

                return true;
            })->values();

        $pageProyeksi = $this->getPage('proyeksiPage');
        $totalProyeksi = $filteredProyeksi->count();
        $paginatedProyeksi = new LengthAwarePaginator(
            $filteredProyeksi->forPage($pageProyeksi, $this->perPageProyeksi)->values(),
            $totalProyeksi,
            $this->perPageProyeksi,
            $pageProyeksi,
            ['pageName' => 'proyeksiPage', 'path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.kepegawaian.cuti-bersama.show', [
            'cutiBersama' => $cutiBersama,
            'details' => $filteredDetails,
            'groupedDetails' => $paginatedGrouped,
            'proyeksiSummary' => $paginatedProyeksi,
        ]);
    }
}
