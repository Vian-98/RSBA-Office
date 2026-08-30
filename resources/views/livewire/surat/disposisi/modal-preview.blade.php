<div>
    <!-- Modal Preview & Aksi Disposisi -->
    <x-ts:modal wire="modalPreview" id="modal-preview" title="Preview Surat Disposisi Direktur" size="4xl" center blur>
        @if($selectedDisposisi)
            <div class="space-y-5 text-left">
                <!-- Header Info & Digital Signature QR Badge -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-4 gap-4">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">
                            LEMBAR PENERUS / DISPOSISI
                        </h2>
                        <h3 class="text-xs font-bold text-indigo-700 dark:text-indigo-400">
                            RS BINTANG AMIN - DIREKTUR
                        </h3>
                        <div class="mt-1 text-[11px] font-mono text-slate-500">
                            ID Transaksi: {{ $selectedDisposisi->signature_hash ?? 'N/A' }}
                        </div>
                    </div>

                    @if($selectedDisposisi->signature_hash)
                        @php
                            $verifyUrl = config('services.docstore.verify_app_url', env('VERIFY_APP_URL', 'http://localhost:5173'));
                            $targetVerifyLink = rtrim($verifyUrl, '/') . '/?hash=' . $selectedDisposisi->signature_hash;
                            if (!empty($selectedDisposisi->docstore_key)) {
                                $targetVerifyLink = rtrim($verifyUrl, '/') . '/?key=' . $selectedDisposisi->docstore_key;
                            }
                        @endphp
                        <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl p-2.5">
                            <div class="p-1.5 bg-white rounded-lg border shadow-2xs">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode($targetVerifyLink) }}" alt="QR Keabsahan" class="w-12 h-12" />
                            </div>
                            <div>
                                <x-ts:badge color="emerald" icon="check-badge" text="TTD DIGITAL VALID" />
                                <a href="{{ $targetVerifyLink }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline font-semibold flex items-center gap-1 mt-1">
                                    Verifikasi Publik &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Meta Details Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <div>
                        <span class="font-bold text-slate-500 uppercase block text-[10px]">No. Agenda</span>
                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ $selectedDisposisi->no_agenda }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-500 uppercase block text-[10px]">Tgl Surat</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedDisposisi->tgl_surat->format('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-500 uppercase block text-[10px]">No. Surat</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedDisposisi->no_surat }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-500 uppercase block text-[10px]">Asal Surat</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedDisposisi->asal_surat }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="font-bold text-slate-500 uppercase block text-[10px]">Perihal</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ $selectedDisposisi->perihal }}</span>
                    </div>
                </div>

                <!-- Table Recipients -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                        Kepada YTH (Penerima & Status Tindak Lanjut)
                    </h3>
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="py-2 px-3 text-center border-b">No</th>
                                    <th class="py-2 px-3 border-b">Kepada YTH</th>
                                    <th class="py-2 px-2 text-center border-b">Info</th>
                                    <th class="py-2 px-2 text-center border-b">Action</th>
                                    <th class="py-2 px-2 text-center border-b">Arsip</th>
                                    <th class="py-2 px-3 text-center border-b">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($selectedDisposisi->details as $idx => $det)
                                    <tr>
                                        <td class="py-2 px-3 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ $det->nama_tujuan }}</td>
                                        <td class="py-2 px-2 text-center font-bold {{ $det->is_info ? 'text-indigo-600' : 'text-slate-300' }}">{{ $det->is_info ? '✓' : '-' }}</td>
                                        <td class="py-2 px-2 text-center font-bold {{ $det->is_action ? 'text-emerald-600' : 'text-slate-300' }}">{{ $det->is_action ? '✓' : '-' }}</td>
                                        <td class="py-2 px-2 text-center font-bold {{ $det->is_arsip ? 'text-amber-600' : 'text-slate-300' }}">{{ $det->is_arsip ? '✓' : '-' }}</td>
                                        <td class="py-2 px-3 text-center">
                                            @if($det->status_tindak_lanjut === 'done')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Paraf / Done</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan Direktur -->
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Catatan / Instruksi Direktur:</h3>
                    <div class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-slate-800 dark:text-slate-200 text-xs whitespace-pre-line">
                        {{ $selectedDisposisi->catatan ?: 'Tidak ada catatan khusus.' }}
                    </div>
                </div>
            </div>
        @else
            <div class="py-8 text-center text-slate-400">
                Memuat preview surat disposisi...
            </div>
        @endif
        <x-slot name="footer">
            <div class="flex items-center justify-between w-full">
                <x-ts:button wire:click="$set('modalPreview', false)" x-on:click="$tsui.close('modal-preview')" color="slate" outline size="sm">
                    Tutup
                </x-ts:button>
                @if($selectedDisposisi)
                    <div class="flex items-center gap-2">
                        <x-ts:button onclick="printDocument('{{ route('kepegawaian.surat.disposisi.print', $selectedDisposisi->id) }}')" type="button" color="slate" outline icon="printer" size="sm">
                            Cetak Dokumen
                        </x-ts:button>
                        <x-ts:button href="{{ route('kepegawaian.surat.disposisi.download', $selectedDisposisi->id) }}" color="emerald" icon="arrow-down-tray" size="sm">
                            Unduh PDF
                        </x-ts:button>
                    </div>
                @endif
            </div>
        </x-slot>
    </x-ts:modal>
</div>
