<div class="flex w-full flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.user-plus" x-on:click="$dispatch('open-modal', {id:'new-jenis-cuti'})">
                Tambah
            </x-ts:button>
        </div>
    </div>

    <div class="relative items-center overflow-x-auto rounded-lg bg-white px-4 py-2">
        <livewire:Master.Cuti.TableJenisCuti :key="'table-jenis-cuti' . Str::random(5)" />
    </div>


    {{-- Modal new Jabatan --}}
    <x-filament::modal id="new-jenis-cuti" width="2xl" :autofocus="false" x-on:jenis-cuti-created.window="$dispatch('close-modal',{id:'new-jenis-cuti'})">
        <x-slot name="heading">
            Jenis Cuti Baru
        </x-slot>
        {{-- form --}}
        <livewire:Master.Cuti.Add key="tambah-jenis-cuti" @jenis-wcuti-created="$refresh" />
    </x-filament::modal>

</div>
