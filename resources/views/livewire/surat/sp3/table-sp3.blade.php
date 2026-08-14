<div class="w-full">
    {!! $this->table->toHtml() !!}

    <x-filament::modal id="modal-detail-sp3" width="max-w-4xl" :autofocus="false">
        <x-slot:heading>SP3</x-slot:heading>
        <livewire:Surat.Sp3.Details :$suratSp3 :key="Str::random(5)" />
    </x-filament::modal>


    <x-filament::modal id="modal-verifikasi-keuangan-sp3" width="4xl" :autofocus="false" :close-by-clicking-away="false" x-on:update-approval="$dispatch('close-modal',{id:'modal-verifikasi-keuangan-sp3'})">
        <x-slot:heading>Verifikasi Keuangan SP3</x-slot:heading>
        <livewire:Surat.Sp3.VerifikasiKeuangan :$suratSp3 :key="Str::random(5)" />
    </x-filament::modal>

    <x-filament::modal id="modal-approval-sp3" width="4xl" :autofocus="false" :close-by-clicking-away="false" x-on:update-approval="$dispatch('close-modal',{id:'modal-approval-sp3'})">
        <x-slot:heading>Persetujuan SP3</x-slot:heading>
        <livewire:Surat.Sp3.Approval :$suratSp3 :key="Str::random(5)" />
    </x-filament::modal>

    <x-filament::modal id="modal-edit-sp3" width="max-w-4xl" :autofocus="false" :close-by-clicking-away="false" x-on:update-approval="$dispatch('close-modal',{id:'modal-edit-sp3'})">
        <x-slot:heading>Edit & Ajukan Ulang SP3</x-slot:heading>
        <livewire:Surat.Sp3.Edit :$suratSp3 :key="Str::random(5)" />
    </x-filament::modal>
</div>


