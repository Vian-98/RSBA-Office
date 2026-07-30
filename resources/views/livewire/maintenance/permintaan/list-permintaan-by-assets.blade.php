<div class="w-full">
    {{-- <x-table-static :headers="$this->headers()" :rows="$this->rows()" striped></x-table-static> --}}
    {{ $this->table }}



    <x-filament::modal id="modal-maintenance-work-report" width="max-w-6xl" :close-by-clicking-away="false" :close-by-escaping="false">
        <x-slot:heading>
            <span class="text-lg font-semibold">Report Maintenance
                <span class="text-gray-500">#{{ $jadwalIdSelected }}</span>
            </span>
        </x-slot:heading>
        <livewire:Maintenance.Work.Report :jadwalId="$jadwalIdSelected" :key="'work-report-' . Str::random()" />
    </x-filament::modal>
</div>
