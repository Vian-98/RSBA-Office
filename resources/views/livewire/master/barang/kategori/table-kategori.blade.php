<div>
    {{ $this->table }}


    {{-- modal --}}
    <x-filament::modal id="modal-edit-kategori-barang">
        <x-slot:heading>Edit Kategori Barang</x-slot:heading>

        <livewire:Master.Barang.Kategori.Edit :id="$selectedId" :key="Str::random()" @kategori-barang-updated="$refresh" />
    </x-filament::modal>
</div>
