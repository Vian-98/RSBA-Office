<?php

namespace App\Livewire\Jasmed\Dokter;

use Throwable;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\JmDokter;
use App\Models\JmPasien;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use TallStackUi\Traits\Interactions;

#[Lazy]
class CheckAnastesi extends Component
{
    use Interactions;

    public $tgl_checkout, $cabar;
    public $search_option = 'no_rekmedis';
    public $cari = '';

    public $anastesi = [];

    public function mount($tgl_checkout, $cabar, $search_option, $cari)
    {
        $this->tgl_checkout = $tgl_checkout;
        $this->cabar = $cabar;
        $this->search_option = $search_option;
        $this->cari = $cari;
    }

    #[Computed]
    public function getRows()
    {

        $query = JmPasien::select([
            'jm_pasien.id',
            'jm_pasien.nama_pasien',
            'jm_pasien.no_rekmedis',
            'jm_pasien.tgl_checkout',
            'jm_pasien.dpjp',
            'jm_pasien.sep',
            'jm_pasien.kelompok'
        ])
            ->leftJoin('jm_dokter', function ($join) {
                $join->on('jm_pasien.id', '=', 'jm_dokter.jm_pasien_id')
                    ->where('jm_dokter.status', '=', 'an');
            })
            ->whereNull('jm_dokter.id')
            ->where('layanan', 'ranap')
            ->where('cabar', $this->cabar)
            ->whereBetween('tgl_checkout', [
                Carbon::parse($this->tgl_checkout)->startOfMonth(),
                Carbon::parse($this->tgl_checkout)->endOfMonth()
            ])
            ->whereIn('kelompok', ['ri_sc', 'ri_op', 'ri_mata']);

        if ($this->search_option && $this->cari) {
            $query->where($this->search_option, 'like', '%' . $this->cari . '%');
        }

        return $query->paginate(20);
    }

    public function submitAnastesi($id): void
    {
        $value = $this->anastesi[$id] ?? null;

        if (!$value) return;

        DB::beginTransaction();

        try {
            JmDokter::updateOrCreate(
                ['jm_pasien_id' => $id, 'status' => 'an'],
                ['dokter' => $value, 'jumlah' => 1]
            );

            // clear input
            unset($this->anastesi[$id]);

            DB::commit();
            $this->toast()
                ->success('Berhasil', 'Dokter berhasil disimpan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Gagal', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {

        return view('livewire.jasmed.dokter.check-anastesi');
    }
}
