<div class="w-full">
    {!! $this->table->toHtml() !!}

    <x-filament::modal id="modal-detail-perintah-tugas" width="max-w-4xl" :autofocus="false">
        <x-slot:heading>Detail Surat Perintah Tugas</x-slot:heading>
        <livewire:Surat.PerintahTugas.Details :$suratPerintahTugas :key="Str::random(5)" />
    </x-filament::modal>

    <x-filament::modal id="modal-approval-perintah-tugas" width="max-w-md" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Persetujuan Direktur — Surat Perintah Tugas</x-slot:heading>
        <livewire:Surat.PerintahTugas.Approval :$suratPerintahTugas :key="Str::random(5)" />
    </x-filament::modal>
</div>
