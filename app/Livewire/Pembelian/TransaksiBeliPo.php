<?php

namespace App\Livewire\Pembelian;

use Throwable;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Master\Barang;
use Livewire\Attributes\Lazy;
use App\Models\Gudang\Pembelian;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Gudang\PembelianDetail;
use App\Models\Gudang\PembelianRequestDetails;
use App\Traits\BlocksTransactionDuringOpname;
use Illuminate\Support\Facades\Cache;

#[Lazy]
class TransaksiBeliPo extends Component
{
    use BlocksTransactionDuringOpname;
    use Interactions;

    public $createTerm = '';
    public $cartItems = [];

    public $tgl_pembelian;
    public int $supplier;

    protected $rules = [
        'tgl_pembelian' => 'required|date',
        'supplier' => 'required',
        'cartItems' => 'required|array|min:1',
        // 'cartItems.*.jumlah' => 'required|numeric|min:1',
    ];


    public function messages()
    {
        return [
            'cartItems.required' => 'Minimal tambah satu item pembelian.',
            'cartItems.min' => 'Minimal tambah satu item pembelian.'
        ];
    }

    public function mount()
    {
        $this->tgl_pembelian = date('Y-m-d');

        $cacheKey = session()->get('current_pengajuan_cache_key');
        $selectedIds = Cache::get($cacheKey);
        if ($cacheKey) {
            $this->loadProducts($selectedIds);
        }
    }

    function getBarang($id): ?object
    {
        $barang = Barang::with(['satuan', 'latestStok'])
            ->where('id', $id)
            ->orWhere('sku', $id)
            ->first();

        if ($barang) {
            $items = (object) [
                'id' => $barang->id,
                'bhp' => $barang->bhp == 1 ? true : false,
                'sku' => $barang->sku,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan->nama,
                'latest_harga' => $barang->latestStok->harga_satuan ?? 0
            ];
            return $items;
        }
        return null;
    }

    public function loadProducts($selectedIds)
    {
        $pengajuan = PembelianRequestDetails::with(['barang', 'barang.satuan'])
            ->whereIn('id', $selectedIds)
            ->selectRaw('barang_id, SUM(jml_disetujui) as total_jml_disetujui')
            ->groupBy('barang_id')
            ->get()
            ->map(function ($item): array {
                return [
                    'id' => $item->barang_id,
                    'bhp' => $item->barang->bhp,
                    'sku' => $item->barang->sku,
                    'nama' => $item->barang->nama,
                    'satuan' => $item->barang->satuan->nama,
                    'jumlah' => $item->total_jml_disetujui,
                    'harga' => 0,
                    'batch' => '',
                    'subTotal' => 0
                ];
            })->toArray();

        $this->cartItems = $pengajuan;
    }


    function submit()
    {
        /**
         * No 
         * {PO}{0001}{1224}
         * PO = Pre Order
         * 0001 = number [reset setiap tahun], max nomor setiap tahun 9999
         * 1224 = bulantahun
         */


        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'no' => $this->generateNumberPO(),
                'tgl' => $this->tgl_pembelian,
                'supplier_id' => $this->supplier,
                'jenis' => 'pre_order',
            ];

            // dd($data);
            $pembelian = Pembelian::create($data);

            // detil pembelian
            $items = collect($this->cartItems)->map(function ($item) use ($pembelian) {
                // If item is an object, convert to array
                $item = is_object($item) ? (array) $item : $item;

                return [
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'] ?? 0,
                ];
            });

            PembelianDetail::insert($items->toArray());

            DB::commit();

            $this->dispatch('new-transaksi-po-created');

            $this->toast()
                ->success('Berhasil', 'Pembelian berhasil disimpan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error:' . $e->getMessage())
                ->send();
        }
    }


    // generate nomor
    // urutan nomor berganti setiap tahun
    private function generateNumberPO(): string
    {
        $tglPembelian = $this->tgl_pembelian;
        $bulantahun = Carbon::parse($tglPembelian)->format('my');
        $tahun = Carbon::parse($tglPembelian)->format('Y');;

        $last = Pembelian::select('id', 'no')
            ->whereYear('tgl', $tahun)
            ->where('jenis', 'pre_order')
            ->orderBy('id', 'desc')
            ->first();

        $no = 1;
        if ($last) {
            $no = (int)substr($last->no, 6, 4) + 1;
        }
        $no = str_pad($no, 4, '0', STR_PAD_LEFT);

        return "PO{$bulantahun}{$no}";
    }

    public function render()
    {
        if (!$this->blockIfOpnameActive()) {
            return view('components.opname-block');
        }

        return view('livewire.pembelian.transaksi-beli-po');
    }
}
