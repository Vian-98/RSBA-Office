<div class="flex flex-col gap-3">
    <div class="w-full overflow-visible rounded-xl bg-white p-4 shadow-sm border border-slate-100">
        {{-- Header: Judul + Tombol Aksi + Filter --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="flex size-9 items-center justify-center rounded-lg bg-teal-50 text-teal-600 border border-teal-100">
                    <x-tabler-microscope class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Surat Balasan Presurvey & Penelitian</h2>
                    <p class="text-xs text-slate-400">Pemberian Izin Penelitian Mahasiswa / Dosen & Rincian Biaya</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Tombol Pengaturan Tarif & Template --}}
                <x-ts:button sm outline color="secondary" icon="tabler.settings" x-on:click="$dispatch('open-modal',{id:'modal-konfig-tarif-penelitian'})">
                    Pengaturan Tarif & Format
                </x-ts:button>

                {{-- Tombol Tambah Surat --}}
                <x-ts:button sm color="primary" icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-balasan-penelitian'})">
                    Buat Surat Baru
                </x-ts:button>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div>
            <livewire:Surat.BalasanPenelitian.TableBalasanPenelitian :key="Str::random()" />
        </div>
    </div>

    {{-- Modal Konfigurasi Tarif & Template Nomor --}}
    <x-filament::modal id="modal-konfig-tarif-penelitian" width="max-w-xl" :autofocus="false">
        <x-slot:heading>Pengaturan Tarif & Format Nomor Penelitian</x-slot:heading>
        <livewire:Surat.BalasanPenelitian.KonfigTarif :key="Str::random(5)" />
    </x-filament::modal>

    {{-- Modal Tambah Surat --}}
    <x-filament::modal id="modal-add-balasan-penelitian" width="max-w-3xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Buat Surat Balasan Presurvey / Penelitian Baru</x-slot:heading>
        <livewire:Surat.BalasanPenelitian.Add :key="Str::random(5)" />
    </x-filament::modal>
</div>
