<div class="flex flex-col gap-2">
    <div class="w-full flex bg-white rounded-lg py-2 px-4">
        <div class="flex flex-row gap-2 text-gray-500">
            <x-ts:icon name="tabler.info-circle" />
            <span>Lokasi penyimpanan stok barang, contoh : gudang, lemari, rak. </span>
        </div>
        <div class="flex ml-auto justify-end">
            <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'modal-new-penyimpanan'})" icon="tabler.plus">
                Tambah</x-ts:button>
        </div>
    </div>

    <div class="w-full bg-white rounded-lg p-4">
        <livewire:Master.Penyimpanan.TablePenyimpanan :key="Str::random()" />
    </div>



    {{-- modal new --}}
    <x-filament::modal id="modal-new-penyimpanan" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Tambah Tempat Penyimpanan
        </x-slot:heading>

        <livewire:Master.Penyimpanan.Add :key="Str::random()" @new-penyimpanan-created="$refresh" />
    </x-filament::modal>
</div>
