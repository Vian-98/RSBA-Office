<div class="grid grid-cols-4 gap-2">
    <x-ts:stats icon="tabler.trending-down" color="red" title="Akan Habis" :number="$barangAkanHabis" footer="Barang akan habis" role="button" x-on:click="$dispatch('open-modal',{id:'modal-stok-akan-habis'})" />
    <x-ts:stats icon="tabler.stack-back" color="orange" title="Belum disusun" :number="$barangBelumDisusun" footer="Barang belum disusun." />
    <x-ts:stats icon="tabler.shopping-cart-bolt" color="green" title="Fast Moving" :number="$barangFastMoving" footer="Barang sering terpakai 1 bulan terkahir." role="button"
        x-on:click="$dispatch('open-modal',{id:'modal-fast-moving'})" />
    <x-ts:stats icon="tabler.shopping-cart-pause" color="blue" title="Slow Moving" :number="$barangSlowMoving" footer="Barang tidak terpakai 1 bulan terakhir." role="button"
        x-on:click="$dispatch('open-modal',{id:'modal-slow-moving'})" />
    <x-filament::modal id="modal-stok-akan-habis" width="max-w-4xl">
        <x-slot:heading>Data Stok Gudang Akan Habis</x-slot:heading>
        <livewire:Gudang.ViewStokHabis :key="Str::random()" />
    </x-filament::modal>
    <x-filament::modal id="modal-belum-disusun" width="max-w-4xl">
        <x-slot:heading>Data Barang Belum Disusun</x-slot:heading>
        <livewire:Gudang.ViewStokHabis :key="Str::random()" />
    </x-filament::modal>
    <x-filament::modal id="modal-fast-moving" width="max-w-4xl">
        <x-slot:heading>Data Gudang Fast Moving</x-slot:heading>
        <livewire:Gudang.ViewFastMoving :key="Str::random()" />
    </x-filament::modal>
    <x-filament::modal id="modal-slow-moving" width="max-w-4xl">
        <x-slot:heading>Data Gudang Slow Moving</x-slot:heading>
        <livewire:Gudang.ViewSlowMoving :key="Str::random()" />
    </x-filament::modal>
</div>
