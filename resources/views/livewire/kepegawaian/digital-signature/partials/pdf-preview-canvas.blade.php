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
            Hold Click & Resize Active
        </span>
    </div>

    {{-- High-Resolution Workstation Canvas with PDF.js Edge-to-Edge Rendering --}}
    <div 
        x-ref="canvasBox"
        style="position: relative; width: 100%; aspect-ratio: 1 / 1.414; background-color: #cbd5e1; padding: 0; border-radius: 20px; border: 2px solid #94a3b8; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; user-select: none;"
    >
        {{-- Processing / Uploading Overlay on Preview Canvas --}}
        <div 
            wire:loading.flex 
            wire:target="pdf_file" 
            class="absolute inset-0 z-40 bg-slate-900/60 backdrop-blur-xs flex flex-col items-center justify-center text-white space-y-3 p-6 text-center"
        >
            <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
                <svg class="animate-spin w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold tracking-wide">Merender Pratinjau Dokumen...</p>
                <p class="text-xs text-slate-300 mt-0.5">Membaca tata letak halaman PDF</p>
            </div>
        </div>

        {{-- PDF.js Canvas Rendering (100% Exact Edge-to-Edge PDF Page 1) --}}
        <canvas 
            x-ref="pdfCanvas" 
            wire:ignore
            style="width: 100%; height: 100%; display: block; border-radius: 18px; background: white;"
        ></canvas>

        {{-- Manual Draggable & Resizable RSBA QR Code Digital Signature Stamp Overlay --}}
        <div 
            x-ref="stampBadge"
            @mousedown.prevent="startDrag($event)"
            :style="`left: ${posX}%; top: ${posY}%; transform: scale(${(scale / 100) * (editorWidth / 850)}); transform-origin: top left; width: 230px;`"
            class="absolute z-30 cursor-grab active:cursor-grabbing select-none transition-transform duration-75"
        >
            <div :style="`background: rgba(255, 255, 255, ${(opacity ?? 100) / 100}); backdrop-filter: blur(${((100 - (opacity ?? 100)) / 100) * 4}px); padding: 10px 12px; border-radius: 12px; border: 2px solid rgba(5, 150, 105, ${Math.min(1, ((opacity ?? 100) + 20) / 100)}); box-shadow: 0 10px 25px rgba(0,0,0,${0.18 * ((opacity ?? 100) / 100)}), 0 0 0 3px rgba(16, 185, 129, ${0.2 * ((opacity ?? 100) / 100)});`" class="text-left w-full select-none">
                {{-- Stamp Header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #d1fae5; padding-bottom: 5px; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 5px;" class="pointer-events-none">
                        <div style="width: 15px; height: 15px; background: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 9px; font-weight: 900;" class="shrink-0">
                            ✓
                        </div>
                        <span style="font-size: 8.5px; font-weight: 900; text-transform: uppercase; color: #065f46; letter-spacing: 0.04em;">E-SIGNATURE & VERIFIKASI RSBA</span>
                    </div>
                </div>

                {{-- Stamp Body: QR Code + Signer Information --}}
                <div style="display: flex; align-items: center; gap: 8px;" class="pointer-events-none">
                    {{-- QR Code Column --}}
                    <div :style="`background: rgba(255, 255, 255, ${(opacity ?? 100) / 100}); border: 1px solid rgba(226, 232, 240, ${(opacity ?? 100) / 100}); border-radius: 8px; padding: 3px; display: flex; flex-direction: column; align-items: center; justify-content: center;`" class="shrink-0">
                        @if ($this->previewQrCode)
                            <img src="data:image/png;base64,{{ $this->previewQrCode }}" alt="QR Code Verifikasi" :style="`width: 58px; height: 58px; display: block; opacity: ${Math.max(0.4, (opacity ?? 100) / 100)};`">
                        @else
                            <div style="width: 58px; height: 58px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #64748b; text-align: center; border-radius: 4px;">
                                QR CODE
                            </div>
                        @endif
                        <span style="font-size: 6.5px; color: #64748b; font-weight: 600; margin-top: 2px;">Scan Verifikasi</span>
                    </div>

                    {{-- Metadata Column --}}
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px;">
                        <div style="font-size: 11px; font-weight: 800; color: #0f172a; line-height: 1.2;" class="truncate">{{ $this->highestRankSignerName }}</div>
                        <div style="font-size: 8.5px; color: #64748b; font-family: monospace;">{{ date('d M Y H:i') }} WIB</div>
                        <div style="font-size: 7px; color: #059669; font-weight: 700; margin-top: 1px;">
                            Dokumen Sah Terdaftar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
