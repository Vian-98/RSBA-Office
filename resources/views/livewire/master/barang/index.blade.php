<div class="flex flex-col gap-2">
    <div class="flex w-full justify-end rounded-lg bg-white px-4 py-2">
        <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'modal-new-barang'})" icon="tabler.plus">
            Baru
        </x-ts:button>
    </div>
    <div class="relative overflow-auto rounded-lg bg-white p-4">
        <livewire:Master.Barang.TableBarang :key="Str::random()" />
    </div>



    <x-filament::modal id="modal-new-barang" width="xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot name="heading">
            Tambah Item Barang
        </x-slot>

        <livewire:Master.Barang.Add :key="Str::random()" @new-barang-created="$refresh" @new-kategori-created="$refresh" @satuan-created="$refresh" />
    </x-filament::modal>
</div>
