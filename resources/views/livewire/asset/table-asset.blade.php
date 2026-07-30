<div>
    {{ $this->table }}


    {{-- modal action table --}}
    {{-- catat asset --}}
    <x-filament::modal id="modal-catat-asset" :close-by-clicking-away="false">
        <x-slot:heading>Catat Asset</x-slot:heading>
        <livewire:Asset.Catat :id="$selectedId" :key="Str::random()" @new-asset-created="$refresh" />
    </x-filament::modal>

    {{-- modal asset --}}
    <x-filament::modal id="modal-asset-details" width="7xl">
        <x-slot:heading>Asset Details</x-slot:heading>
        <livewire:Asset.Details :id="$selectedId" :key="Str::random()" />
    </x-filament::modal>

    {{-- kelengkapan data asset --}}
    <x-filament::modal id="modal-asset-specs" width="max-w-3xl">
        <x-slot:heading>Asset Spesification</x-slot:heading>
        <livewire:Asset.Specs :id="$selectedId" :key="Str::random()" />
    </x-filament::modal>


    {{-- permintaan maintenance asset --}}
    <x-filament::modal id="modal-maintenance-asset" width="max-w-3xl" :close-by-clicking-away="false">
        <x-slot:heading>
            <div class="flex flex-row items-center gap-2">
                <x-tabler-device-imac-cog class="size-5" />
                Permintaan Maintenance
            </div>
        </x-slot:heading>
        <livewire:Maintenance.Permintaan.Add :id="$selectedId" :key="Str::random()" @maintenance-request-created="$refresh" />
    </x-filament::modal>


    {{-- Modal Mutasi Asset --}}
    <x-filament::modal id="modal-mutasi-asset" width="max-w-3xl">
        <x-slot:heading>Mutasi Asset</x-slot:heading>
        <livewire:Asset.Mutasi :id="$selectedId" :key="Str::random()" @mutasi-asset-saved="$refresh" />
    </x-filament::modal>



    {{-- Modal Logs asset --}}
    <x-filament::modal id="modal-logs-asset" width="max-w-3xl">
        <x-slot:heading>Logs</x-slot:heading>
        <livewire:Asset.Logs :id="$selectedId" :key="Str::random()" />
    </x-filament::modal>


    {{-- Modal Status Asset --}}
    <x-filament::modal id="modal-maintenance-status">
        <x-slot:heading>Status Maintenance</x-slot:heading>
        <livewire:Maintenance.LogsStatus :assetId="$selectedId" :key="'status-' . Str::random()" />

    </x-filament::modal>


    @isset($selectedId)
        <div class="hidden" id="print-label-area" x-on:print-label.window="printArea('print-label-area')">
            <livewire:Asset.PrintLabel :id="$selectedId" :key="'print-' . $selectedId" />
        </div>
    @endisset


</div>
