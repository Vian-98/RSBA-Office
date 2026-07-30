<?php

namespace App\Livewire\Laporan\Umum;

use App\Models\Gudang\PenerimaanDetail;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Isolate;

#[Isolate]
#[Lazy]
class Pembelian extends Component
{
    public bool $init = true;

    #[Locked]
    public $headers = [
        ['index' => 'no_faktur', 'label' => 'No. Faktur'],
        ['index' => 'nama_barang', 'label' => 'Barang'],
        ['index' => 'tanggal', 'label' => 'Tanggal'],
        ['index' => 'supplier', 'label' => 'Supplier'],
        ['index' => 'jumlah', 'label' => 'Jumlah'],
        ['index' => 'harga', 'label' => 'Harga Satuan'],
        ['index' => 'subtotal', 'label' => 'Total'],
    ];

    #[Locked]
    public $rows = [];

    #[Locked]
    public $total = 0;

    public function mount()
    {
        $this->init = false;
        $periode = [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString(),
        ];
        $this->getDataBeli($periode, [], null, null);
    }


    #[On('filterPembelianLaporan')]
    public function cariDataBeli($data): void
    {
        $this->init = false;
        $periode = $data['periode'];
        $items = $data['items'];
        $vendor = $data['vendor'];
        $jenis = $data['jenis'];

        $this->getDataBeli($periode, $items, $vendor, $jenis);
    }

    #[Computed]
    public function getDataBeli($periode, $items, $vendor, $jenis)
    {
        // periode to string $periode_awal and $periode_akhir
        [$periode_awal, $periode_akhir] = $periode;

        $data = PenerimaanDetail::with('penerimaan', 'pembelianDet', 'pembelianDet.pembelian', 'pembelianDet.barang')
            ->whereHas(
                'penerimaan',
                function ($query) use ($periode_awal, $periode_akhir) {
                    $query->whereBetween(
                        'tanggal',
                        [$periode_awal, $periode_akhir]
                    );
                }
            )
            ->when(
                $vendor,
                function ($query, $vendor) {
                    $query->whereHas(
                        'pembelianDet.pembelian',
                        function ($q) use ($vendor) {
                            $q->where(
                                'supplier_id',
                                $vendor
                            );
                        }
                    );
                }
            )
            ->when(
                $items,
                function ($query, $items) {
                    $query->whereHas(
                        'pembelianDet',
                        function ($q) use ($items) {
                            $q->whereIn(
                                'barang_id',
                                $items
                            );
                        }
                    );
                }
            )
            ->get();

        $this->rows = $data->map(function ($data) {
            $total = ($data->pembelianDet->jumlah * $data->pembelianDet->harga_satuan);

            return [
                'nama_barang' => $data->pembelianDet->barang->nama,
                'no_faktur' => $data->penerimaan->no_faktur,
                'tanggal' => $data->penerimaan->tanggal,
                'supplier' => $data->pembelianDet->pembelian->supplier->nama,
                'jumlah' => $data->jumlah,
                'harga' => formatRupiah($data->pembelianDet->harga_satuan, false, false),
                'subtotal' => formatRupiah($total, false, false),
                'total' => $total
            ];
        })->toArray();

        // Total Table
        $this->total = collect(
            $this->rows
        )->map(function ($data) {
            return $data['total'];
        })->sum();
    }

    public function render()
    {
        return view('livewire.laporan.umum.pembelian');
    }
}
