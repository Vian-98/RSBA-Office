<div class="w-full">
    {{ $this->table }}


    <x-filament::modal id="modal-edit-rician-jasmed" width="7xl" class="max-h-screen overflow-auto">
        <x-slot:heading>Rincian Billing</x-slot:heading>
        <livewire:Jasmed.Verify.Rincian :id="$selectedId" :key="'edit-rincian-' . $selectedId" />
    </x-filament::modal>

    <x-filament::modal id="modal-edit-dokter" width="4xl" class="max-h-screen overflow-auto">
        <x-slot:heading>Dokter</x-slot:heading>
        <livewire:Jasmed.Dokter.Edit :id="$selectedId" :key="'edit-dokter-' . $selectedId" />
    </x-filament::modal>
</div>
