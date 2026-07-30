<?php

namespace App\Livewire\StokOpname;

use Exception;
use Livewire\Component;
use App\Helpers\ErrorHelper;
use Livewire\WithPagination;
use App\Models\Master\Barang;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use TallStackUi\Traits\Interactions;
use App\Models\Master\BarangKategori;

use App\Models\Gudang\OpnameStokDetail;

#[Lazy]
class Input extends Component
{
    use WithPagination, WithoutUrlPagination;

    use Interactions;

    public string $cari = '';
    public int $stokOpanmeId;
    public bool $sudahTerinput = false;
    public $filteredKategori;
    public $stokdata;
    public $kategoriBarang;

    public function mount($id)
    {
        $this->stokOpanmeId = $id;
        // $this->stokdata = $this->getDataSo($id);
        $this->kategoriBarang = $this->getKategoriBarang();
    }


    #[Computed]
    public function getDataSo()
    {
        $query = OpnameStokDetail::with([
            'stoks',
            'stoks.penerimaanDet',
            'barang',
            'barang.kategori',
            'barang.satuan'
        ])
            ->where('opname_id', $this->stokOpanmeId)
            ->when(
                $this->cari,
                fn($q) => $q->whereHas(
                    'barang',
                    fn($sq) => $sq->where('nama', 'like', "%{$this->cari}%")
                )
            )
            ->when(
                $this->filteredKategori,
                fn($q) => $q->whereHas(
                    'barang.kategori',
                    fn($sq) => $sq->where('id', $this->filteredKategori)
                )
            )
            ->when(
                $this->sudahTerinput,
                fn($q) => $q->whereNotNull('opname_by')
                    ->whereNotNull('selisih')
            )
            ->when(!$this->sudahTerinput, fn($q) => $q->where('opname_by', null))
            ->orderBy(
                Barang::select('nama')->whereColumn('um_barang.id', 'um_opname_stok_details.barang_id'),
                'asc'
            );

        return $query->paginate(25);
    }

    #[Computed]
    public function getKategoriBarang()
    {
        return BarangKategori::get();
    }

    public function saveRow($soDetId, $fisik, $textSelisih, $keterangan)
    {
        DB::beginTransaction();
        try {
            $so = OpnameStokDetail::findOrFail($soDetId);
            $so->stok_fisik = $fisik;
            $so->selisih = $textSelisih;
            $so->ket = $keterangan;
            $so->opname_by = auth()->user()->id;
            $so->save();

            DB::commit();

            $this->toast()
                ->success('Berhasil Disimpan.')
                ->send();
        } catch (Exception $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Tersimpan.', ErrorHelper::getUserMessage($e))
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.stok-opname.input');
    }
}
