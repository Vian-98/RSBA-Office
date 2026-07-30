<?php

namespace App\Livewire\StokOpname;

use Throwable;
use Livewire\Component;
use App\Models\Gudang\Stok;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use App\Models\Gudang\OpnameStok;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Gudang\OpnameStokDetail;
use App\Traits\AuthorizesFromRoute;

#[Title('Stok Opname')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;
    use Interactions;

    public function create()
    {
        DB::beginTransaction();
        try {

            if (OpnameStok::active()->exists()) {
                $this->toast()
                    ->warning('Tidak Berhasil.', 'Terdapat kegiatan stok opname yang sedang berlangsung.')
                    ->send();
                return;
            }

            $so = $this->createKegiatan();
            $this->createSnapshotStok($so->id);

            DB::commit();

            $this->render();

            $this->toast()
                ->success('Berhasil', 'Kegiatan stok opaname ditambahkan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            if ($e->getCode() == 23000) { //duplicate status == 'process'
                $this->toast()
                    ->warning('Tidak Berhasil.', 'Terdapat kegiatan stok opname yang sedang berlangsung.')
                    ->send();
                return;
            }

            $this->toast()
                ->error('Tidak Berhasil', "<i>{$e->getMessage()}</i>")
                ->send();
        }
    }

    private function createKegiatan(): OpnameStok
    {
        $stokOpname = OpnameStok::create(
            ['created_by' => auth()->user()->id]
        );

        return $stokOpname;
    }

    private function createSnapshotStok($so_id): void
    {
        $stok = Stok::where('is_open', true)->get();

        foreach ($stok as $item) {
            OpnameStokDetail::create([
                'opname_id' => $so_id,
                'barang_id' => $item->barang_id,
                'stok_id' => $item->id,
                'stok_sistem_opname' => $item->stok,
                'stok_fisik' => $item->stok,
                'harga_satuan' => $item->harga_satuan,
            ]);
        }
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.stok-opname.index');
    }
}
