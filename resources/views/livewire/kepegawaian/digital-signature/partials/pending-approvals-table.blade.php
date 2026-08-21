<div class="space-y-4">
    {{-- Header & Sub-Tabs Navigation & Search --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
        {{-- Sub-Tabs Toggle --}}
        <div class="flex items-center space-x-2">
            <button wire:click="setSubTab('pending')" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center space-x-2 cursor-pointer
                    {{ $subTab === 'pending' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Perlu Tindakan Saya</span>
                @if ($pendingActionCount > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-[10px] font-black rounded-full {{ $subTab === 'pending' ? 'bg-white text-indigo-700' : 'bg-indigo-600 text-white' }}">
                        {{ $pendingActionCount }}
                    </span>
                @endif
            </button>

            <button wire:click="setSubTab('history')" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center space-x-2 cursor-pointer
                    {{ $subTab === 'history' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>History & Semua Assign Saya</span>
                @if ($totalAssignedCount > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-[10px] font-black rounded-full {{ $subTab === 'history' ? 'bg-white text-indigo-700' : 'bg-slate-200 text-slate-700' }}">
                        {{ $totalAssignedCount }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Search Input Bar --}}
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari Judul, No. Surat, Pengaju..." 
                   class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-800 placeholder-slate-400">
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Info Alert jika di Sub-Tab History --}}
    @if ($subTab === 'history')
        <div class="bg-indigo-50/80 border border-indigo-200 p-3 rounded-xl text-xs text-indigo-900 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Menampilkan <strong>seluruh dokumen yang di-assign</strong> kepada Anda (yang sudah disetujui, ditolak, maupun yang sedang menunggu giliran).</span>
            </div>
        </div>
    @endif

    {{-- Main Document Table --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Dokumen & Nomor Surat</th>
                        <th class="px-4 py-3.5">Pengirim</th>
                        <th class="px-4 py-3.5 text-center">Tingkat Persetujuan</th>
                        <th class="px-4 py-3.5 text-center">Status Anda</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @php
                        $userAuthId = auth()->id();
                        $itemsList = ($subTab === 'pending') ? $documents : ($paginatedDocs ? $paginatedDocs->items() : []);
                    @endphp

                    @forelse ($itemsList as $doc)
                        @php
                            $userAppr = $doc->approvals->where('user_id', $userAuthId)->first();
                            $isMyTurn = $doc->isPendingForUser($userAuthId);
                            $totalSteps = $doc->approvals->count();
                            $approvedSteps = $doc->approvals->where('status', 'approved')->count();
                            $rejectedStep = $doc->approvals->where('status', 'rejected')->first();
                            $activeStep = $doc->approvals->where('status', 'pending')->first();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- Dokumen & Nomor Surat --}}
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800 text-sm leading-snug">{{ $doc->title }}</div>
                                <div class="text-xs font-mono text-indigo-600 mt-0.5 flex items-center gap-1.5">
                                    <span>{{ $doc->document_number }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">
                                        {{ str_replace('_', ' ', $doc->document_type) }}
                                    </span>
                                </div>
                                @if ($doc->revised_from_number)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Revisi dari No: {{ $doc->revised_from_number }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Pengirim --}}
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $doc->user?->name }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $doc->created_at ? $doc->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            {{-- Tingkat Persetujuan --}}
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="font-bold text-xs text-slate-700">Tier {{ $userAppr?->step_order ?? 1 }} / {{ $totalSteps }}</span>
                                    <div class="w-20 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                        @php
                                            $pct = $totalSteps > 0 ? round(($approvedSteps / $totalSteps) * 100) : 0;
                                        @endphp
                                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $approvedSteps }} dari {{ $totalSteps }} Disetujui</span>
                                </div>
                            </td>

                            {{-- Status Anda / Status Tier --}}
                            <td class="px-4 py-3.5 text-center">
                                @if ($doc->status === 'rejected')
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            DITOLAK
                                        </span>
                                        @if ($rejectedStep)
                                            <span class="text-[10px] text-rose-600 mt-1 max-w-[150px] truncate" title="{{ $rejectedStep->rejection_reason }}">
                                                oleh {{ $rejectedStep->user?->name }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif ($userAppr?->status === 'approved')
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            DISETUJUI SAYA
                                        </span>
                                        @if ($userAppr->signed_at)
                                            <span class="text-[10px] text-slate-400 mt-0.5">{{ $userAppr->signed_at->translatedFormat('d M, H:i') }}</span>
                                        @endif
                                    </div>
                                @elseif ($isMyTurn)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        PERLU TINDAKAN ANDA
                                    </span>
                                @else
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Menunggu Tier {{ $activeStep?->step_order ?? 1 }}
                                        </span>
                                        @if ($activeStep)
                                            <span class="text-[10px] text-slate-400 mt-0.5">({{ $activeStep->user?->name }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center gap-1.5 py-0.5">
                                    @if ($isMyTurn)
                                        <button wire:click="openSignModal({{ $doc->id }})" 
                                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-2xs cursor-pointer w-full max-w-[140px]">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Setujui & TTD
                                        </button>
                                        <button wire:click="openRejectModal({{ $doc->id }})" 
                                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer w-full max-w-[140px]">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Tolak Surat
                                        </button>
                                    @elseif ($userAppr?->status === 'pending' && $doc->status !== 'rejected')
                                        {{-- Locked TTD Button for users waiting for earlier tiers --}}
                                        <button disabled 
                                                title="Menunggu persetujuan dari pejabat tingkat sebelumnya (Tier {{ $activeStep?->step_order ?? 1 }})"
                                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed w-full max-w-[140px]">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            TTD Terkunci
                                        </button>
                                    @endif

                                    <button wire:click="viewDetail({{ $doc->id }})" 
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors cursor-pointer w-full max-w-[140px]">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail Rantai
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-medium text-slate-600">
                                    {{ $subTab === 'pending' ? 'Tidak Ada Surat yang Memerlukan Tindakan Anda saat Ini' : 'Belum Ada Riwayat Persetujuan Surat yang Di-assign' }}
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ !empty($search) ? 'Tidak ditemukan surat sesuai kata kunci pencarian.' : 'Dokumen yang di-assign kepada Anda akan tampil pada daftar ini.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Controls for History Sub-Tab --}}
        @if ($subTab === 'history' && $paginatedDocs && $paginatedDocs->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $paginatedDocs->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL SIGN / APPROVE --}}
    <x-ts:modal title="Konfirmasi Tanda Tangan Digital" wire="showSignModal" center size="md">
        @if ($selectedDocument)
            <div class="space-y-4 text-xs">
                <div class="bg-indigo-50 p-3.5 rounded-xl border border-indigo-100">
                    <div class="font-bold text-slate-800 text-sm">{{ $selectedDocument->title }}</div>
                    <div class="text-xs font-mono text-indigo-600 mt-0.5">Nomor: {{ $selectedDocument->document_number }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Pengirim: <span class="font-medium text-slate-700">{{ $selectedDocument->user?->name }}</span></div>
                </div>

                @if ($selectedDocument->revised_from_number)
                    <div class="text-xs bg-amber-50 border border-amber-200 p-2.5 rounded-lg text-amber-900">
                        <div><strong>Surat Hasil Revisi</strong> (Revisi dari No: <span class="font-mono font-bold">{{ $selectedDocument->revised_from_number }}</span>)</div>
                        @if ($selectedDocument->catatan_revisi)
                            <div class="mt-1 font-mono text-[11px] text-amber-800">"{{ $selectedDocument->catatan_revisi }}"</div>
                        @endif
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Password Akun Anda untuk Verifikasi TTD:</label>
                    <input type="password" 
                           wire:model="accountPassword" 
                           wire:keydown.enter="approveDocument"
                           placeholder="Masukkan password akun Anda..." 
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs">
                    <p class="text-[10px] text-slate-400">Tanda tangan digital akan dibubuhkan secara otomatis pada dokumen PDF.</p>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <x-ts:button color="slate" variant="outline" wire:click="$set('showSignModal', false)">Batal</x-ts:button>
                    <x-ts:button color="emerald" wire:click="approveDocument">Setujui & TTD Dokumen</x-ts:button>
                </div>
            </div>
        @endif
    </x-ts:modal>

    {{-- MODAL REJECT / TOLAK --}}
    <x-ts:modal title="Konfirmasi Penolakan Surat" wire="showRejectModal" center size="md">
        @if ($selectedDocument)
            <div class="space-y-4 text-xs">
                <div class="bg-rose-50 p-3.5 rounded-xl border border-rose-100">
                    <div class="font-bold text-slate-800 text-sm">{{ $selectedDocument->title }}</div>
                    <div class="text-xs font-mono text-rose-600 mt-0.5">Nomor: {{ $selectedDocument->document_number }}</div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-rose-900 block">Alasan / Catatan Penolakan (Wajib Diisi):</label>
                    <textarea wire:model="rejectionReason" 
                              rows="3" 
                              placeholder="Tuliskan catatan perbaikan atau alasan penolakan..." 
                              class="w-full p-2.5 border border-rose-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-xs"></textarea>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <x-ts:button color="slate" variant="outline" wire:click="$set('showRejectModal', false)">Batal</x-ts:button>
                    <x-ts:button color="rose" wire:click="rejectDocument">Tolak Pengajuan Surat</x-ts:button>
                </div>
            </div>
        @endif
    </x-ts:modal>

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
</div>
