<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-bagian-koordinator'})">
                Tambah Koordinator
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new --}}
    <x-filament::modal id="new-bagian-koordinator" width="md" :autofocus="false">
        <x-slot name="heading">
            Tambah Koordinator Bagian
        </x-slot>
        <livewire:Master.BagianKoordinator.Add lazy @new-bagian-koordinator-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit --}}
    <x-filament::modal id="edit-bagian-koordinator" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Koordinator Bagian
        </x-slot>
        @if($editingId)
            <livewire:Master.BagianKoordinator.Edit lazy :key="$editingId" />
            <div x-init="$dispatch('load-bagian-koordinator-data', { id: {{ $editingId }} })"></div>
        @endif
    </x-filament::modal>
</div>
