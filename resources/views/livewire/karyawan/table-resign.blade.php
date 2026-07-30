<div>
    {{ $this->table }}


    {{-- Modal print cv karyawan --}}
    <x-filament::modal id="modal-print-cv" width="5xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot name="heading">
            CV Karyawan
        </x-slot>
        <div class="flex w-full">
            <livewire:Karyawan.PrintCv :$karyawanId :key="$karyawanId" />
        </div>
    </x-filament::modal>




    {{-- modal history --}}
    <x-filament::modal id="history-jabatan" width="lg">
        <x-slot name="heading">
            Histori Jabatan
        </x-slot>
        <div class="flex w-full">
            <livewire:Karyawan.HistoryJabatan :$karyawanId :key="Str::random()" />
        </div>
    </x-filament::modal>
</div>
