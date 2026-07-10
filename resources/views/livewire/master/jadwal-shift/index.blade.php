<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-jadwal-shift'})">
                Tambah
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new shift --}}
    <x-filament::modal id="new-jadwal-shift" width="md" :autofocus="false">
        <x-slot name="heading">
            Shift Baru
        </x-slot>
        <livewire:Master.JadwalShift.Add lazy @new-jadwal-shift-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit shift --}}
    <x-filament::modal id="edit-jadwal-shift" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Shift
        </x-slot>
        @if($editingId)
            <livewire:Master.JadwalShift.Edit lazy :key="$editingId" />
            <div x-init="$dispatch('load-shift-data', { id: {{ $editingId }} })"></div>
        @endif
    </x-filament::modal>
</div>
