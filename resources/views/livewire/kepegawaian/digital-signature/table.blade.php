<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 w-full">
        <div>
            <h2 class="text-base font-bold text-slate-800">Daftar Surat PDF Ter-Sign</h2>
            <p class="text-xs text-slate-500">Docstore Vault & Single Source of Truth</p>
        </div>
        <div class="w-full md:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari judul, nomor, atau ID docstore..." 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
            />
        </div>
    </div>

    <div class="overflow-x-auto w-full">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4">Dokumen</th>
                    <th class="px-6 py-4">ByteCounter</th>
                    <th class="px-6 py-4">Docstore ID</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($documents as $doc)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $doc->title }}</div>
                            <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $doc->document_number }}</div>
                            <div class="text-[11px] text-indigo-600 mt-1 font-medium">Oleh: {{ optional($doc->user)->name ?? optional(optional($doc->user)->karyawan)->nama ?? optional($doc->user)->email ?? 'Pengguna' }}</div>

                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-mono bg-slate-100 px-2.5 py-1 rounded-md inline-block text-slate-700 font-semibold mb-1">
                                {{ number_format($doc->file_size) }} bytes
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono truncate max-w-[240px]" title="{{ $doc->byte_counter_hash }}">
                                SHA: {{ $doc->byte_counter_hash }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if ($doc->docstore_key)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ $doc->docstore_key }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    Pending Sync
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if ($doc->docstore_key)
                                <button wire:click="openPrintModal({{ $doc->id }})" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Tombol Print
                                </button>
                            @else
                                <button disabled class="inline-flex items-center px-3.5 py-2 bg-slate-200 text-slate-400 text-xs font-medium rounded-xl cursor-not-allowed">
                                    Print N/A
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                            Belum ada dokumen PDF ter-sign. Silakan pindah ke tab <strong>Upload & Sign Surat PDF</strong> untuk membuat dokumen pertama Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($documents && is_object($documents) && method_exists($documents, 'links'))
        <div class="p-4 border-t border-slate-100 w-full">
            {{ $documents->links() }}
        </div>
    @endif

    {{-- Modal Print Dokumen dari Docstore --}}
    @if ($showPrintModal && $selectedDocument)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-6 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Preview & Cetak Dokumen (Docstore Single Source)</h3>
                        <p class="text-xs text-slate-500">ID Docstore: <span class="font-mono text-indigo-600 font-semibold">{{ $selectedDocument->docstore_key }}</span></p>
                    </div>
                    <button wire:click="closePrintModal" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <div class="space-y-4 text-sm text-slate-700 bg-slate-50 p-5 rounded-xl border border-slate-200/80">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-slate-400 block">Judul Surat:</span>
                            <span class="font-bold text-slate-800">{{ $selectedDocument->title }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Nomor Surat:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $selectedDocument->document_number }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Penandatangan:</span>
                            <span class="font-semibold text-slate-800">{{ optional($selectedDocument->user)->name }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Waktu Sign:</span>
                            <span class="font-semibold text-slate-800">{{ $selectedDocument->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <span class="text-xs text-slate-400 block">Metric ByteCounter (SHA-256 Checksum):</span>
                        <div class="font-mono text-xs bg-white p-2.5 rounded-lg border border-slate-200 text-indigo-900 break-all mt-1">
                            {{ $selectedDocument->byte_counter_hash }}
                        </div>
                    </div>

                    {{-- QR Code Verification Section --}}
                    <div class="border-t border-slate-200 pt-4 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Status Keaslian Portal Verify:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">
                                ByteCounter Valid & Registered
                            </span>
                        </div>
                        <div class="text-center">
                            @php
                                $verifyUrl = env('VERIFY_APP_URL', 'http://localhost:5174') . '?key=' . $selectedDocument->docstore_key;
                                $qrCode = (new \Milon\Barcode\DNS2D)->getBarcodePNGPath($verifyUrl, 'QRCODE', 3, 3);
                            @endphp
                            <img src="{{ $qrCode }}" alt="QR Verification" class="w-20 h-20 mx-auto rounded-lg border border-slate-200 shadow-sm" />
                            <span class="text-[10px] text-slate-400 block mt-1">Scan to Verify</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button wire:click="closePrintModal" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                        Tutup
                    </button>
                    <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/25 flex items-center space-x-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak Surat (Print)</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
