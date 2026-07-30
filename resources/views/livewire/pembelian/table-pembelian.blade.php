<div>
    {{ $this->table }}


    {{-- modal --}}
    <x-filament::modal id="modal-detail-pembelian" width="7xl" :close-on-click-away="false" x-on:tutup-detail-beli.window="$dispatch('close-modal', { id: 'modal-detail-pembelian' })">
        <x-slot:heading>Detail Pembelian</x-slot:heading>

        <livewire:Pembelian.Detail :id="$selectedId" :key="time() . $selectedId" />
    </x-filament::modal>


    <x-filament::modal id="modal-create-sp3" width="5xl" :close-on-click-away="false" x-on:created-sp3.window="$dispatch('close-modal',{id:'modal-create-sp3'})">
        <x-slot:heading>Buat SP3 Pembelian</x-slot:heading>

        <livewire:Surat.Sp3.AddSp3Pembelian :id="$selectedId" :key="time() . $selectedId" @created-sp3="$refresh" />
    </x-filament::modal>

    <x-filament::modal id="modal-view-sp3" width="5xl" :close-on-click-away="false">
        <x-slot:heading>View SP3</x-slot:heading>

        <livewire:Surat.Sp3.Details :$suratSp3 :key="Str::random(5)" />
    </x-filament::modal>

    <div x-data x-on:trigger-print.window="$nextTick(() => printArea('print-pre-order'))">
        <div class="hidden" id="print-pre-order">
            @if ($selectedId)
                <livewire:Pembelian.Pesanan.PrintPesanan :id="$selectedId" :$mengetahui :$menyetujui :$verifikator :key="'print-po' . Str::random()" />
            @endif
        </div>
    </div>
</div>
