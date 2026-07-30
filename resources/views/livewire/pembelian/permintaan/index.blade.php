<div class="flex flex-col gap-2">
    <div class="flex flex-row rounded-lg bg-white px-4 py-2">
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button @click="$dispatch('open-modal',{id:'modal-add-permintaan-beli'})" sm outline icon="tabler.send">Pengajuan</x-ts:button>
        </div>
    </div>

    <div class="w-full rounded-lg bg-white px-4 py-2">
        <livewire:pembelian.permintaan.listPermintaan key="list-permintaan" />
    </div>


    <x-filament::modal id="modal-add-permintaan-beli" width="4xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Pengajuan Pembelian</x-slot:heading>

        <livewire:Pembelian.Permintaan.Add @new-request-pembelian-created="$dispatch('close-modal',{id:'modal-add-permintaan-beli'})" :key="Str::random()" />
    </x-filament::modal>
</div>
