<div>
    {{ $this->table }}


    <x-filament::modal id="modal-edit-satuan" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Edit Satuan
        </x-slot:heading>

        <livewire:Master.Barang.Satuan.Edit :id="$selectedId" :key="Str::random()" @satuan-updated="$refresh" />
    </x-filament::modal>
</div>
