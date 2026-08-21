{{-- DRAWER / MODAL DETAIL DOKUMEN DOCSTORE --}}
<x-ts:modal title="Detail Arsip Dokumen Bank Surat" wire="modalDetail" size="2xl" center blur>
    @if($loadingDetail)
        <div class="p-8 text-center text-slate-500">
            <x-tabler-loader-2 class="size-8 animate-spin mx-auto mb-2 text-indigo-600" />
            <p class="text-xs">Memuat detail dari bank surat...</p>
        </div>
    @elseif($selectedDoc && isset($selectedDoc['document']))
        @php
            $sDoc = $selectedDoc['document'];
            $sContent = $sDoc['content'] ?? [];
            $sSigner = $selectedDoc['scanned_signature'] ?? [];
        @endphp
        <div class="space-y-4 text-xs">
            {{-- Header Metadata Card --}}
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                @if(!empty($sContent['title']))
                    <div class="font-bold text-slate-800 text-sm leading-snug mb-2">{{ $sContent['title'] }}</div>
                @endif
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Nomor Surat:</span>
                        <span class="font-mono font-bold text-indigo-600 text-xs">{{ $sDoc['number'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Jenis Dokumen:</span>
                        <span class="font-bold text-slate-800 text-xs">{{ strtoupper(str_replace('_', ' ', $sDoc['type'])) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Status Vault:</span>
                        <span class="font-bold text-emerald-700 text-xs flex items-center gap-1">
                            <x-tabler-shield-check class="size-3.5" />
                            {{ $selectedDoc['verification_status'] ?? 'VALID' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Status Dokumen:</span>
                        <span class="font-bold text-slate-800 text-xs uppercase">{{ $sDoc['status'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Information Box jika Surat Hasil Revisi --}}
            @if(!empty($sContent['revised_from_number']))
                <div class="p-3.5 bg-amber-50/90 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1.5 shadow-2xs">
                    <div class="font-bold flex items-center gap-1.5 text-amber-950">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Dokumen Hasil Revisi
                    </div>
                    <div><strong>Revisi dari Surat No:</strong> <span class="font-mono font-bold text-amber-950">{{ $sContent['revised_from_number'] }}</span></div>
                    @if(!empty($sContent['catatan_revisi']))
                        <div class="mt-1 bg-white/90 p-2.5 rounded-lg border border-amber-200 font-mono text-[11px] text-amber-900 leading-relaxed">
                            <strong>Catatan Revisi:</strong><br>
                            "{{ $sContent['catatan_revisi'] }}"
                        </div>
                    @endif
                </div>
            @endif

            {{-- Multi-Tier Signatures List --}}
            @if(!empty($selectedDoc['all_signatures']))
                <div class="space-y-2.5 pt-3 border-t border-slate-200">
                    <span class="text-slate-500 text-[10px] uppercase font-bold tracking-wider block">Rantai Penandatangan (Multi-Tier):</span>
                    <div class="space-y-2">
                        @foreach($selectedDoc['all_signatures'] as $idx => $sig)
                            <div class="flex items-center justify-between p-3 rounded-xl border text-xs shadow-2xs
                                @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-50/60 border-emerald-200
                                @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-50/60 border-rose-200
                                @else bg-amber-50/60 border-amber-200 @endif">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full font-bold text-[10px] flex items-center justify-center shrink-0 shadow-2xs
                                        @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-600 text-white
                                        @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-600 text-white
                                        @else bg-amber-600 text-white @endif">
                                        T{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $sig['signer_name'] }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $sig['signer_role'] ?? 'Pejabat' }}</div>
                                        @if(!empty($sig['original_data']) && in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak']))
                                            <div class="text-[10px] text-rose-600 mt-0.5 font-mono">💬 "{{ $sig['original_data'] }}"</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="font-bold uppercase text-[10px] px-2.5 py-1 rounded-full border shrink-0
                                    @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-100 text-emerald-800 border-emerald-200
                                    @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-100 text-rose-800 border-rose-200
                                    @else bg-amber-100 text-amber-800 border-amber-200 @endif">
                                    {{ $sig['status'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-ts:modal>
