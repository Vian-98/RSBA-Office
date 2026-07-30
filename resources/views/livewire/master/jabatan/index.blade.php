<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.user-plus" x-on:click="$dispatch('open-modal', {id:'new-jabatan'})">
                Jabatan
            </x-ts:button>
        </div>
    </div>

    <div class="relative items-center overflow-x-auto rounded-lg bg-white px-4 py-2">
        <livewire:Master.Jabatan.JabatanTable :key="Str::random()" />
    </div>


    {{-- Modal new Jabatan --}}
    <x-filament::modal id="new-jabatan" :autofocus="false">
        <x-slot name="heading">
            Jabatan Baru
        </x-slot>
        {{-- form --}}
        <livewire:Master.Jabatan.Add @new-jabatan-created="$refresh" :key="Str::random()" />
    </x-filament::modal>

</div>
