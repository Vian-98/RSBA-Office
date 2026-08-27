{{-- MODAL DETAIL RANTAI PERSETUJUAN --}}
<x-ts:modal title="Detail Rantai Persetujuan Surat" wire="showDetailModal" center size="2xl">
    @if ($selectedDocument)
        <div class="space-y-4 text-xs">
            {{-- Metadata Grid --}}
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-bold text-slate-800 text-base leading-snug">{{ $selectedDocument->title }}</div>
                        <div class="text-xs font-mono text-indigo-600 mt-1 flex items-center gap-1.5">
                            Nomor Surat: {{ $selectedDocument->document_number }}
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shrink-0
                        @if($selectedDocument->status === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-200
                        @elseif($selectedDocument->status === 'rejected') bg-rose-100 text-rose-800 border border-rose-200
                        @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                        {{ $selectedDocument->status }}
                    </span>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                    <div>Pengirim: <span class="font-semibold text-slate-700">{{ $selectedDocument->user?->name }}</span></div>
                    <div>Tgl Pengajuan: <span class="font-medium text-slate-700">{{ $selectedDocument->created_at ? $selectedDocument->created_at->translatedFormat('d M Y, H:i') : '-' }}</span></div>
                </div>
            </div>

            {{-- Information Box jika Surat Hasil Revisi --}}
            @if ($selectedDocument->revised_from_number)
                <div class="bg-amber-50/90 border-l-4 border-amber-500 p-4 rounded-r-xl text-xs space-y-1.5">
                    <div class="font-bold text-amber-900 flex items-center gap-1.5">
                        Dokumen Hasil Revisi
                    </div>
                    <div class="text-amber-800">
                        <strong>Revisi dari Surat No:</strong> <span class="font-mono font-bold text-amber-950">{{ $selectedDocument->revised_from_number }}</span>
                    </div>
                    @if ($selectedDocument->catatan_revisi)
                        <div class="mt-1 bg-white/90 p-3 rounded-lg border border-amber-200 font-mono text-amber-900 text-[11px] leading-relaxed">
                            <strong>Catatan Revisi Pengaju:</strong><br>
                            "{{ $selectedDocument->catatan_revisi }}"
                        </div>
                    @endif
                </div>
            @endif

            {{-- Section Status Global & Feedback --}}
            @if ($selectedDocument->status === 'rejected')
                @php
                    $rej = $selectedDocument->approvals->where('status', 'rejected')->first();
                @endphp
                <div class="bg-rose-50/90 border-l-4 border-rose-500 p-4 rounded-r-xl">
                    <div class="flex items-center text-rose-800 font-bold text-sm">
                        <svg class="w-5 h-5 mr-2 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Pengajuan Ditolak oleh {{ $rej?->user?->name ?? 'Penandatangan' }}
                    </div>
                    <div class="mt-2 text-xs text-rose-700 bg-white/90 p-3 rounded-lg border border-rose-200 font-mono leading-relaxed">
                        <strong>Catatan Feedback Penolakan:</strong><br>
                        "{{ $rej?->rejection_reason ?? 'Tidak ada catatan feedback.' }}"
                    </div>
                </div>
            @endif

            {{-- Rantai Penandatangan --}}
            <div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    Rantai Penandatangan Bertingkat (Multi-Tier):
                </h4>
                <div class="space-y-3">
                    @foreach ($selectedDocument->approvals as $appr)
                        <div class="flex items-start justify-between p-3.5 rounded-xl border transition-all shadow-2xs
                            @if($appr->status === 'approved') bg-emerald-50/50 border-emerald-200
                            @elseif($appr->status === 'rejected') bg-rose-50/50 border-rose-200
                            @else bg-amber-50/50 border-amber-200 @endif">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-xs
                                    @if($appr->status === 'approved') bg-emerald-500 text-white
                                    @elseif($appr->status === 'rejected') bg-rose-500 text-white
                                    @else bg-amber-500 text-white @endif">
                                    T{{ $appr->step_order }}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-800 text-sm">{{ $appr->user?->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $appr->user?->karyawan?->jabatan?->first()?->nama ?? 'Penandatangan' }}</div>
                                    @if($appr->status === 'approved' && $appr->signed_at)
                                        <div class="text-[11px] text-emerald-600 mt-1 flex items-center gap-1 font-medium">
                                            Disetujui pada: {{ $appr->signed_at->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    @elseif($appr->status === 'rejected')
                                        <div class="text-[11px] text-rose-600 mt-1 font-medium">✕ Ditolak: "{{ $appr->rejection_reason }}"</div>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shrink-0
                                @if($appr->status === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($appr->status === 'rejected') bg-rose-100 text-rose-800 border border-rose-200
                                @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                {{ $appr->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-ts:modal>
