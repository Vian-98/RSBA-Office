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

    public function mount($id)
    {
        $this->id = $id;
        $this->loadSimulasi();
    }

    public function loadSimulasi()
    {
        $cutiBersama = CutiBersama::with(['tanggal', 'jenisCuti'])->findOrFail($this->id);
        $simulasiService = app(SimulasiCutiBersamaService::class);
        $this->simulasiData = $simulasiService->simulasikan($cutiBersama);

        if ($cutiBersama->status === 'draft') {
            $cutiBersama->update(['status' => 'disimulasikan']);
        }
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

        $filteredDetails = collect($this->simulasiData['details'] ?? [])
            ->filter(function ($item) use ($search, $filter) {
                if (!empty($search) && stripos($item['karyawan_nama'], $search) === false) {
                    return false;
                }

                if ($filter === 'potong') return $item['status_aksi'] === 'DIPOTONG_CUTI';
                if ($filter === 'piket') return $item['status_aksi'] === 'TETAP_HADIR';
                if ($filter === 'roster') return $item['status_aksi'] === 'LIBUR_ROSTER';
                if ($filter === 'belum_ada') return $item['status_aksi'] === 'JADWAL_BELUM_ADA';

                return true;
            });

        return view('livewire.kepegawaian.cuti-bersama.show', [
            'cutiBersama' => $cutiBersama,
            'details' => $filteredDetails,
        ]);
    }
}
