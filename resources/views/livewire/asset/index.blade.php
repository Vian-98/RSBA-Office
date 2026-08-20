<div class="flex w-full flex-col gap-2">

    <div class="flex flex-row justify-between rounded-lg bg-white p-4">
        <div class="relative w-3/4 lg:w-1/3">
            <input id="search-asset" placeholder="Cari Barang..." type="text" class="h-8 w-full rounded-lg border-gray-200 px-10 transition-all duration-300 focus:outline-none" autocomplete="off" />

            <!-- Icon (Search) -->
            <x-ts:icon name="tabler.scan" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
        </div>

    </div>

    <div class="w-full rounded-lg bg-white p-4">
        <livewire:Asset.TableAsset :key="Str::random()" />
    </div>

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
