<div>
    <span class="text-sm italic text-indigo-500">Riwayat Maintenance</span>
    <div class="w-full">
        {{ $this->table }}
    </div>


    <x-filament::modal id="modal-maintenance-work-add" width="max-w-6xl" :close-by-clicking-away="false" :close-by-escaping="false">
        <x-slot:heading>Maintenance</x-slot:heading>
        <livewire:Maintenance.Work.Add :assetId="$assetBarang?->id" :workId="$selectedId" :key="'maintenance-work-' . Str::random()" />
    </x-filament::modal>


    <x-filament::modal id="modal-maintenance-work-report-on-list-riwayat" width="max-w-6xl" :close-by-clicking-away="false" :close-by-escaping="false">
        <x-slot:heading>
            <span class="text-lg font-semibold">Report Maintenance
                <span class="text-gray-500">#{{ $jadwalIdSelected }}</span>
            </span>
        </x-slot:heading>
        <livewire:Maintenance.Work.Report :jadwalId="$jadwalIdSelected" :key="'work-report-in-list' . Str::random()" />
    </x-filament::modal>
</div>
