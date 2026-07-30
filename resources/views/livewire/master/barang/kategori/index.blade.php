<div class="flex flex-col gap-2">
    <div class="w-full flex flex-row py-2 px-4 bg-white rounded-lg">
        <div class="flex flex-row gap-2 text-gray-500">
            <x-ts:icon name="tabler.info-circle" />
            <span>Kategori barang seperti : barang material, logistik, atk, dll.</span>
        </div>
        <div class="flex ml-auto justify-end">
            <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'modal-new-kategori'})" icon="tabler.plus">
                Tambah
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-auto bg-white rounded-lg p-4">
        <livewire:Master.Barang.Kategori.TableKategori :key="Str::random()" />
    </div>

    <x-filament::modal id="modal-new-kategori" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Tambah Kategori Barang
        </x-slot:heading>

        <livewire:Master.Barang.Kategori.add :key="Str::random()" @new-kategori-created="$refresh" />
    </x-filament::modal>

</div>
