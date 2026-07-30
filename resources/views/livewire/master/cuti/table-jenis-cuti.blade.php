<div>
    {{ $this->table }}

    <x-filament::modal id="modal-edit-jenis-cuti">
        <x-slot:heading>{{ $cuti_jenis?->nama }}</x-slot:heading>

        <livewire:Master.Cuti.Edit :$cuti_jenis :key="Str::random(5)" @updated="$refresh" />
    </x-filament::modal>
</div>
