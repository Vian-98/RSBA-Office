<div class="w-full">
    {!! $this->table->toHtml() !!}

    <x-filament::modal id="modal-detail-balasan-pkl" width="max-w-4xl" :autofocus="false">
        <x-slot:heading>Detail Surat Balasan PKL</x-slot:heading>
        <livewire:Surat.BalasanPkl.Details :$suratBalasanPkl :key="Str::random(5)" />
    </x-filament::modal>

    <x-filament::modal id="modal-approval-balasan-pkl" width="max-w-md" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Persetujuan Direktur — Surat Balasan PKL</x-slot:heading>
        <livewire:Surat.BalasanPkl.Approval :$suratBalasanPkl :key="Str::random(5)" />
    </x-filament::modal>
</div>
