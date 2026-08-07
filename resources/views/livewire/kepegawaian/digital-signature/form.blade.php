<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 space-y-6">
    {{-- Mekari Sign Header & Workflow Stepper --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white rounded-xl shadow-md shadow-indigo-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Studio Tanda Tangan Digital (Mekari Sign Workflow)</h2>
                <p class="text-xs text-slate-500">Isi identitas di sisi kiri, geser & atur ukuran stempel pada pratinjau PDF di sisi kanan</p>
            </div>
        </div>

        {{-- Stepper Badges --}}
        <div class="flex items-center space-x-2 text-xs">
            <span class="px-3.5 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-indigo-600 text-white shadow-sm' }}">
                1. Unggah PDF
            </span>
            <span class="text-slate-300">➔</span>
            <span class="px-3.5 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
                2. Identitas, Stempel & Ukuran
            </span>
            <span class="text-slate-300">➔</span>
            <span class="px-3.5 py-2 rounded-xl font-bold bg-slate-100 text-slate-400">
                3. Password & Sign
            </span>
        </div>
    </div>

    <form wire:submit.prevent="openPasswordModal" class="space-y-6 w-full">
        {{-- STEP 1: PDF Upload Dropzone --}}
        <div class="w-full">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih / Ganti Berkas PDF</label>
                @if ($pdf_file)
                    <span class="text-xs text-emerald-600 font-bold flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Berkas PDF Terverifikasi
                    </span>
                @endif
            </div>

            <div class="relative border-2 border-dashed {{ $pdf_file ? 'border-indigo-400 bg-indigo-50/20' : 'border-slate-300 bg-slate-50/60' }} hover:border-indigo-500 rounded-2xl p-5 text-center transition-all group cursor-pointer w-full">
                <input type="file" wire:model="pdf_file" accept="application/pdf,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="text-sm font-bold text-slate-800">
                                @if ($pdf_file)
                                    <span class="text-indigo-600">{{ $pdf_file->getClientOriginalName() }}</span>
                                @else
                                    <span>Klik untuk unggah berkas PDF</span> atau seret berkas ke sini
                                @endif
                            </div>
                            <p class="text-xs text-slate-400">Format .PDF (Ukuran Maksimal 10 MB)</p>
                        </div>
                    </div>

                    @if ($pdf_file)
                        <div class="text-right text-xs">
                            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full font-mono bg-indigo-100 text-indigo-800 font-semibold">
                                {{ $fileSizeFormatted }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            @error('pdf_file') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
        </div>

        {{-- STEP 2: STRICT GUARANTEED FLEX SIDE-BY-SIDE LAYOUT --}}
        @if ($pdf_file && $previewPdfBase64)
            <div 
                x-data="{
                    posX: @entangle('stamp_x'),
                    posY: @entangle('stamp_y'),
                    scale: @entangle('stamp_scale'),
                    isDragging: false,
                    grabOffsetX: 0,
                    grabOffsetY: 0,

                    startDrag(e) {
                        this.isDragging = true;
                        const stampRect = $refs.stampBadge.getBoundingClientRect();
                        this.grabOffsetX = e.clientX - stampRect.left;
                        this.grabOffsetY = e.clientY - stampRect.top;
                    },

                    onDrag(e) {
                        if (!this.isDragging) return;
                        const canvasRect = $refs.canvasBox.getBoundingClientRect();
                        
                        let leftPx = e.clientX - canvasRect.left - this.grabOffsetX;
                        let topPx = e.clientY - canvasRect.top - this.grabOffsetY;
                        
                        let pctX = (leftPx / canvasRect.width) * 100;
                        let pctY = (topPx / canvasRect.height) * 100;

                        this.posX = Math.max(0, Math.min(75, Math.round(pctX)));
                        this.posY = Math.max(0, Math.min(85, Math.round(pctY)));
                    },

                    stopDrag() {
                        this.isDragging = false;
                    }
                }"
                @mousemove.window="onDrag($event)"
                @mouseup.window="stopDrag()"
                style="display: flex; flex-wrap: nowrap; gap: 24px; width: 100%; align-items: flex-start; border-top: 1px solid #f1f5f9; padding-top: 24px;"
            >
                {{-- SIDE KIRI: Form Pengisian Identitas & Metadata (Width: 38%) --}}
                <div style="flex: 0 0 38%; width: 38%; min-width: 320px;" class="space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas & Informasi Surat</h3>
                        <p class="text-xs text-slate-400">Lengkapi metadata sebelum penandatanganan</p>
                    </div>

                    {{-- ByteCounter SHA-256 Box --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>ByteCounter SHA-256</span>
                            <span class="text-[10px] text-emerald-600 font-mono font-bold">AUTHENTIC</span>
                        </div>
                        <div class="font-mono text-xs text-indigo-900 bg-white p-2.5 rounded-xl border border-slate-200 break-all">
                            {{ $fileHashSHA256 }}
                        </div>
                    </div>

                    {{-- Stamp Resizer Quick Controller in Sidebar --}}
                    <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-bold text-indigo-900 uppercase tracking-wider">
                            <span>Ukuran Stempel (Scale)</span>
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

                    {{-- Keterangan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
                        <textarea wire:model="keterangan" rows="3" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4 w-full">
                        <button type="submit" wire:loading.attr="disabled" class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:bg-indigo-900 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center space-x-2 text-base">
                            <span wire:loading.remove>Sign & Kirim ke Docstore (Mekari e-Sign)</span>
                            <span wire:loading class="flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyiapkan Modal Konfirmasi...</span>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- SIDE KANAN: Extra-Tall & Wide PDF Preview Canvas (Width: 60%) --}}
                <div style="flex: 1 1 60%; width: 60%; min-width: 480px;" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Pratinjau PDF (Side Kanan)
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            🖐️ Hold Click & Resize Active
                        </span>
                    </div>

                    {{-- High-Resolution Workstation Canvas with Explicit Height (850px) --}}
                    <div 
                        x-ref="canvasBox"
                        style="position: relative; width: 100%; height: 850px; min-height: 850px; background-color: #e2e8f0; padding: 12px; border-radius: 24px; border: 2px solid #cbd5e1; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; user-select: none;"
                    >
                        {{-- Full-height PDF Object / Iframe Viewer --}}
                        <object 
                            data="{{ $previewPdfBase64 }}#view=FitH&toolbar=0&navpanes=0" 
                            type="application/pdf" 
                            style="width: 100%; height: 100%; min-height: 820px; border-radius: 16px; border: none; background: white;"
                        >
                            <iframe src="{{ $previewPdfBase64 }}#view=FitH&toolbar=0&navpanes=0" style="width: 100%; height: 100%; min-height: 820px; border-radius: 16px; border: none; background: white;"></iframe>
                        </object>

                        {{-- Manual Draggable & Resizable Mekari Vault Seal Stamp Overlay --}}
                        <div 
                            x-ref="stampBadge"
                            @mousedown.prevent="startDrag($event)"
                            :style="`left: ${posX}%; top: ${posY}%; transform: scale(${scale / 100}); transform-origin: top left;`"
                            class="absolute z-30 cursor-grab active:cursor-grabbing select-none transition-transform duration-75"
                        >
                            <div class="bg-white/95 backdrop-blur-md p-3.5 rounded-2xl border-2 border-emerald-500 shadow-2xl text-left max-w-xs ring-4 ring-emerald-500/20 hover:ring-emerald-500/40 transition-all select-none">
                                {{-- Stamp Header with Interactive Resize Buttons --}}
                                <div class="flex items-center justify-between border-b border-emerald-100 pb-1.5 mb-1.5">
                                    <div class="flex items-center space-x-1.5 pointer-events-none">
                                        <div class="w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center text-white shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <span class="text-[9px] font-black uppercase text-emerald-800 tracking-wider">MEKARI VAULT</span>
                                    </div>
                                    
                                    {{-- Interactive Resize Controls Directly on Stamp --}}
                                    <div class="flex items-center space-x-1 bg-emerald-50 rounded-lg p-0.5 border border-emerald-200">
                                        <button type="button" @click.stop="scale = Math.max(50, parseInt(scale) - 10)" class="w-4 h-4 rounded bg-white hover:bg-emerald-200 active:bg-emerald-300 text-[10px] font-black text-emerald-800 flex items-center justify-center shadow-xs transition-colors">
                                            -
                                        </button>
                                        <span class="text-[8px] font-mono font-bold text-emerald-900 px-1 select-none" x-text="scale + '%'"></span>
                                        <button type="button" @click.stop="scale = Math.min(180, parseInt(scale) + 10)" class="w-4 h-4 rounded bg-white hover:bg-emerald-200 active:bg-emerald-300 text-[10px] font-black text-emerald-800 flex items-center justify-center shadow-xs transition-colors">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <div class="text-xs font-bold text-slate-800 leading-tight pointer-events-none">{{ Auth::user()->name }}</div>
                                <div class="text-[9px] text-slate-500 font-mono mt-0.5 pointer-events-none">{{ date('d M Y H:i') }} WIB</div>
                                <div class="text-[8px] font-mono text-indigo-700 truncate mt-1 bg-indigo-50 px-1.5 py-0.5 rounded pointer-events-none">
                                    SHA: {{ substr($fileHashSHA256, 0, 18) }}...
                                </div>
                                <div class="text-[8px] text-slate-400 text-center border-t border-slate-100 pt-1 mt-1 font-sans pointer-events-none">
                                    🖐️ Klik & geser | gunakan [-] [+] untuk ukuran
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </form>

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
                    {{-- Hidden username field to prevent browser autofill from hijacking the sidebar search input --}}
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
</div>
