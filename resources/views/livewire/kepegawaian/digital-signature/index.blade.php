<div class="w-full space-y-6">
    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="w-full p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="w-full p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Main Grid Layout --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 w-full min-w-0">
        {{-- Form Sign Document (Left Column) --}}
        <div class="xl:col-span-5 w-full min-w-0 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-4 mb-6">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Upload & Sign Surat PDF</h2>
                        <p class="text-xs text-slate-500">Unggah berkas untuk proses tanda tangan digital</p>
                    </div>
                </div>

                <form wire:submit.prevent="saveAndSign" class="space-y-5">
                    {{-- Upload PDF Input --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unggah Berkas Surat (PDF strictly)</label>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-xl p-6 text-center transition-all bg-slate-50/60 group cursor-pointer">
                            <input type="file" wire:model="pdf_file" accept="application/pdf,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div class="space-y-2">
                                <div class="mx-auto w-12 h-12 rounded-xl bg-indigo-100/70 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="text-sm text-slate-600">
                                    @if ($pdf_file)
                                        <span class="font-semibold text-indigo-600 block truncate max-w-xs mx-auto">{{ $pdf_file->getClientOriginalName() }}</span>
                                    @else
                                        <span class="font-semibold text-indigo-600">Klik untuk upload</span> atau seret berkas PDF ke sini
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400">Hanya format .PDF (Maksimal 10MB)</p>
                            </div>
                        </div>
                        @error('pdf_file') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Judul Surat --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul / Nama Surat</label>
                        <input type="text" wire:model="title" placeholder="Contoh: Surat Keputusan Direksi Nomer 102" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
                        @error('title') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nomor Surat --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Surat</label>
                        <input type="text" wire:model="document_number" placeholder="Nomor Surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono text-slate-800 bg-white" />
                        @error('document_number') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
                        <textarea wire:model="keterangan" rows="2" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
                    </div>

                    {{-- Passphrase Sertifikat --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Passphrase Sertifikat Digital (Opsional)</label>
                        <input type="password" wire:model="passphrase" placeholder="Masukkan passphrase sertifikat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" wire:loading.attr="disabled" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center space-x-2">
                        <span wire:loading.remove>Sign & Kirim ke Docstore</span>
                        <span wire:loading class="flex items-center space-x-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses Signature & ByteCounter...</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Table Documents List (Right Column) --}}
        <div class="xl:col-span-7 w-full min-w-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Daftar Surat PDF Ter-Sign</h2>
                        <p class="text-xs text-slate-500">Docstore Vault & Single Source of Truth</p>
                    </div>
                </div>

                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5">Dokumen</th>
                                <th class="px-5 py-3.5">ByteCounter</th>
                                <th class="px-5 py-3.5">Docstore ID</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($documents as $doc)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-800 text-sm">{{ $doc->title }}</div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $doc->document_number }}</div>
                                        <div class="text-[11px] text-indigo-600 mt-1 font-medium">Oleh: {{ optional($doc->user)->name }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-xs font-mono bg-slate-100 px-2.5 py-1 rounded-md inline-block text-slate-700 font-semibold mb-1">
                                            {{ number_format($doc->file_size) }} bytes
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono truncate max-w-[130px]" title="{{ $doc->byte_counter_hash }}">
                                            SHA: {{ substr($doc->byte_counter_hash, 0, 16) }}...
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($doc->docstore_key)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <svg class="w-3 h-3 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ substr($doc->docstore_key, 0, 8) }}...
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Pending Sync
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        @if ($doc->docstore_key)
                                            <button wire:click="openPrintModal({{ $doc->id }})" class="inline-flex items-center px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                                Tombol Print
                                            </button>
                                        @else
                                            <button disabled class="inline-flex items-center px-3 py-1.5 bg-slate-200 text-slate-400 text-xs font-medium rounded-xl cursor-not-allowed">
                                                Print N/A
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                                        Belum ada dokumen PDF ter-sign. Silakan unggah dan sign berkas pertama Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>

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
