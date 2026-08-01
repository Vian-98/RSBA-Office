<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">
                <x-ts:icon name="tabler.building-store" class="h-3.5 w-3.5 text-indigo-600" />
                Master Data Vendor & Rekanan
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Supplier & Rekanan RS</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                Kelola daftar mitra vendor distributor obat, alat kesehatan, dan Bahan Medis Habis Pakai (BMHP).
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-ts:button sm icon="tabler.plus" class="py-2.5 px-4 font-bold" x-on:click="$dispatch('open-modal',{id:'modal-new-supplier'})">
                Tambah Rekanan Baru
            </x-ts:button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <livewire:Master.Supplier.TableSupplier :key="Str::random()" />
    </div>

    {{-- Modal Supplier --}}
    <x-filament::modal id="modal-new-supplier" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Tambah Rekanan / Supplier Baru</x-slot:heading>
        <livewire:Master.Supplier.Add :key="Str::random()" @new-supplier-created="$refresh" />
    </x-filament::modal>
</div>
