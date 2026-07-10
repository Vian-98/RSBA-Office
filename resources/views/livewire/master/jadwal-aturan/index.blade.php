<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-jadwal-aturan'})">
                Tambah Aturan
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new --}}
    <x-filament::modal id="new-jadwal-aturan" width="md" :autofocus="false">
        <x-slot name="heading">
            Tambah Aturan Bagian
        </x-slot>
        <livewire:Master.JadwalAturan.Add lazy @new-jadwal-aturan-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit --}}
    <x-filament::modal id="edit-jadwal-aturan" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Aturan Bagian
        </x-slot>
        @if($editingId)
            <livewire:Master.JadwalAturan.Edit lazy :key="$editingId" />
            <div x-init="$dispatch('load-jadwal-aturan-data', { id: {{ $editingId }} })"></div>
        @endif
    </x-filament::modal>
</div>
