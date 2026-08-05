<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2 flex items-center gap-2">
            <x-ts:button sm outline color="indigo" icon="tabler.adjustments" x-on:click="$dispatch('open-modal', {id:'modal-master-aturan-jadwal'})">
                Aturan Jadwal
            </x-ts:button>
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-bagian'})">
                Tambah
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal Master Aturan Jadwal --}}
    <x-filament::modal id="modal-master-aturan-jadwal" width="4xl" :autofocus="false">
        <x-slot name="heading">
            Master Aturan Jadwal
        </x-slot>
        <livewire:master.jadwal-aturan.index />
    </x-filament::modal>

    {{-- Modal new bagian --}}
    <x-filament::modal id="new-bagian" width="md" :autofocus="false">
        <x-slot name="heading">
            Bagian Baru
        </x-slot>
        {{-- form --}}
        <livewire:Master.Bagian.Add lazy @new-bagian-created="$refresh" />
    </x-filament::modal>

    {{-- Modal Aturan Jadwal Departemen --}}
    <x-filament::modal id="modal-aturan-bagian" width="2xl" :autofocus="false">
        <x-slot name="heading">
            Aturan Jadwal Departemen
        </x-slot>
        <livewire:master.bagian.bagian-aturan-modal />
    </x-filament::modal>
</div>
