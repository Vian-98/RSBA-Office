{{-- DRAWER / MODAL DETAIL DOKUMEN DOCSTORE --}}
<x-ts:modal wire="modalDetail" size="2xl" center blur>
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
            {{-- Metadata Grid --}}
            <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Nomor Surat:</span>
                    <span class="font-bold text-slate-800 text-xs">{{ $sDoc['number'] }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Jenis Dokumen:</span>
                    <span class="font-bold text-slate-800 text-xs">{{ strtoupper($sDoc['type']) }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Status Vault:</span>
                    <span class="font-bold text-emerald-700 text-xs">{{ $selectedDoc['verification_status'] ?? 'VALID' }}</span>
                </div>
            {{-- Multi-Tier Signatures List --}}
            @if(!empty($selectedDoc['all_signatures']))
                <div class="space-y-2 pt-2 border-t border-slate-200">
                    <span class="text-slate-500 text-[10px] uppercase font-bold block">Rantai Penandatangan (Multi-Tier):</span>
                    <div class="space-y-1.5">
                        @foreach($selectedDoc['all_signatures'] as $idx => $sig)
                            <div class="flex items-center justify-between p-2 rounded-lg border text-xs
                                @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-50/60 border-emerald-200
                                @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-50/60 border-rose-200
                                @else bg-amber-50/60 border-amber-200 @endif">
                                <div class="flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-full font-bold text-[10px] flex items-center justify-center
                                        @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-600 text-white
                                        @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-600 text-white
                                        @else bg-amber-600 text-white @endif">
                                        T{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $sig['signer_name'] }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $sig['signer_role'] ?? 'Pejabat' }}</div>
                                        @if(!empty($sig['original_data']) && in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak']))
                                            <div class="text-[10px] text-rose-600 mt-0.5">💬 "{{ $sig['original_data'] }}"</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="font-bold uppercase text-[10px] px-2 py-0.5 rounded
                                    @if(in_array(strtolower($sig['status'] ?? ''), ['approved', 'valid', 'signed', 'disetujui'])) bg-emerald-100 text-emerald-800
                                    @elseif(in_array(strtolower($sig['status'] ?? ''), ['rejected', 'ditolak'])) bg-rose-100 text-rose-800
                                    @else bg-amber-100 text-amber-800 @endif">
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
