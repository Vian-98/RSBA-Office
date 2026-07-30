<div>
    {{ $this->table }}


    <x-filament::modal id="modal-view-document-ep-{{ $modalPreffix }}" width="screen" :close-by-escaping="true" :close-button="true">
        <x-slot:heading></x-slot:heading>

        <livewire:Akreditasi.Documents.View :$docSelectedId :key="'view-doc-on-table-ep' . $docSelectedId" />
    </x-filament::modal>


    <x-filament::modal id="modal-manage-document-ep-{{ $modalPreffix }}" width="4xl">
        <x-slot:heading>Manage Document</x-slot:heading>

        <livewire:Akreditasi.Documents.Index :elementId="$elementSelectedId" :key="'manage-doc-' . $elementSelectedId" />
    </x-filament::modal>
</div>
