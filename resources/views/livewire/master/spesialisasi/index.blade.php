<div class="flex flex-col gap-2">
    <div class="ml-auto flex w-full justify-end rounded-lg bg-white px-4 py-2">
        <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-new-spesialis'})">
            Tambah
        </x-ts:button>

    </div>
    <div class="relative overflow-x-auto rounded-lg bg-white">
        <div class="p-5">
            <livewire:Master.Spesialisasi.Table :key="Str::random()" />
        </div>
    </div>

    <x-filament::modal id="modal-new-spesialis">
        <x-slot:heading>
            Tambah Spesialisasi Dokter
        </x-slot:heading>

        <livewire:Master.Spesialisasi.Add :key="Str::random()" @new-spesialisasi-created="$refresh" />
    </x-filament::modal>
</div>
