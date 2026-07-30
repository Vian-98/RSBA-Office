<?php

namespace App\Livewire\Pembelian;

use Throwable;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Gudang\Stok;
use Illuminate\Support\Arr;
use App\Models\Master\Barang;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use App\Models\Gudang\Pembelian;
use App\Models\Gudang\Penerimaan;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Cache;
use App\Models\Gudang\PembelianDetail;
use App\Models\Gudang\PenerimaanDetail;
use App\Traits\BlocksTransactionDuringOpname;
use App\Models\Gudang\PembelianRequestDetails;
use App\Models\Gudang\StokMutasi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;

#[Lazy]
class TransaksiBeliLangsung extends Component
{
    use BlocksTransactionDuringOpname;
    use Interactions;
    use WithFileUploads;

    public $createTerm = '';
    public $cartItems = [];

    #[Locked]
    public $selectedPermintaan;

    public $tgl_pembelian, $tgl_pembayaran;
    public int $supplier;
    public string $no_faktur, $keterangan;
    public $status_pembayaran = 'lunas';
    public bool $isSaving = false;
    public array $cabarOptions = [
        ['value' => 'lunas', 'nama' => 'Tunai / Lunas'],
        ['value' => 'tempo', 'nama' => 'Tempo'],
    ];

    public  function rules(): array
    {
        return [
            'supplier' => 'required',
            'tgl_pembelian' => 'required|date',
            'no_faktur' => 'required',
            'tgl_pembayaran' => ['nullable', 'date', 'required_unless:status_pembayaran,lunas'],
            'cartItems' => 'required|array|min:1',
            // 'cartItems.*.jumlah' => 'required|numeric|min:1',
        ];
    }


    public function messages()
    {
        return [
            'cartItems.required' => 'Minimal ada satu item pembelian.',
            'cartItems.min' => 'Minimal ada satu item pembelian.',
            'lampirans.*' => 'file|mimes:png,jpg,jpeg,pdf|max:5120',
        ];
    }


    // Multi file uplods
    public $lampirans = [];
    public $backup = [];


    public function updatingLampirans(): void
    {
        $this->backup = $this->lampirans;
    }

    public function updatedLampirans(): void
    {
        if (!$this->lampirans) {
            return;
        }
        $file  = Arr::flatten(array_merge($this->backup, [$this->lampirans]));
        $this->lampirans = collect($file)
            ->unique(
                fn(UploadedFile $item) => $item->getClientOriginalName()
            )
            ->toArray();
    }

    public function deleteUpload(array $content): void
    {

        if (!$this->lampirans) {
            return;
        }

        $files = Arr::wrap($this->lampirans);
        $file = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() === $content['temporary_name'])->first();

        // 1. Here we delete the file. Even if we have a error here, we simply
        // ignore it because as long as the file is not persisted, it is
        // temporary and will be deleted at some point if there is a failure here.
        rescue(fn() => $file->delete(), report: false);

        $collect = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() !== $content['temporary_name']);

        // 2. We guarantee restore of remaining files regardless of upload
        $this->lampirans = is_array($this->lampirans) ? $collect->toArray() : $collect->first();
    }


    public function mount()
    {
        $this->tgl_pembelian = date('Y-m-d');


        // Check data dari pengajuan
        $cacheKey = session()->get('cart_pengajuan_cache_key');
        $this->selectedPermintaan = Cache::pull($cacheKey); //retrive data cache dan hapus cache
        if ($this->selectedPermintaan) {
            $this->loadProducts(selectedDetIds: $this->selectedPermintaan);
        }
    }

    public function loadProducts($selectedDetIds)
    {
        $pengajuan = PembelianRequestDetails::with(['barang', 'barang.satuan', 'barang.konversiSatuans.satuan'])
            ->whereIn('id', $selectedDetIds)
            ->selectRaw('barang_id, SUM(jml_disetujui) as total_jml_disetujui')
            ->groupBy('barang_id')
            ->get()
            ->map(function ($item): array {
                $barang = $item->barang;
                
                $konversiList = [];
                $konversiList[] = [
                    'satuan_id' => $barang->satuan_id,
                    'nama_satuan' => $barang->satuan->nama,
                    'rasio' => 1
                ];
                foreach ($barang->konversiSatuans as $k) {
                    $konversiList[] = [
                        'satuan_id' => $k->satuan_id,
                        'nama_satuan' => $k->satuan->nama,
                        'rasio' => $k->rasio
                    ];
                }

                return [
                    'id' => $barang->id,
                    'bhp' => $barang->bhp,
                    'sku' => $barang->sku,
                    'nama' => $barang->nama,
                    'satuan' => $barang->satuan->nama,
                    'satuan_beli_id' => $barang->satuan_id,
                    'rasio' => 1,
                    'selected_satuan_val' => $barang->satuan_id . '_1',
                    'pilihan_satuan' => $konversiList,
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

    public function getBarang($id): ?object
    {
        $barang = Barang::with(['satuan', 'konversiSatuans.satuan'])
            ->where('id', $id)
            ->orWhere('sku', $id)
            ->first();

        if ($barang) {
            $konversiList = [];
            // Add base unit as konversi ratio 1
            $konversiList[] = [
                'satuan_id' => $barang->satuan_id,
                'nama_satuan' => $barang->satuan->nama,
                'rasio' => 1
            ];
            // Add alternative units
            foreach ($barang->konversiSatuans as $k) {
                $konversiList[] = [
                    'satuan_id' => $k->satuan_id,
                    'nama_satuan' => $k->satuan->nama,
                    'rasio' => $k->rasio
                ];
            }

            $items = (object) [
                'id' => $barang->id,
                'bhp' => $barang->bhp == 1 ? true : false,
                'sku' => $barang->sku,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan->nama,
                'satuan_id' => $barang->satuan_id,
                'pilihan_satuan' => $konversiList,
            ];
            return $items;
        }
        return null;
    }

    // function updateItemQuantity($index, $quantity)
    // {
    //     if ($quantity > 0) {
    //         $this->cartItems[$index]->jumlah = $quantity;
    //     }
    // }

    // function deleteItemCart($index): void
    // {
    //     unset($this->cartItems[$index]);
    //     $this->cartItems = array_values($this->cartItems);
    // }

    public function submit()
    {
        /**
         * No 
         * {PD}{0001}{1224}
         * PD = Pembelian Direct
         * 0001 = number [reset setiap tahun], max nomor setiap tahun 9999
         * 1224 = bulantahun
         */

        $this->validate();

        if ($this->isSaving) {
            return;
        }
        $this->isSaving = true;

        DB::beginTransaction();
        try {

            // calc sub total
            $subtotal = collect($this->cartItems)
                ->sum(
                    fn($cart) => $cart['jumlah'] * $cart['harga']
                );

            //total ppn
            $totalPpn = collect($this->cartItems)
                ->sum(
                    fn($cart) => $cart['ppnAmount']
                );

            // total diskon
            $totalDiskon = collect($this->cartItems)
                ->sum(
                    fn($cart) => $cart['diskon']
                );

            // harus bayar
            $harus_bayar = ($subtotal - $totalDiskon) + $totalPpn;


            $lampirans = collect($this->lampirans)->map(
                function ($file) {
                    return $file->store('pembelian/langsung', 'public');
                }
            )->toArray();

            //Saving Data
            // 01. Header Pembelian
            $pembelian = Pembelian::create([
                'no' => $this->generateNumberPembelian(),
                'tgl' => $this->tgl_pembelian,
                'supplier_id' => $this->supplier,
                'jenis' => 'langsung',
                'status_pembayaran' => $this->status_pembayaran,
                'tgl_pembayaran' => $this->tgl_pembayaran ?? now(),
                'subtotal' => $subtotal,
                'total_diskon' => $totalDiskon,
                'total_ppn' => $totalPpn,
                'total' => $harus_bayar,
                'lampirans' => $lampirans,
                'created_by' => Auth::id(),
                'status' => 'selesai'
            ]);

            // 02. Header Penerimaan
            $penerimaan = Penerimaan::create([
                'tanggal' => $this->tgl_pembelian,
                'no_faktur' => $this->no_faktur,
                'keterangan' => $this->keterangan ?? '-',
                'penerima' => auth()->user()->id
            ]);

            // 03. Detail
            foreach ($this->cartItems as $item) {
                // Calculate base quantity
                $rasio = $item['rasio'] ?? 1;
                $qtyBeli = $item['jumlah'] ?? 0;
                $jumlahBase = $qtyBeli * $rasio;
                $hargaSatuanBase = $item['harga'] / $rasio; // convert package price to base unit price

                // 03.01 Pembelian Detail
                $dets = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $item['id'],
                    'satuan_beli_id' => $item['satuan_beli_id'] ?? $item['satuan_id'],
                    'qty_beli' => $qtyBeli,
                    'rasio' => $rasio,
                    'jumlah' => $jumlahBase,
                    'batch' => $item['batch'] ?? null,
                    'warranty' => $item['waranty_date'] ?? null,
                    'harga_satuan' => $hargaSatuanBase,
                    'diskon' => $item['diskon'] ?? 0,
                    'ppn' => $item['ppn'] ?? 0
                ]);

                // 03.02 Penerimaan Detail
                $terimaDets = PenerimaanDetail::create([
                    'penerimaan_id' => $penerimaan->id,
                    'pembelian_det_id' => $dets->id,
                    'jumlah' => $jumlahBase,
                ]);

                // 03.03 Stok In
                $stok = Stok::create([
                    'penerimaan_det_id' => $terimaDets->id,
                    'barang_id' => $dets->barang_id,
                    'stok' => $jumlahBase,
                    'batch' => $dets->batch,
                    'harga_satuan' => $hargaSatuanBase,
                ]);

                // 03.04 Mutasi Stok
                $this->createMutasiPembelian($stok, $dets, $pembelian, $jumlahBase);
            }
            // Update permintaan details
            if (!empty($this->selectedPermintaan)) {
                PembelianRequestDetails::whereIn('id', $this->selectedPermintaan)
                    ->update(['pembelian_id' => $pembelian->id]);
            }

            DB::commit();

            $this->dispatch('new-transaksi-langsung-created');
            $this->dispatch('close-modal', id: 'modal-pengajuan-to-langsung');

            $this->toast()
                ->success('Berhasil', 'Pembelian berhasil disimpan.')
                ->send();
        } catch (Throwable $e) {
            // Rollback
            DB::rollBack();
            $this->isSaving = false;

            $this->toast()
                ->error('Failed', 'Error:' . $e->getMessage())
                ->send();
        }
    }


    /**
     * Catat mutasi stok untuk transaksi pembelian langsung.
     * stok_sebelum = 0 karena ini adalah baris stok (batch) baru yang baru pertama kali dibuat.
     */
    private function createMutasiPembelian(
        object $stok,
        object $dets,
        object $pembelian,
        int $jumlahBase
    ): void {
        $keterangan = sprintf(
            'Pembelian Langsung No. %s | Barang ID: %d | Qty: %d',
            $pembelian->no,
            $dets->barang_id,
            $jumlahBase
        );

        StokMutasi::create([
            'stok_id'       => $stok->id,
            'barang_id'     => $dets->barang_id,
            'jenis_mutasi'  => 'PEMBELIAN',
            'jumlah'        => $jumlahBase,
            'multiplier'    => 1,
            'stok_sebelum'  => 0,          // Batch stok baru, belum pernah ada sebelumnya
            'stok_sesudah'  => $jumlahBase,
            'keterangan'    => $keterangan,
            'referensi_type' => PembelianDetail::class,
            'referensi_id'  => $dets->id,
            'created_by'    => auth()->id(),
            'is_posted'     => 1,
            'is_reversed'   => 0,
        ]);
    }

    // generate nomor
    // urutan nomor berganti setiap tahun
    private function generateNumberPembelian(): string
    {
        $tglPembelian = $this->tgl_pembelian;
        $bulantahun = Carbon::parse($tglPembelian)->format('my');
        $tahun = Carbon::parse($tglPembelian)->format('Y');;

        $last = Pembelian::select('id', 'no')
            ->whereYear('tgl', $tahun)
            ->where('jenis', 'langsung')
            ->orderBy('id', 'desc')
            ->first();

        $no = 1;
        if ($last) {
            $no = (int)substr($last->no, 6, 4) + 1;
        }
        $no = str_pad($no, 4, '0', STR_PAD_LEFT);

        return "PD{$bulantahun}{$no}";
    }

    public function render()
    {
        if (!$this->blockIfOpnameActive()) {
            return view('components.opname-block');
        }

        return view('livewire.pembelian.transaksi-beli-langsung');
    }
}
