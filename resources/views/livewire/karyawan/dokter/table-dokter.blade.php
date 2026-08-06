<div>
    {{ $this->table }}


    {{-- Modal new dokter --}}
    <x-filament::modal id="new-dokter" width="xl" :autofocus="false" :close-by-clicking-away='false'>
        <x-slot name="heading">
            Tambah Dokter Baru
        </x-slot>
        <livewire:Karyawan.Dokter.Add :key="Str::random()" @new-dokter-created="$refresh" />
    </x-filament::modal>

    {{-- Modal Atur Ruangan Koordinasi Dokter --}}
    {{-- Hanya bisa dibuka oleh Super-Admin / Staff-SDM / Kepala-Bidang / Wakil-Direktur --}}
    <x-filament::modal id="modal-koor-ruangan" width="lg" :autofocus="false" :close-by-clicking-away='false'>
        <x-slot name="heading">
            Atur Ruangan Koordinasi Dokter
        </x-slot>
        <x-slot name="description">
            Pilih ruangan yang dapat dikelola jadwal dokternya oleh koordinator ini.
        </x-slot>
        <livewire:Karyawan.Dokter.KoorRuangan @koor-ruangan-updated="$dispatch('close-modal', {id: 'modal-koor-ruangan'})" />
    </x-filament::modal>
</div>
