<div class="space-y-4">
    {{-- Header Banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-amber-500 text-white rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-900">Perlu Persetujuan & Tanda Tangan Anda</h3>
                <p class="text-xs text-amber-700">Daftar dokumen bertingkat yang saat ini sedang menunggu giliran persetujuan dari Anda.</p>
            </div>
        </div>
        <span class="px-3 py-1 bg-amber-200 text-amber-900 text-xs font-bold rounded-full">
            {{ $pendingDocs->count() }} Menunggu
        </span>
    </div>

    {{-- Pending Approvals Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3">Pengirim & Dokumen</th>
                        <th class="px-4 py-3">Nomor Surat</th>
                        <th class="px-4 py-3">Tingkat Persetujuan</th>
                        <th class="px-4 py-3">Tgl Masuk</th>
                        <th class="px-4 py-3 text-center">Aksi Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pendingDocs as $index => $doc)
                        @php
                            $myAppr = $doc->approvals->where('user_id', auth()->id())->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800 text-sm">{{ $doc->title }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">Diajukan oleh: <span class="font-medium text-slate-700">{{ $doc->user?->name ?? 'User' }}</span></div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-indigo-600 font-semibold whitespace-nowrap">
                                {{ $doc->document_number }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800">
                                    Tier {{ $myAppr?->step_order ?? 1 }} dari {{ $doc->approvals->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $doc->created_at ? $doc->created_at->diffForHumans() : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-2">
                                <button wire:click="openSignModal({{ $doc->id }})" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Setujui & TTD
                                </button>
                                <button wire:click="openRejectModal({{ $doc->id }})" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Tolak Pengajuan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-medium text-slate-600">Tidak Ada Dokumen Menunggu Persetujuan</p>
                                <p class="text-xs text-slate-400 mt-1">Saat ini belum ada pengajuan dokumen bertingkat yang memerlukan tanda tangan Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Confirm Approval & Sign --}}
    <x-ts:modal title="Konfirmasi Persetujuan & Tanda Tangan" wire="showSignModal" center size="md">
        @if ($selectedDocument)
            <div class="space-y-4">
                <div class="bg-emerald-50 border border-emerald-200 p-3.5 rounded-xl">
                    <div class="font-bold text-emerald-900 text-sm">{{ $selectedDocument->title }}</div>
                    <div class="text-xs font-mono text-emerald-700 mt-0.5">{{ $selectedDocument->document_number }}</div>
                    <div class="text-xs text-emerald-800 mt-2">
                        Anda akan menyetujui dan membubuhi Tanda Tangan Digital pada tingkat <strong>Tier {{ $selectedApproval?->step_order }}</strong>.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Verifikasi Password Akun Anda *
                    </label>
                    <input type="password" 
                           wire:model="accountPassword" 
                           placeholder="Masukkan password akun..." 
                           class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showSignModal', false)" 
                            class="px-4 py-2 text-xs font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">
                        Batal
                    </button>
                    <button wire:click="approveDocument" 
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm">
                        Proses Persetujuan & TTD
                    </button>
                </div>
            </div>
        @endif
    </x-ts:modal>

    {{-- Modal Reject with Mandatory Feedback --}}
    <x-ts:modal title="Penolakan Pengajuan Dokumen" wire="showRejectModal" center size="lg">
        @if ($selectedDocument)
            <div class="space-y-4">
                <div class="bg-rose-50 border border-rose-200 p-3.5 rounded-xl">
                    <div class="font-bold text-rose-900 text-sm">{{ $selectedDocument->title }}</div>
                    <div class="text-xs font-mono text-rose-700 mt-0.5">{{ $selectedDocument->document_number }}</div>
                    <div class="text-xs text-rose-800 mt-2">
                        Pengirim: <strong>{{ $selectedDocument->user?->name }}</strong>. Menolak pengajuan ini akan membatalkan alur persetujuan bertingkat.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-rose-800 uppercase tracking-wider mb-1.5">
                        Catatan Feedback / Alasan Penolakan (Wajib Diisi) *
                    </label>
                    <textarea wire:model="rejectionReason" 
                              rows="4" 
                              placeholder="Tuliskan catatan feedback atau alasan spesifik mengapa pengajuan dokumen ini ditolak..." 
                              class="w-full p-3 text-sm border border-rose-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 bg-rose-50/30"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Catatan feedback ini akan secara otomatis dikirimkan via notifikasi kepada pengirim dokumen dan ditampilkan pada portal verifikasi.</p>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showRejectModal', false)" 
                            class="px-4 py-2 text-xs font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">
                        Batal
                    </button>
                    <button wire:click="rejectDocument" 
                            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-sm">
                        Kirim Penolakan & Feedback
                    </button>
                </div>
            </div>
        @endif
    </x-ts:modal>
</div>
