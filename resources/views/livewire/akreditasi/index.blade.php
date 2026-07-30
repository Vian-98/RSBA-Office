<div class="flex flex-col gap-2">
    <div class="flex flex-row rounded-lg bg-white px-4 py-2">
        <div class="ml-auto flex items-center justify-end gap-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-kegiatan-akre'})">
                Kegiatan
            </x-ts:button>
        </div>
    </div>

    <div class="w-full rounded-lg bg-white p-2">
        <livewire:Akreditasi.TableKegiatan />
    </div>


    <x-filament::modal id="modal-add-kegiatan-akre" width="xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Akreditasi</x-slot:heading>

        <livewire:Akreditasi.AddKegiatan />
    </x-filament::modal>
</div>
