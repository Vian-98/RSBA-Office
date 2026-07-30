<div class="flex flex-col gap-2">
    {{-- action navbar --}}
    <div class="flex w-full justify-end rounded-lg bg-white px-4 py-2">
        <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'modal-new-satuan'})" icon="tabler.plus">
            Tambah
        </x-ts:button>
    </div>

    {{-- table --}}
    <div class="relative overflow-auto rounded-lg bg-white p-4">
        <livewire:Master.Barang.Satuan.TableSatuan :key="Str::random()" />
    </div>


    {{-- modal --}}
    <x-filament::modal id="modal-new-satuan" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Tambah Satuan
        </x-slot:heading>

        <livewire:Master.Barang.Satuan.Add :key="Str::random()" @satuan-created="$refresh" />
    </x-filament::modal>
</div>
