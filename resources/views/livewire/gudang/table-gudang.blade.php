<div>
    {{ $this->table }}


    <x-filament::modal id="modal-detil-stok" width="5xl">
        <x-slot:heading>
            Detail Stok : <span class="text-indigo-500">{{ $barang?->nama }}</span>
        </x-slot:heading>

        <livewire:Gudang.DetailStok :barang="$barang" :key="Str::random()" />
    </x-filament::modal>


    <x-filament::modal id="modal-stok-aktif" width="5xl">
        <x-slot:heading>
            Stok Aktif : <span class="text-indigo-500">{{ $barang?->nama }}</span>
        </x-slot:heading>

        <livewire:Gudang.DetailStok :barang="$barang" :stok="true" :key="Str::random()" />
    </x-filament::modal>


    <div id="print-kartu-stok" class="hidden" x-on:print-kartu-stok.window="printArea('print-kartu-stok')">
        @if ($barang)
            <livewire:Gudang.PrintKartuStok :barang="$barang" :key="'kartu-stok-' . Str::random(5)" />
        @endif
    </div>


    <div id="print-kartu-stok-transaksi" class="hidden" x-on:print-kartu-stok-transaksi.window="printArea('print-kartu-stok-transaksi')">
        @if ($barang)
            <livewire:Gudang.PrintKartuStokByTrans :barang="$barang" :key="'kartu-stok-' . Str::random(5)" />
        @endif
    </div>
</div>
