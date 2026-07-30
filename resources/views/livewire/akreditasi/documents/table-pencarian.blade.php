<div>
    {{ $this->table }}

    <x-filament::modal id="modal-view-document-search" width="screen" :close-by-escaping="true" :close-button="true">
        <x-slot:heading></x-slot:heading>
        <livewire:Akreditasi.Documents.View :$docSelectedId :key="'doc-view-search-' . $docSelectedId" />
    </x-filament::modal>

    <x-filament::modal id="modal-attach-file-search" width="3xl" :close-by-escaping="true" :close-button="true">
        <x-slot:heading>Attach File</x-slot:heading>
        <livewire:Akreditasi.Documents.AttachTo :$kegiatan_id :$docSelectedId :key="'doc-attach-to-' . $docSelectedId" />
    </x-filament::modal>
</div>
