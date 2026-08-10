{{-- POPUP MODAL METADATA SURAT --}}
@if ($showPrintModal && $selectedDocument)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in duration-150">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Metadata Dokumen (Docstore Vault)</h3>
                        <p class="text-xs text-slate-500">Docstore ID: <span class="font-mono text-indigo-600 font-semibold">{{ $selectedDocument->docstore_key }}</span></p>
                    </div>
                </div>
                <button wire:click="closePrintModal" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            {{-- Document Metadata Card --}}
            <div class="space-y-4 text-xs text-slate-700 bg-slate-50 p-5 rounded-2xl border border-slate-200/80">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Judul Surat:</span>
                        <span class="font-bold text-slate-800 text-sm leading-tight block">{{ $selectedDocument->title }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor Surat:</span>
                        <span class="font-mono font-bold text-slate-800 text-sm block">{{ $selectedDocument->document_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Penandatangan:</span>
                        <span class="font-semibold text-slate-800 block">{{ optional($selectedDocument->user)->name ?? 'Super Admin' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Waktu Sign:</span>
                        <span class="font-semibold text-slate-800 block">{{ $selectedDocument->created_at->format('d M Y H:i') }} WIB</span>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-3">
                    <span class="text-slate-400 block mb-1">Metric ByteCounter SHA-256 Checksum:</span>
                    <div class="font-mono text-xs bg-white p-3 rounded-xl border border-slate-200 text-indigo-900 break-all">
                        {{ $selectedDocument->byte_counter_hash }}
                    </div>
                </div>

                {{-- QR Code Verification Section --}}
                <div class="border-t border-slate-200 pt-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Status Keaslian Portal Verify:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">
                            ByteCounter Valid & Registered
                        </span>
                    </div>
                    <div class="text-center">
                        @php
                            $verifyUrl = env('VERIFY_APP_URL', 'http://localhost:5174') . '?key=' . $selectedDocument->docstore_key;
                            $qrCode = (new \Milon\Barcode\DNS2D)->getBarcodePNGPath($verifyUrl, 'QRCODE', 2, 2);
                        @endphp
                        <img src="{{ $qrCode }}" alt="QR Verification" class="w-14 h-14 inline-block rounded-xl border border-slate-200 shadow-sm" />
                        <span class="text-[9px] text-slate-400 block mt-1">Scan to Verify</span>
                    </div>
                </div>
            </div>

            {{-- Modal Action Buttons --}}
            <div class="flex items-center justify-end space-x-3 pt-2 border-t border-slate-100">
                <button wire:click="closePrintModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Tutup
                </button>
                <a 
                    href="{{ route('digital-signature.print', $selectedDocument->id) }}" 
                    target="_blank"
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/25 inline-flex items-center space-x-2 transition-all cursor-pointer no-underline"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Surat (Print)</span>
                </a>
            </div>
        </div>
    </div>
@endif
