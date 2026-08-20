{{-- SIDE KIRI: Form Pengisian Identitas & Metadata (Width: 38%) --}}
<div style="flex: 0 0 38%; width: 38%; min-width: 320px;" class="space-y-5">
    <div class="border-b border-slate-100 pb-3">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas & Informasi Surat</h3>
        <p class="text-xs text-slate-400">Lengkapi metadata sebelum penandatanganan</p>
    </div>

    {{-- Stamp Resizer Quick Controller in Sidebar --}}
    <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-2.5">
        <div class="flex items-center justify-between text-xs font-bold text-indigo-900 uppercase tracking-wider">
            <span>Ukuran Stempel QR Code (Scale)</span>
            <span class="font-mono text-indigo-700 font-bold" x-text="scale + '%'"></span>
        </div>
        <div class="flex items-center space-x-3">
            <input type="range" min="50" max="180" step="5" x-model="scale" class="w-full accent-indigo-600 h-2 bg-indigo-200/80 rounded-lg cursor-pointer" />
        </div>
        <div class="flex justify-between gap-1 text-[11px]">
            <button type="button" @click="scale = 65" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Kecil (65%)</button>
            <button type="button" @click="scale = 100" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Normal (100%)</button>
            <button type="button" @click="scale = 135" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Besar (135%)</button>
        </div>
    </div>

    {{-- Judul Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul / Nama Surat</label>
        <input type="text" wire:model="title" placeholder="Judul Surat..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
        @error('title') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Nomor Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Surat</label>
        <input type="text" wire:model="document_number" placeholder="Nomor Surat..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono text-slate-800 bg-white" />
        @error('document_number') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Jenis / Kategori Arsip Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Jenis / Kategori Arsip Surat <span class="text-rose-500">*</span>
        </label>
        <select 
            wire:model="document_type" 
            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white cursor-pointer font-medium"
        >
            @if(isset($kategoriList) && count($kategoriList) > 0)
                @foreach($kategoriList as $cat)
                    <option value="{{ $cat->kode }}">
                        {{ $cat->nama }}
                    </option>
                @endforeach
            @else
                <option value="file_text">📄 File Text / Umum</option>
            @endif
        </select>
        <span class="text-[11px] text-slate-400 mt-1 block">Pilih kategori agar terarsip otomatis secara terdata di Bank Surat.</span>
        @error('document_type') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Keterangan --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
        <textarea wire:model="keterangan" rows="3" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
    </div>

    {{-- Submit Button --}}
    <div class="pt-4 w-full">
        <button 
            type="submit" 
            wire:loading.attr="disabled"
            wire:target="openPasswordModal"
            class="w-full py-3.5 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl shadow-sm transition-all flex items-center justify-center space-x-2 text-base cursor-pointer disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="openPasswordModal">Tanda Tangan</span>
            <span wire:loading.flex wire:target="openPasswordModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyiapkan Modal Konfirmasi...</span>
            </span>
        </button>
    </div>
</div>
