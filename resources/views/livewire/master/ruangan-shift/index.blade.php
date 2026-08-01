<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-ruangan-shift'})">
                Tambah Shift Ruangan
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new --}}
    <x-filament::modal id="new-ruangan-shift" width="md" :autofocus="false">
        <x-slot name="heading">
            Tambah Shift Ruangan
        </x-slot>
        <livewire:Master.RuanganShift.Add lazy @new-ruangan-shift-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit --}}
    <x-filament::modal id="edit-ruangan-shift" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Shift Ruangan
        </x-slot>
        @if($editingId)
            <livewire:Master.RuanganShift.Edit lazy :key="$editingId" />
            <div x-init="$dispatch('load-ruangan-shift-data', { id: {{ $editingId }} })"></div>
        @endif
    </x-filament::modal>
</div>
