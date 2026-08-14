<div class="flex flex-col gap-3">
    <div class="w-full overflow-visible rounded-xl bg-white p-4 shadow-sm border border-slate-100">
        {{-- Header: Judul + Tombol Aksi + Filter --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <x-tabler-school class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Surat Balasan PKL</h2>
                    <p class="text-xs text-slate-400">Pemberian Izin Praktik Kerja Lapangan & Rincian Biaya</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Tombol Pengaturan Tarif & Template --}}
                <x-ts:button sm outline color="secondary" icon="tabler.settings" x-on:click="$dispatch('open-modal',{id:'modal-konfig-tarif-pkl'})">
                    Pengaturan Tarif & Format
                </x-ts:button>

                {{-- Tombol Tambah Surat --}}
                <x-ts:button sm color="primary" icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-balasan-pkl'})">
                    Buat Surat Baru
                </x-ts:button>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div>
            <livewire:Surat.BalasanPkl.TableBalasanPkl :key="Str::random()" />
        </div>
    </div>

    {{-- Modal Konfigurasi Tarif & Template Nomor --}}
    <x-filament::modal id="modal-konfig-tarif-pkl" width="max-w-xl" :autofocus="false">
        <x-slot:heading>Pengaturan Tarif & Format Nomor PKL</x-slot:heading>
        <livewire:Surat.BalasanPkl.KonfigTarif :key="Str::random(5)" />
    </x-filament::modal>

    {{-- Modal Tambah Surat --}}
    <x-filament::modal id="modal-add-balasan-pkl" width="max-w-3xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Buat Surat Balasan PKL Baru</x-slot:heading>
        <livewire:Surat.BalasanPkl.Add :key="Str::random(5)" />
    </x-filament::modal>
</div>
