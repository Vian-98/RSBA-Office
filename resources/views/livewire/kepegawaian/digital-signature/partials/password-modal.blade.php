
{{-- MEKARI SIGN SECURITY POPUP MODAL --}}
@if ($showPasswordModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Konfirmasi Tanda Tangan (Mekari Vault)</h3>
                        <p class="text-xs text-slate-500">Verifikasi Keaslian Pengirim & Password Akun</p>
                    </div>
                </div>
                <button wire:click="closePasswordModal" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            {{-- Document Summary Card --}}
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Judul Dokumen:</span>
                    <span class="font-bold text-slate-800 truncate max-w-[220px]">{{ $title }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Nomor Surat:</span>
                    <span class="font-mono text-slate-800 font-semibold">{{ $document_number }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Penandatangan:</span>
                    <span class="font-bold text-slate-800">{{ Auth::user()->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Skala Stempel:</span>
                    <span class="font-mono font-semibold text-indigo-600">{{ $stamp_scale }}%</span>
                </div>
                <div class="border-t border-slate-200 pt-2 mt-1">
                    <span class="text-slate-500 block">SHA-256 Checksum:</span>
                    <span class="font-mono text-[10px] text-indigo-900 break-all block mt-0.5">{{ $fileHashSHA256 }}</span>
                </div>
            </div>

            <div>
                {{-- Hidden username field to prevent browser autofill --}}
                <input type="text" name="username" value="{{ Auth::user()->email ?? 'user' }}" autocomplete="username" class="sr-only hidden" style="display: none !important;" readonly tabIndex="-1" />

                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Masukkan Password Akun Anda</label>
                <input 
                    type="password" 
                    name="account_password"
                    id="account_password_input"
                    autocomplete="current-password"
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
                <button wire:click="confirmAndSign" wire:loading.attr="disabled" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/25 flex items-center space-x-2 transition-all">
                    <span wire:loading.remove>Konfirmasi Sign & Kirim</span>
                    <span wire:loading class="flex items-center space-x-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses Signature...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
