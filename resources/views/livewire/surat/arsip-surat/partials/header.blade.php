{{-- Header Banner & Action Button --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
    <div>
        <h1 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">Arsip Surat Resmi</h1>
        <p class="text-xs text-slate-500 mt-0.5">Arsip terpusat dari seluruh jenis dokumen resmi RS Bintang Amin.</p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button wire:click="openModalKategori" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-2xs transition-all cursor-pointer">
            <x-tabler-plus class="size-4" />
            <span>Tambah Jenis Arsip Surat</span>
        </button>
    </div>
</div>
