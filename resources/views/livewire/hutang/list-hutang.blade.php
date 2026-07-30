<div>

    {{ $this->table }}

    <x-filament::modal id="modal-pembayaran-hutang" width="7xl" :closew-by-clicking-away="false">
        <x-slot:heading>Pembayaran Hutang</x-slot:heading>
        <livewire:Hutang.Bayar.Add :id="$selectedId" :key="'pembayaran-add-' . $selectedId" />
    </x-filament::modal>

    <x-filament::modal id="modal-detail-pembayaran-hutang" width="5xl" :close-by-clicking-away="false">
        <x-slot:heading>Detail Pembelian</x-slot:heading>
        <livewire:Pembelian.Detail :id="$selectedId" :key="'detail-pembayaran' . $selectedId" />
    </x-filament::modal>
</div>
