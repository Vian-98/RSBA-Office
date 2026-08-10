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
            data="{{ $this->previewPdfUrl }}#view=FitH&toolbar=0&navpanes=0" 
            type="application/pdf" 
            style="width: 100%; height: 100%; min-height: 820px; border-radius: 16px; border: none; background: white;"
        >
            <iframe src="{{ $this->previewPdfUrl }}#view=FitH&toolbar=0&navpanes=0" style="width: 100%; height: 100%; min-height: 820px; border-radius: 16px; border: none; background: white;"></iframe>
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
