<div class="space-y-4">
    {{-- Search & Filter Controls --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="relative flex-1">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari berdasarkan judul atau nomor surat..." 
                   class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status:</label>
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">Semua Status</option>
                <option value="pending">⏳ Menunggu Persetujuan</option>
                <option value="approved">✅ Disetujui</option>
                <option value="rejected">❌ Ditolak</option>
            </select>
        </div>
    </div>

    {{-- Submissions Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3">Dokumen Surat</th>
                        <th class="px-4 py-3">Tgl Pengajuan</th>
                        <th class="px-4 py-3">Rantai Penandatangan (Multi-Tier)</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($documents as $index => $doc)
                        @php
                            $rejectedAppr = $doc->approvals->where('status', 'rejected')->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-mono text-xs">
                                {{ $documents->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $doc->title }}</div>
                                <div class="text-xs font-mono text-indigo-600 mt-0.5">{{ $doc->document_number }}</div>
                                @if ($doc->revised_from_number)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Revisi dari No: {{ $doc->revised_from_number }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $doc->created_at ? $doc->created_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($doc->approvals->isNotEmpty())
                                        @foreach ($doc->approvals as $appr)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border
                                                @if($appr->status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                                                @elseif($appr->status === 'rejected') bg-rose-50 text-rose-700 border-rose-200
                                                @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                                <span class="font-bold mr-1">T{{ $appr->step_order }}:</span>
                                                {{ $appr->user?->name ?? 'User' }}
                                                @if($appr->status === 'approved') ✓ @elseif($appr->status === 'rejected') ✕ @endif
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-slate-400 font-italic">Tunggal (Sistem)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($doc->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        DISETUJUI
                                    </span>
                                @elseif ($doc->status === 'rejected')
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            DITOLAK
                                        </span>
                                        @if($rejectedAppr && $rejectedAppr->rejection_reason)
                                            <div class="mt-1 text-xs text-rose-600 max-w-xs truncate" title="{{ $rejectedAppr->rejection_reason }}">
                                                💬 "{{ $rejectedAppr->rejection_reason }}"
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <svg class="w-3.5 h-3.5 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        MENUNGGU PERSETUJUAN
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center gap-1.5 py-0.5">
                                    @if ($doc->status === 'rejected')
                                        <button wire:click="reviseDocument({{ $doc->id }})" 
                                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-amber-800 bg-amber-100 border border-amber-300 rounded-lg hover:bg-amber-200 transition-colors shadow-2xs cursor-pointer w-full max-w-[135px]">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Revisi Surat Ini
                                        </button>
                                    @endif
                                    <button wire:click="viewDetail({{ $doc->id }})" 
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors cursor-pointer w-full max-w-[135px]">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail Rantai
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-medium text-slate-600">Belum Ada Surat yang Diajukan</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tab "Studio Upload & Sign" untuk membuat pengajuan penandatanganan bertingkat baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Detail Approval Chain & Rejection Feedback --}}
    <x-ts:modal title="Detail Rantai Persetujuan Surat" wire="showDetailModal" center size="2xl">
        @if ($selectedDocument)
            <div class="space-y-4">
                {{-- Header Card Metadata --}}
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-bold text-slate-800 text-base leading-snug">{{ $selectedDocument->title }}</div>
                            <div class="text-xs font-mono text-indigo-600 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
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
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
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

                {{-- List Approval Steps (Rantai Penandatangan Bertingkat) --}}
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
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
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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

                @if ($selectedDocument->docstore_key)
                    <div class="pt-3 border-t border-slate-200 flex justify-end">
                        <a href="{{ rtrim(env('VERIFY_APP_URL', 'http://localhost:5173'), '/') }}/?key={{ $selectedDocument->docstore_key }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-xs">
                            Buka Portal Verifikasi Publik
                            <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </x-ts:modal>
</div>
