{{-- Form Pencarian & Range Filter --}}
<div class="p-4 bg-white border-b border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
    {{-- Search Bar --}}
    <div class="md:col-span-2 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <x-tabler-search class="size-4" />
        </div>
        <input 
            wire:model.live.debounce.300ms="search" 
            type="text" 
            placeholder="Cari Nomor Surat / Docstore Key / Nama Terkait..." 
            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50"
        />
    </div>

    {{-- Status Filter --}}
    <div>
        <select wire:model.live="filterStatus" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50">
            <option value="all">Semua Status</option>
            <option value="approved">VALID / Approved</option>
            <option value="manual">DISETUJUI MANUAL</option>
            <option value="pending">PENDING</option>
            <option value="rejected">DITOLAK</option>
        </select>
    </div>

    {{-- Reset Filters Button --}}
    <div class="flex justify-end">
        <button wire:click="resetFilters" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors w-full md:w-auto justify-center cursor-pointer">
            <x-tabler-refresh class="size-3.5" />
            <span>Reset Filter</span>
        </button>
    </div>
</div>
