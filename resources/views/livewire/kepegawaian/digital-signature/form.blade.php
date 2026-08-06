<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 space-y-6">
    <div class="flex items-center space-x-3 border-b border-slate-100 pb-5">
        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Form Unggah & Sign Surat PDF</h2>
            <p class="text-xs text-slate-500">Unggah dokumen PDF. Konfirmasi password akun akan diminta melalui popup saat pengiriman ke Docstore</p>
        </div>
    </div>

    <form wire:submit.prevent="openPasswordModal" class="space-y-6 w-full">
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

        {{-- Keterangan --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
            <textarea wire:model="keterangan" rows="3" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
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
                    <span>Memvalidasi Form...</span>
                </span>
            </button>
        </div>
    </form>

    {{-- POPUP MODAL VERIFIKASI PASSWORD AKUN --}}
    @if ($showPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Konfirmasi Password Akun</h3>
                            <p class="text-xs text-slate-500">Verifikasi Pengirim Dokumen</p>
                        </div>
                    </div>
                    <button wire:click="closePasswordModal" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Pengirim:</span>
                        <span class="font-bold text-slate-800">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Email / Account:</span>
                        <span class="font-mono text-indigo-600 font-semibold">{{ Auth::user()->email }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Masukkan Password Akun Anda</label>
                    <input 
                        type="password" 
                        wire:model="account_password" 
                        wire:keydown.enter="confirmAndSign"
                        placeholder="Password akun..." 
                        autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" 
                    />
                    @error('account_password') 
                        <span class="text-xs text-rose-500 mt-1.5 block font-semibold bg-rose-50 p-2 rounded-lg border border-rose-200">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button wire:click="closePasswordModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button wire:click="confirmAndSign" wire:loading.attr="disabled" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/25 flex items-center space-x-2 transition-all">
                        <span wire:loading.remove>Konfirmasi Sign & Kirim</span>
                        <span wire:loading class="flex items-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
