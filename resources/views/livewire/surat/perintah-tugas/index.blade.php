<div class="flex flex-col gap-3">
    <div class="w-full overflow-visible rounded-xl bg-white p-4 shadow-sm border border-slate-100">
        {{-- Header: Judul + Tombol Aksi + Filter --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 border border-amber-100">
                    <x-tabler-clipboard-list class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Surat Perintah Tugas (SPT)</h2>
                    <p class="text-xs text-slate-400">Penugasan Resmi Karyawan RS Bintang Amin</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Tombol Pengaturan Format Nomor --}}
                <x-ts:button sm outline color="secondary" icon="tabler.settings" x-on:click="$dispatch('open-modal',{id:'modal-konfig-format-spt'})">
                    Format Penomoran
                </x-ts:button>

                {{-- Tombol Tambah Surat --}}
                <x-ts:button sm color="primary" icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-perintah-tugas'})">
                    Buat SPT Baru
                </x-ts:button>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div>
            <livewire:Surat.PerintahTugas.TablePerintahTugas :key="Str::random()" />
        </div>
    </div>

    {{-- Modal Konfigurasi Template Nomor --}}
    <x-filament::modal id="modal-konfig-format-spt" width="max-w-md" :autofocus="false">
        <x-slot:heading>Pengaturan Format Nomor Surat Perintah Tugas</x-slot:heading>
        <livewire:Surat.PerintahTugas.KonfigFormat :key="Str::random(5)" />
    </x-filament::modal>

    {{-- Modal Tambah Surat --}}
    <x-filament::modal id="modal-add-perintah-tugas" width="max-w-3xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot:heading>Buat Surat Perintah Tugas (SPT) Baru</x-slot:heading>
        <livewire:Surat.PerintahTugas.Add :key="Str::random(5)" />
    </x-filament::modal>
</div>
