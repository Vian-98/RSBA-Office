<div>
    <div>
        {{ $this->table }}
    </div>

    {{-- modal --}}
    <x-filament::modal id="detail-distribusi" width="4xl">
        <x-slot:heading>Detail Distribusi</x-slot:heading>

        <livewire:Distribusi.ViewDetail :$distribusi :key="$distribusi?->id" />
    </x-filament::modal>
</div>
