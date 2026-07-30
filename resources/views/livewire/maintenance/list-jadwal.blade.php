<div>
    {{ $this->table }}

    {{-- :close-button="false" --}}
    <x-filament::modal id="modal-maintenance-work" width="max-w-full" :close-by-clicking-away="false" :close-by-escaping="false"
        x-on:close-modal.window="
            if ($event.detail.id === 'modal-maintenance-work') {    
                // Dispatch custom Alpine event
                $dispatch('maintenance-work-updated');
            }
        ">
        <x-slot:heading>
            <span class="text-lg font-semibold">Maintenance Work </span>
        </x-slot:heading>

        <livewire:maintenance.work.index :jadwalId="$selectedId" :key="'work-' . Str::random()" />
    </x-filament::modal>



    <x-filament::modal id="modal-maintenance-work-report" width="max-w-6xl" :close-by-clicking-away="false" :close-by-escaping="false">
        <x-slot:heading>
            <span class="text-lg font-semibold">Report Maintenance
                <span class="text-gray-500">#{{ $selectedId }}</span>
            </span>
        </x-slot:heading>
        <livewire:Maintenance.Work.Report :jadwalId="$selectedId" :key="'work-report-' . Str::random()" />
    </x-filament::modal>
</div>
