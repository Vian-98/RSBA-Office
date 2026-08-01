<div>
    {{ $this->table }}

    {{-- modal edit --}}
    <x-filament::modal id="modal-edit-penyimpanan">
        <x-slot:heading>Edit Tempat Penyimpanan</x-slot:heading>

        <livewire:Master.Penyimpanan.Edit :id="$selectedId" :key="Str::random()" @penyimpanan-updated="$refresh" />
    </x-filament::modal>
</div>
