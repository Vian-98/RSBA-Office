<div>
    {{ $this->table }}

    <x-filament::modal id="modal-approval-pengajuan-pembelian" width="5xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Approval Pengajuan Pengadaan</x-slot:heading>

        <livewire:Pembelian.Permintaan.Approval :$beliReqIdSelected :key="'approval-beli-' . $beliReqIdSelected" @submit-approval-beli-request="$refresh" />
    </x-filament::modal>


    <x-filament::modal id="modal-approval-pengajuan-report" width="4xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Status</x-slot:heading>

        <livewire:Pembelian.Permintaan.Report :$beliReqIdSelected :key="'permintaan-blei-report-' . $beliReqIdSelected" />
    </x-filament::modal>
</div>
