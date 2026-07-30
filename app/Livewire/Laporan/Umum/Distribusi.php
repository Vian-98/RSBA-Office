<?php

namespace App\Livewire\Laporan\Umum;

use App\Models\Gudang\DistribusiDetail;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;

#[Lazy]
class Distribusi extends Component
{
    public bool $init = true;

    #[Locked]
    public $headers = [
        ['index' => 'nama_barang', 'label' => 'Barang'],
        ['index' => 'tanggal', 'label' => 'Tanggal'],
        ['index' => 'ruangan', 'label' => 'Ruangan'],
        ['index' => 'penerima', 'label' => 'Penerima'],
        ['index' => 'jumlah', 'label' => 'Jumlah'],
        ['index' => 'harga', 'label' => 'Harga Satuan'],
        ['index' => 'total', 'label' => 'Total'],
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
        $this->getDataDistribusi($periode, [], null);
    }

    #[On('filterDistribusiLaporan')]
    function cariDataDistribusi($data)
    {
        $this->init = false;

        $periode = $data['periode'];
        $items = $data['items'];
        $ruangan = $data['ruangan'];

        $this->getDataDistribusi($periode, $items, $ruangan);
    }


    #[Computed]
    public function getDataDistribusi($periode, $items, $ruangan)
    {
        // periode to string $periode
        [$periode_awal, $periode_akhir] = $periode;

        $data = DistribusiDetail::with('distribusi', 'stoks', 'stoks.barang')
            ->whereHas(
                'distribusi',
                function ($query) use ($periode_awal, $periode_akhir) {
                    $query->whereBetween(
                        'tanggal',
                        [$periode_awal, $periode_akhir]
                    );
                    // $query->where('tanggal', '>=', $periode_awal)
                    //     ->where('tanggal', '<=', $periode_akhir);
                }
            )
            ->when(
                $ruangan,
                function ($query, $ruangan) {
                    $query->whereHas(
                        'distribusi',
                        function ($q) use ($ruangan) {
                            $q->where('tujuan', $ruangan);
                        }
                    );
                }
            )
            ->when(
                $items,
                function ($query, $items) {
                    $query->whereHas(
                        'stoks',
                        function ($q) use ($items) {
                            $q->whereIn('barang_id', $items);
                        }
                    );
                }
            )
            ->get()
            ->sortBy('distribusi.tanggal');

        // return $data;
        $this->rows = $data->map(function ($data): array {
            return [
                'nama_barang' => $data->stoks->barang->nama,
                'tanggal' => $data->distribusi->tanggal,
                'ruangan' => $data->distribusi->ruangan->nama,
                'penerima' => $data->distribusi->penerima_nama,
                'jumlah' => $data->jml . ' ' . $data->stoks->barang->satuan->nama,
                'harga' => formatRupiah($data->stoks->harga_satuan, false, false),
                'total' => formatRupiah(($data->jml * $data->stoks->harga_satuan), false, false),
            ];
        })->toArray();

        $this->total = $data->sum(function ($item) {
            return $item->jml * $item->stoks->harga_satuan;
        });
    }

    public function render()
    {
        return view('livewire.laporan.umum.distribusi');
    }
}
