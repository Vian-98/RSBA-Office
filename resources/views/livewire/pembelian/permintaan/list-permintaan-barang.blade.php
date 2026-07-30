<div>
    {{ $this->table }}


    <x-filament::modal id="modal-pengajuan-to-langsung" width="7xl">
        <x-slot:heading>Pembelian Langsung</x-slot:heading>

        <livewire:Pembelian.TransaksiBeliLangsung key="pembelian-langsung-permintaan" @new-transaksi-langsung-created="$refresh" />
    </x-filament::modal>


    <x-filament::modal id="modal-pengajuan-to-pesanan" width="7xl">
        <x-slot:heading>Pembelian Pre Order</x-slot:heading>

        <livewire:Pembelian.Pesanan.Add key="pesanan-permintaan" @new-pesanan-created="$refresh" />
    </x-filament::modal>
</div>
