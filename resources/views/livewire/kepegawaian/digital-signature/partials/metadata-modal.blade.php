{{-- POPUP MODAL METADATA SURAT --}}
@if ($showPrintModal && $selectedDocument)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in duration-150">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Rincian Dokumen Ter-Sign</h3>
                </div>
                <button wire:click="closePrintModal" class="text-slate-400 hover:text-slate-600 text-base font-bold p-1">✕</button>
            </div>

            {{-- Essential Metadata Section --}}
            <div class="space-y-3.5 text-xs text-slate-700 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold block mb-0.5">Judul Surat</span>
                    <span class="font-bold text-slate-800 text-sm leading-snug block">{{ $selectedDocument->title }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-200/60 pt-3">
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold block mb-0.5">Nomor Surat</span>
                        <span class="font-mono font-bold text-slate-800 text-xs block">{{ $selectedDocument->document_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold block mb-0.5">Penandatangan</span>
                        <span class="font-semibold text-slate-800 text-xs block">{{ optional($selectedDocument->user)->name ?? 'Super Admin' }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-200/60 pt-3 items-center">
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold block mb-0.5">Waktu Tanda Tangan</span>
                        <span class="font-medium text-slate-700 text-xs block">{{ $selectedDocument->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold block mb-0.5">Status Verifikasi</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            ✓ Valid & Registered
                        </span>
                    </div>
                </div>
            </div>

            {{-- Modal Action Buttons --}}
            <div 
                x-data="{ isPrinting: false }" 
                class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100"
            >
                <button wire:click="closePrintModal" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Tutup
                </button>

                <button 
                    type="button"
                    wire:click="downloadPdf({{ $selectedDocument->id }})"
                    wire:loading.attr="disabled"
                    wire:target="downloadPdf"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-2 transition-all cursor-pointer disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="downloadPdf" class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Unduh PDF</span>
                    </span>
                    <span wire:loading.flex wire:target="downloadPdf" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mengunduh...</span>
                    </span>
                </button>

                <button 
                    type="button"
                    @click="
                        isPrinting = true;
                        let printIframe = document.getElementById('hidden-doc-print-iframe');
                        if (!printIframe) {
                            printIframe = document.createElement('iframe');
                            printIframe.id = 'hidden-doc-print-iframe';
                            printIframe.style.position = 'fixed';
                            printIframe.style.right = '0';
                            printIframe.style.bottom = '0';
                            printIframe.style.width = '0';
                            printIframe.style.height = '0';
                            printIframe.style.border = '0';
                            printIframe.style.visibility = 'hidden';
                            document.body.appendChild(printIframe);
                        }
                        printIframe.src = '{{ route('digital-signature.print', $selectedDocument->id) }}';
                        setTimeout(function() { isPrinting = false; }, 2000);
                    "
                    :disabled="isPrinting"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-2 transition-all cursor-pointer disabled:opacity-50"
                >
                    <template x-if="!isPrinting">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 00-2-2Zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Cetak Salinan</span>
                        </span>
                    </template>

                    <template x-if="isPrinting">
                        <span class="inline-flex items-center space-x-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyiapkan Cetak...</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>
@endif
