<div class="w-full">
    {!! $this->table->toHtml() !!}

    <x-filament::modal id="modal-detail-balasan-penelitian" width="max-w-4xl" :autofocus="false">
        <x-slot:heading>Detail Surat Balasan Presurvey & Penelitian</x-slot:heading>
        <livewire:Surat.BalasanPenelitian.Details :$suratBalasanPenelitian :key="Str::random(5)" />
    </x-filament::modal>

    <x-filament::modal id="modal-approval-balasan-penelitian" width="max-w-md" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Persetujuan Direktur — Surat Balasan Penelitian</x-slot:heading>
        <livewire:Surat.BalasanPenelitian.Approval :$suratBalasanPenelitian :key="Str::random(5)" />
    </x-filament::modal>
</div>
