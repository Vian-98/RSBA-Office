<?php

namespace App\Livewire\StokOpname;

use Throwable;
use Livewire\Component;
use App\Models\Gudang\Stok;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use App\Models\Gudang\OpnameStok;
use App\Models\Gudang\StokMutasi;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use TallStackUi\Traits\Interactions;
use App\Models\Gudang\OpnameStokDetail;
use App\Models\Gudang\OpnameInvestigasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;

#[Lazy]
class Investigasi extends Component
{
    use Interactions;
    use WithPagination, WithoutUrlPagination;

    #[Locked]
    public ?int $opname_id;

    public ?string $search = "";

    // Konstanta untuk hasil investigasi
    private const HASIL_INVESTIGASI = [
        'HILANG' => 'Hilang',
        'RUSAK' => 'Rusak',
        'SALAH_INPUT' => 'Salah Input',
        'BARANG_DITEMUKAN' => 'Barang Ditemukan',
        'KEUNTUNGAN_STOK' => 'Keuntungan Stok',
        'LAINNYA' => 'Lainnya',
    ];

    public function mount(int $id)
    {
        $this->opname_id = $id;
    }

    public function getHasilInvestigasi(): array
    {
        return collect(self::HASIL_INVESTIGASI)
            ->map(fn($label, $id) => ['id' => $id, 'label' => $label])
            ->values()
            ->toArray();
    }

    // public function getOpnameDetails()
    // {
    //     return OpnameStokDetail::with(['barang', 'investigasi'])
    //         ->where('opname_id', $this->opname_id);
    // }

    // public function getOpnameNeedInvestigastions()
    // {
    //     return $this->getOpnameDetails()->where('selisih', '!=', 0);
    // }

    // #[Computed]
    // public function opnameInvestigationsPaginate()
    // {
    //     return $this->getOpnameNeedInvestigastions()
    //         ->when(
    //             $this->search,
    //             fn($has) => $has->whereHas(
    //                 'barang',
    //                 fn($q) => $q->where('nama', 'like', "%{$this->search}%")
    //             )
    //         )
    //         ->paginate(10);
    // }

    // #[Computed]
    // public function opnameInvestigationsData()
    // {
    //     return $this->opnameInvestigationsPaginate->items();
    // }

    #[Computed]
    public function investigations(): LengthAwarePaginator
    {
        return OpnameStokDetail::with(['barang', 'investigasi'])
            ->where('opname_id', $this->opname_id)
            ->where('selisih', '!=', 0)
            ->when(
                $this->search,
                fn(Builder $query) => $query->whereHas(
                    'barang',
                    fn($q) => $q->where('nama', 'like', "%{$this->search}%")
                )
            )
            ->paginate(10);
    }

    #[Computed]
    public function investigationsData(): Collection
    {
        return $this->investigations->getCollection();
    }

    public function saveRow($so_det_id, $stok_fisik, $selisih, $keterangan, $hasil_investigasi)
    {
        DB::beginTransaction();
        try {
            $opnDetails = OpnameStokDetail::find($so_det_id);
            $opnDetails->stok_fisik = $stok_fisik;
            $opnDetails->selisih = $selisih;
            $opnDetails->save();

            $dataInvestigasi = [
                'hasil_investigasi' => $hasil_investigasi,
                'catatan_investigasi' => $keterangan,
                'investigated_by' => auth()->user()->id,
                'investigated_at' => date('Y-m-d H:i:s')
            ];

            OpnameInvestigasi::updateOrCreate(
                ['opname_detail_id' => $so_det_id],
                $dataInvestigasi
            );

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Hasil investigasi disimpan.')
                ->send();
        } catch (Throwable $e) {

            DB::rollBack();
            $this->toast()
                ->error('Tidak Berhasil.', $e->getMessage())
                ->send();
        }

        return $opnDetails->fresh(['barang', 'investigasi']);
    }

    public function submitInvestigasiOpname()
    {
        // Check if there are items with discrepancies
        $totalDataSo = OpnameStokDetail::where('opname_id', $this->opname_id)
            ->where('selisih', '!=', 0)
            ->count();


        if ($totalDataSo === 0) {
            $this->updateOpnameStatus($this->opname_id);
            $this->toast()
                ->success('Berhasil.', 'Stok opname telah selesai, tidak ada stok yang diadjustment.')
                ->send();
            return;
        }

        // Check items without investigasi
        $withoutInvestigasi = OpnameStokDetail::where('opname_id', $this->opname_id)
            ->where('selisih', '!=', 0)
            ->doesntHave('investigasi')
            ->count();

        // Some items missing investigation
        if ($withoutInvestigasi > 0) {
            $this->toast()
                ->warning('Peringatan', 'Silahkan lengkapi terlebih dahulu data investigasi.')
                ->send();
            return;
        }

        // All items need investigation but none have it
        if ($withoutInvestigasi === $totalDataSo) {
            $this->updateOpnameStatus($this->opname_id);
            $this->toast()
                ->success('Berhasil.', 'Stok opname telah selesai, tidak ada stok yang diadjustment.')
                ->send();
            return;
        }

        // All validated, fetch full data
        $data = OpnameStokDetail::with(['investigasi', 'barang', 'stoks'])
            ->where('opname_id', $this->opname_id)
            ->where('selisih', '!=', 0)
            ->get();

        // Pre-load semua mutasi dalam satu query
        // $mutasiData = $this->getBulkMutasiAfterOpname(
        //     $data->pluck('stok_id')->unique()->toArray(),
        //     $data->first()->created_at
        // );

        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $this->procesInvestigasiWithoutMutasi($item);
            }
            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Stok opname telah divalidasi, dan stok telah disesuaikan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        } finally {
            $this->updateOpnameStatus($this->opname_id);
            $this->resetComputedProperties();

            // dispatch event
            $this->dispatch('close-modal', id: 'modal-so-investigasi');
            $this->dispatch('opname-validasi-saved');
        }
    }

    private function getBulkMutasiAfterOpname(array $stok_ids, $tanggal): array
    {
        return StokMutasi::whereIn('stok_id', $stok_ids)
            ->where('created_at', '>', $tanggal)
            ->where('is_posted', true)
            ->select('stok_id', DB::raw('SUM(jumlah * multiplier) as total_mutasi'))
            ->groupBy('stok_id')
            ->pluck('total_mutasi', 'stok_id')
            ->toArray();
    }

    // 
    private function procesInvestigasiWithoutMutasi(OpnameStokDetail $item): void
    {
        $stoks = $item->stoks;
        $stokSaatIni = $stoks->stok;

        $stokAdjustmentFinal = $item->selisih;

        $item->investigasi->update([
            'stok_sistem_validasi' => $stokSaatIni,
            'total_mutasi_during_investigasi' => 0,
            'stok_adjustment' => $stokAdjustmentFinal
        ]);

        if ($stokAdjustmentFinal != 0) {
            $this->createAdjustmentMutasi(
                item: $item,
                stoks: $stoks,
                stokSaatIni: $stokSaatIni,
                adjustmentFinal: $stokAdjustmentFinal
            );
        }
    }

    private function createAdjustmentMutasi(
        OpnameStokDetail $item,
        Stok $stoks,
        float $stokSaatIni,
        float $adjustmentFinal
    ): void {
        $stokSetelahAdjustment = $stokSaatIni + $adjustmentFinal;

        $dataMutasi = [
            'stok_id' => $item->stok_id,
            'barang_id' => $item->barang_id,
            'jenis_mutasi' => $this->getJenisMutasi(
                $item->investigasi->hasil_investigasi,
                $adjustmentFinal
            ),
            'jumlah' => abs($adjustmentFinal),
            'multiplier' => $adjustmentFinal > 0 ? 1 : -1,
            'stok_sebelum' => $stokSaatIni,
            'stok_sesudah' => $stokSetelahAdjustment,
            'keterangan' => $this->getKeteranganAdjustment($item),
            'referensi_type' => OpnameStokDetail::class,
            'referensi_id' => $item->id,
            'created_by' => auth()->id(),
            'is_posted' => true,
        ];

        $dataStoksUpdate =
            ($stoks->stok == 0 && $adjustmentFinal == 0) ?
            ['is_open' => 0] :
            ['stok' => $stokSetelahAdjustment];

        // execute
        StokMutasi::create($dataMutasi);
        $stoks->update($dataStoksUpdate);
    }


    private function resetComputedProperties(): void
    {
        unset(
            $this->investigations,
            $this->investigationsData,
            $this->totalInvestigated
        );
    }

    private function stoksClose(Stok $stoks): void
    {
        $stoks->update([
            'is_open' => 0
        ]);
    }

    private function updateOpnameStatus(int $opname_id): void
    {
        OpnameStok::where('id', $opname_id)->update([
            'status' => 'completed',
            'validate_by' => auth()->id(),
            'validate_at' => now(),
        ]);
    }

    private function getKeteranganAdjustment($detail): string
    {
        return sprintf(
            "Opname %s - %s: %s\n" .
                "Detail: Stok Opname Sistem=%d, Fisik=%d, Selisih Opname=%d\n" .
                "Mutasi Operasional=%d, Adjustment Final=%d",
            $detail->opname_id,
            $detail->investigasi->hasil_investigasi,
            $detail->investigasi->catatan_investigasi,
            $detail->stok_sistem_opname,
            $detail->stok_fisik,
            $detail->selisih,
            $detail->investigasi->total_mutasi_during_investigasi,
            $detail->investigasi->stok_adjustment
        );
    }

    private function getJenisMutasi(string $hasil, int $adjustment): string
    {
        return match ($hasil) {
            'HILANG' => 'OPNAME_MISSING',
            'RUSAK' => 'OPNAME_WRITE_OFF',
            'BARANG_DITEMUKAN' => 'OPNAME_FOUND',
            'SALAH_INPUT' => 'OPNAME_CORRECTION',
            default => $adjustment > 0 ? 'ADJUSTMENT_PLUS' : 'ADJUSTMENT_MINUS'
        };
    }

    public function render()
    {
        return view('livewire.stok-opname.investigasi');
    }
}
