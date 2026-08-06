<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 space-y-6">
    <div class="flex items-center space-x-3 border-b border-slate-100 pb-5">
        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Form Unggah & Sign Surat PDF</h2>
            <p class="text-xs text-slate-500">Berkas PDF yang berhasil di-sign akan ter-sync ke docstore dan otomatis dibersihkan dari server lokal</p>
        </div>
    </div>

    <form wire:submit.prevent="saveAndSign" class="space-y-6 w-full">
        {{-- Full Width Drag and Drop PDF File Input --}}
        <div class="w-full">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unggah Berkas Surat (PDF strictly)</label>
            <div class="relative border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-10 text-center transition-all bg-slate-50/60 group cursor-pointer w-full">
                <input type="file" wire:model="pdf_file" accept="application/pdf,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                <div class="space-y-3">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-base text-slate-700">
                        @if ($pdf_file)
                            <span class="font-bold text-indigo-600 block text-lg">{{ $pdf_file->getClientOriginalName() }}</span>
                        @else
                            <span class="font-bold text-indigo-600">Klik untuk upload berkas</span> atau seret berkas PDF ke area ini
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Hanya menerima format <strong>.PDF</strong> (Ukuran Maksimal 10 MB)</p>
                </div>
            </div>
            @error('pdf_file') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
        </div>

        {{-- 2 Columns Grid filling 100% width --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
            {{-- Judul Surat --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul / Nama Surat</label>
                <input type="text" wire:model="title" placeholder="Contoh: Surat Keputusan Direksi Nomer 102" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
                @error('title') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Nomor Surat --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Surat</label>
                <input type="text" wire:model="document_number" placeholder="Nomor Surat..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono text-slate-800 bg-white" />
                @error('document_number') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- 2 Columns Grid for Keterangan & Passphrase filling 100% width --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
            {{-- Keterangan --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
                <textarea wire:model="keterangan" rows="3" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
            </div>

            {{-- Passphrase Sertifikat --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Passphrase Sertifikat Digital (Opsional)</label>
                <input type="password" wire:model="passphrase" placeholder="Masukkan passphrase sertifikat jika ada..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
            </div>
        </div>

        {{-- Full Width Submit Button --}}
        <div class="pt-4 w-full">
            <button type="submit" wire:loading.attr="disabled" class="w-full py-4 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center space-x-2 text-base">
                <span wire:loading.remove>Sign & Kirim ke Docstore</span>
                <span wire:loading class="flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses Signature & ByteCounter...</span>
                </span>
            </button>
        </div>
    </form>
</div>
