<div>
    <div>{{ $this->table }} </div>


    <x-filament::modal id="modal-document-view" width="screen" :close-by-escaping="true" :close-button="true">
        <x-slot:heading></x-slot:heading>
        <livewire:Akreditasi.Documents.View :docSelectedId="$selectedDocId" :key="'view-doc-' . Str::random()" />
    </x-filament::modal>


    <x-filament::modal id="modal-attach-file" width="3xl" :close-by-escaping="true" :close-button="true">
        <x-slot:heading>Attach File</x-slot:heading>
        <livewire:Akreditasi.Documents.AttachTo :$kegiatan_id :docSelectedId="$selectedDocId" :key="'doc-attach-to-' . $selectedDocId" />
    </x-filament::modal>
</div>
