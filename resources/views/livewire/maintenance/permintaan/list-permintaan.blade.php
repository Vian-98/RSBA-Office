<div>
    {{ $this->table }}


    {{-- modal approval --}}
    <x-filament::modal id="modal-apporoval-permintaan" width="max-w-4xl" :autofocus="false" :close-by-clicking-away="false" :close-by-escaping="false">
        <x-slot:heading>Persetujuan Permintaan</x-slot:heading>
        <livewire:Maintenance.Permintaan.PermintaanApproval :$maintenanceRequest :key="Str::random()" @submit-approval-beli-request="$refresh" />
    </x-filament::modal>
</div>
