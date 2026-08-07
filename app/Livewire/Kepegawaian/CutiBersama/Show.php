<?php

namespace App\Livewire\Kepegawaian\CutiBersama;

use App\Models\Sdm\CutiBersama;
use App\Services\BatalkanCutiBersamaService;
use App\Services\SimulasiCutiBersamaService;
use App\Services\TerapkanCutiBersamaService;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Title('Detail & Simulasi Cuti Bersama')]
class Show extends Component
{
    use AuthorizesFromRoute;
    use Interactions;

    public int $id;
    public ?array $simulasiData = null;
    public $searchPegawai = '';
    public $filterKategori = 'semua'; // semua, potong, piket, roster, belum_ada

    public $filterPartisipasi = 'semua'; // semua, ikut, dikecualikan
    public $viewMode = 'pegawai'; // pegawai (grouped per karyawan), tanggal (flat per tanggal)

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

    public function togglePartisipasi($karyawanId)
    {
        $existing = \App\Models\Sdm\CutiBersamaKaryawan::where('cuti_bersama_id', $this->id)
            ->where('karyawan_id', $karyawanId)
            ->first();

        if ($existing) {
            $existing->update(['is_ikut' => !$existing->is_ikut]);
        } else {
            \App\Models\Sdm\CutiBersamaKaryawan::create([
                'cuti_bersama_id' => $this->id,
                'karyawan_id' => $karyawanId,
                'is_ikut' => false, // Default was true, so toggling makes it false
            ]);
        }

        $this->toast()->success('Status partisipasi pegawai berhasil diperbarui.')->send();
        $this->loadSimulasi();
    }

    public function selectAllIkut()
    {
        $filtered = $this->getFilteredKaryawanIds();
        if (empty($filtered)) return;

        \App\Models\Sdm\CutiBersamaKaryawan::where('cuti_bersama_id', $this->id)
            ->whereIn('karyawan_id', $filtered)
            ->update(['is_ikut' => true]);

        $this->toast()->success(count($filtered) . ' Pegawai diset menjadi PESERTA (IKUT).')->send();
        $this->loadSimulasi();
    }

    public function selectAllTidakIkut()
    {
        $filtered = $this->getFilteredKaryawanIds();
        if (empty($filtered)) return;

        foreach ($filtered as $kId) {
            \App\Models\Sdm\CutiBersamaKaryawan::updateOrCreate(
                ['cuti_bersama_id' => $this->id, 'karyawan_id' => $kId],
                ['is_ikut' => false]
            );
        }

        $this->toast()->warning(count($filtered) . ' Pegawai diset DIKECUALIKAN (TIDAK IKUT).')->send();
        $this->loadSimulasi();
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
                if ($filter === 'piket') return $item['status_aksi'] === 'TETAP_HADIR';
                if ($filter === 'roster') return $item['status_aksi'] === 'LIBUR_ROSTER';
                if ($filter === 'belum_ada') return $item['status_aksi'] === 'JADWAL_BELUM_ADA';
                if ($filter === 'dikecualikan') return $item['status_aksi'] === 'DIKECUALIKAN';

                return true;
            });

        $groupedDetails = $filteredDetails->groupBy('karyawan_id')->map(function ($items) {
            $first = $items->first();
            $totalHariDipotong = $items->where('status_aksi', 'DIPOTONG_CUTI')->count();
            $totalHariPiket = $items->where('status_aksi', 'TETAP_HADIR')->count();
            $totalHariRoster = $items->where('status_aksi', 'LIBUR_ROSTER')->count();
            $totalHariDikecualikan = $items->where('status_aksi', 'DIKECUALIKAN')->count();

            return [
                'karyawan_id' => $first['karyawan_id'],
                'karyawan_nama' => $first['karyawan_nama'],
                'kategori_kerja' => $first['kategori_kerja'],
                'is_ikut' => $first['is_ikut'] ?? true,
                'total_hari_dipotong' => $totalHariDipotong,
                'total_hari_piket' => $totalHariPiket,
                'total_hari_roster' => $totalHariRoster,
                'total_hari_dikecualikan' => $totalHariDikecualikan,
                'dates' => $items->values()->toArray(),
            ];
        })->values();

        return view('livewire.kepegawaian.cuti-bersama.show', [
            'cutiBersama' => $cutiBersama,
            'details' => $filteredDetails,
            'groupedDetails' => $groupedDetails,
        ]);
    }
}
