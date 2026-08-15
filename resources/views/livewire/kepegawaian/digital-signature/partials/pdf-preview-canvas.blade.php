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
        {{-- PDF.js Canvas Rendering (100% Exact Edge-to-Edge PDF Page 1) --}}
        <canvas 
            x-ref="pdfCanvas" 
            style="width: 100%; height: 100%; display: block; border-radius: 18px; background: white;"
        ></canvas>

        {{-- Manual Draggable & Resizable Mekari Vault Seal Stamp Overlay --}}
        <div 
            x-ref="stampBadge"
            @mousedown.prevent="startDrag($event)"
            :style="`left: ${posX}%; top: ${posY}%; transform: scale(${(scale / 100) * (editorWidth / 850)}); transform-origin: top left; width: 190px;`"
            class="absolute z-30 cursor-grab active:cursor-grabbing select-none transition-transform duration-75"
        >
            <div style="background: rgba(255, 255, 255, 0.96); padding: 10px 12px; border-radius: 12px; border: 2px solid #10b981; box-shadow: 0 10px 25px rgba(0,0,0,0.15), 0 0 0 3px rgba(16, 185, 129, 0.15);" class="text-left w-full select-none">
                {{-- Stamp Header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #d1fae5; padding-bottom: 4px; margin-bottom: 4px;">
                    <div style="display: flex; align-items: center; gap: 4px;" class="pointer-events-none">
                        <div style="width: 14px; height: 14px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 9px; font-weight: 900;" class="shrink-0">
                            ✓
                        </div>
                        <span style="font-size: 8.5px; font-weight: 900; text-transform: uppercase; color: #065f46; letter-spacing: 0.05em;">SIGNED BY MEKARI VAULT</span>
                    </div>
                </div>

                <div style="font-size: 11px; font-weight: 700; color: #1e293b;" class="pointer-events-none truncate">{{ Auth::user()->name }}</div>
                <div style="font-size: 8.5px; color: #64748b; font-family: monospace; margin-top: 2px;" class="pointer-events-none">{{ date('d M Y H:i') }} WIB</div>
                <div style="font-size: 8px; font-family: monospace; color: #4338ca; margin-top: 3px; background: #eef2ff; padding: 2px 5px; border-radius: 4px;" class="pointer-events-none truncate">
                    SHA: {{ substr($fileHashSHA256, 0, 14) }}...
                </div>
            </div>
        </div>
    </div>
</div>
