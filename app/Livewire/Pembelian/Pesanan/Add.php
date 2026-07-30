<?php

namespace App\Livewire\Pembelian\Pesanan;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;

#[Lazy]
class Add extends Component
{
    use BlocksTransactionDuringOpname;
    use Interactions;

    public $createTerm = '';
    public $cartItems = [];

    #[Locked]
    public $selectedPermintaan;

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

        $cacheKey = session()->get('cart_pengajuan_cache_key');
        $this->selectedPermintaan = Cache::pull($cacheKey);
        if ($this->selectedPermintaan) {
            $this->loadProducts(selectedDetIds: $this->selectedPermintaan);
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

    public function loadProducts($selectedDetIds)
    {
        $pengajuan = PembelianRequestDetails::with(['barang', 'barang.satuan'])
            ->whereIn('id', $selectedDetIds)
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
                    'diskon' => 0,
                    'ppn' => 0,
                    'ppnAmount' => 0,
                    'batch' => null,
                    'waranty_date' => null,
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

            $carts = collect($this->cartItems);
            //calc total
            $subtotal = $carts->sum(
                fn($cart) => $cart['jumlah'] * $cart['harga']
            );

            // total ppn
            $totalPpn = $carts->sum(
                fn($cart) => $cart['ppnAmount']
            );

            // total Diskon
            $totalDiskon = $carts->sum(
                fn($cart) => $cart['diskon']
            );

            // harus bayar
            $harus_bayar = ($subtotal - $totalDiskon) + $totalPpn;

            // 01. Header Pembelian PO
            $pembelian = Pembelian::create([
                'no' => $this->generateNumberPO(),
                'tgl' => $this->tgl_pembelian,
                'supplier_id' => $this->supplier,
                'jenis' => 'pre_order',
                'subtotal' => $subtotal,
                'total_diskon' => $totalDiskon,
                'total_ppn' => $totalPpn,
                'total' => $harus_bayar,
                'created_by' => Auth::id(),
            ]);

            // 02. Detail Pembelian
            foreach ($this->cartItems as $item) {
                $dataDetails = [
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'] ?? 0,
                    'harga_satuan' => $item['harga'] ?? 0,
                    'diskon' => $item['diskon'] ?? 0,
                    'ppn' => $item['ppn'] ?? 0
                ];
                PembelianDetail::insert($dataDetails);
            }

            if (!empty($this->selectedPermintaan)) {
                PembelianRequestDetails::whereIn('id', $this->selectedPermintaan)
                    ->update(['pembelian_id' => $pembelian->id]);
            }

            DB::commit();

            $this->dispatch('new-pesanan-created');
            $this->dispatch('close-modal', id: 'modal-pengajuan-to-pesanan');

            $this->toast()
                ->success('Berhasil', 'Pesanan berhasil disimpan.')
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

        return view('livewire.pembelian.pesanan.add');
    }
}
