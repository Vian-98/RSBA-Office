<div>
    {{ $this->table }}

    <x-filament::modal id="modal-edit-barang" width="xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Edit Item Barang</x-slot:heading>

        <livewire:Master.Barang.Edit :id="$selectedId" :key="Str::random()" @barang-updated="$refresh"
            @new-kategori-created="$refresh" @satuan-created="$refresh" />
    </x-filament::modal>
</div>
