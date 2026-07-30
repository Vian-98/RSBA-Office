<?php

namespace App\Livewire\Pembelian\Pesanan;

use App\Models\Gudang\Pembelian;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PrintPesanan extends Component
{
    public ?Pembelian $pembelian;

    public ?string $mengetahui;
    public ?string $menyetujui;
    public ?string $verifikator;


    public function mount(?int $id, ?string $mengetahui, ?string $menyetujui, ?string $verifikator)
    {
        $this->mengetahui = $mengetahui;
        $this->menyetujui = $menyetujui;
        $this->verifikator = $verifikator;

        $this->pembelian = Pembelian::with(['details', 'details.barang', 'details.barang.satuan', 'supplier'])->find($id);
    }

    #[Computed]
    public function items(): array
    {
        $items = $this->pembelian->details->map(function ($item): array {
            $harga = $item->harga_satuan ?? 0;
            $diskon = $item->diskon ?? 0;
            $ppn = $item->ppn ?? 0;

            return [
                'nama_barang' => $item->barang->nama,
                'qty' => $item->jumlah,
                'satuan' => $item->barang->satuan->nama,
                'harga' => $harga,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'ppn_amount' => (($item->jumlah * $harga) - $diskon) * ($ppn / 100)
            ];
        })->toArray();

        return $items;
    }

    #[Computed]
    public function getSummaryBayar()
    {
        $details =  collect($this->items ?? []);;

        $subtotal = $details->sum(fn($i) => $i['harga'] * $i['qty']);
        $diskon   = $details->sum(fn($i) => $i['diskon'] ?? 0);
        $setelah_diskon = $subtotal - $diskon;
        $ppn      = $details->sum(fn($i) => $i['ppn'] ?? 0);
        $ppn_amount      = $details->sum(fn($i) => $i['ppn_amount'] ?? 0);

        $total = $subtotal - $diskon + $ppn_amount;

        return collect([
            ['label' => 'Subtotal', 'nilai' => $subtotal],
            ['label' => 'Diskon', 'nilai' => $diskon],
            ['label' => 'Setelah Diskon', 'nilai' => $setelah_diskon],
            ['label' => 'PPN', 'nilai' => $ppn_amount],
            ['label' => 'Total Bayar', 'nilai' => $total],
        ]);
    }

    public function render()
    {
        return view('livewire.pembelian.pesanan.print-pesanan');
    }
}
