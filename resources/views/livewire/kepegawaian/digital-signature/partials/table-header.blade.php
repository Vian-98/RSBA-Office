<div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 w-full">
    <div>
        <h2 class="text-base font-bold text-slate-900 whitespace-nowrap">
            Daftar Surat Ter-Sign
        </h2>
    </div>
    <div class="w-full md:w-80">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Cari judul, nomor, atau ID docstore..." 
            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
        />
    </div>
</div>
