<div>
    {{ $this->table }}


    <x-filament::modal id="modal-edit-spesialisasi" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Edit <span class="text-primary-500 font-semibold">{{ $spesialisasi?->nama }}</span>
        </x-slot:heading>

        <livewire:Master.Spesialisasi.Edit :id="$spesialisasi?->id" :key="$spesialisasi?->id" @spesialisasi-updated="$refresh" />
    </x-filament::modal>
</div>
