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
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Penandatangan Vault:</span>
                    <span class="font-bold text-slate-800 text-xs">{{ $sSigner['signer_name'] ?? 'Direktur' }}</span>
                </div>
            </div>
        </div>
    @endif
</x-ts:modal>
