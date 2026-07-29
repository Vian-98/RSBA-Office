<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-ruangan-koordinator'})">
                Tambah Koordinator
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new --}}
    <x-filament::modal id="new-ruangan-koordinator" width="md" :autofocus="false">
        <x-slot name="heading">
            Tambah Koordinator Ruangan
        </x-slot>
        <livewire:Master.BagianKoordinator.Add lazy @new-ruangan-koordinator-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit --}}
    <x-filament::modal id="edit-ruangan-koordinator" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Koordinator Ruangan
        </x-slot>
        @if($editingId)
            <livewire:Master.BagianKoordinator.Edit lazy :id="$editingId" :key="$editingId" />
        @endif
    </x-filament::modal>

    {{-- Modal Atur Ruangan Koordinasi Multi-Select --}}
    <x-filament::modal id="modal-koor-ruangan" width="lg" :autofocus="false" :close-by-clicking-away="false">
        <x-slot name="heading">
            Atur Ruangan Koordinasi Dokter / Karyawan
        </x-slot>
        <x-slot name="description">
            Pilih ruangan-ruangan yang dapat dikelola jadwal kerjanya oleh koordinator ini.
        </x-slot>
        <livewire:Karyawan.Dokter.KoorRuangan @koor-ruangan-updated="$dispatch('close-modal', {id: 'modal-koor-ruangan'})" />
    </x-filament::modal>
</div>
