<div>
    {{ $this->table }}


    {{-- Modal new karyawan --}}
    <x-filament::modal id="new-karyawan" width="5xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot name="heading">
            Karyawan Baru
        </x-slot>
        <livewire:Karyawan.add lazy @new-karyawan-created="$refresh" />
    </x-filament::modal>


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


    <x-filament::modal id="import-karyawan" :autofocus="false" :close-by-clicking-away="false">
        <x-slot name="heading" class="text-indigo-500">
            Import Karyawan
        </x-slot>
        <div class="flex w-full">
            <livewire:Karyawan.ImportKaryawan @karyawan-imported="$refresh" />
        </div>
    </x-filament::modal>
</div>
