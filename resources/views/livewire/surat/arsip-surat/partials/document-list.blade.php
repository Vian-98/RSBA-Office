{{-- Grid Card / Tabel Arsip Surat --}}
<div class="divide-y divide-slate-100">
    @if ($docstoreData && !empty($docstoreData['data']))
        @foreach ($docstoreData['data'] as $doc)
            @php
                $typeObj = $kategoriList->firstWhere('kode', $doc['document_type']);
                $typeName = $typeObj ? $typeObj->nama : strtoupper(str_replace('_', ' ', $doc['document_type']));
                $docContent = $doc['content'] ?? [];
                $syncedAtIndo = !empty($doc['synced_at']) ? \Carbon\Carbon::parse($doc['synced_at'])->translatedFormat('d M Y H:i') : '-';
            @endphp

            <div class="p-4 hover:bg-slate-50/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex-shrink-0 mt-1">
                        <x-tabler-file-check class="size-5" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="font-mono font-bold text-slate-800 text-sm">
                                {{ $doc['document_number'] ?: 'No. -' }}
                            </span>

                            {{-- Badge Kategori --}}
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $typeName }}
                            </span>

                            {{-- Badge Status Docstore --}}
                            @if ($doc['is_manual'] ?? false)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                    <x-tabler-check class="size-3" /> DISETUJUI MANUAL
                                </span>
                            @elseif (strtolower($doc['status'] ?? '') === 'approved')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <x-tabler-lock class="size-3" /> DISETUJUI
                                </span>
                            @elseif (in_array(strtolower($doc['status'] ?? ''), ['rejected', 'ditolak']))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    DITOLAK
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    PENDING
                                </span>
                            @endif
                        </div>

                        {{-- Meta Sub-info --}}
                        <div class="text-xs text-slate-500 space-y-0.5">
                            @if(!empty($docContent['tujuan_universitas']))
                                <div><strong>Kampus/Tujuan:</strong> Universitas {{ $docContent['tujuan_universitas'] }}</div>
                            @elseif(!empty($docContent['karyawan_name']))
                                <div><strong>Karyawan:</strong> {{ $docContent['karyawan_name'] }} ({{ $docContent['jenis_cuti'] ?? 'Cuti' }})</div>
                            @elseif(!empty($docContent['rekanan']))
                                <div><strong>Rekanan:</strong> {{ $docContent['rekanan'] }}</div>
                            @elseif(!empty($docContent['perihal']))
                                <div><strong>Perihal:</strong> {{ $docContent['perihal'] }}</div>
                            @endif

                            <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-1">
                                <span>Selesai Sync: {{ $syncedAtIndo }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-2 self-end md:self-center">
                    <button 
                        wire:click="showDetail('{{ $doc['docstore_key'] }}')" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition-colors shadow-2xs cursor-pointer"
                    >
                        <x-tabler-eye class="size-4" />
                        <span>Detail Arsip</span>
                    </button>

                    <a 
                        href="http://localhost:5173/?key={{ $doc['docstore_key'] }}" 
                        target="_blank" 
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        title="Buka Portal Verifikasi Publik"
                    >
                        <x-tabler-external-link class="size-3.5" />
                        <span>Verify</span>
                    </a>
                </div>
            </div>
        @endforeach

        {{-- Pagination Meta --}}
        @if(!empty($docstoreData['meta']))
            <div class="p-4 bg-slate-50/60 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <div>
                    Menampilkan Halaman <strong>{{ $docstoreData['meta']['current_page'] }}</strong> dari <strong>{{ $docstoreData['meta']['last_page'] }}</strong> (Total {{ $docstoreData['meta']['total'] }} Dokumen)
                </div>
                <div class="flex items-center gap-2">
                    @if($docstoreData['meta']['current_page'] > 1)
                        <button wire:click="$set('page', {{ $docstoreData['meta']['current_page'] - 1 }})" type="button" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-medium cursor-pointer">
                            &laquo; Sebelum
                        </button>
                    @endif

                    @if($docstoreData['meta']['current_page'] < $docstoreData['meta']['last_page'])
                        <button wire:click="$set('page', {{ $docstoreData['meta']['current_page'] + 1 }})" type="button" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-medium cursor-pointer">
                            Berikut &raquo;
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="p-12 text-center text-slate-400">
            <x-tabler-folder-off class="size-12 mx-auto mb-3 text-slate-300" />
            <p class="font-bold text-sm text-slate-600">Tidak ada dokumen tersimpan di kategori ini</p>
            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter pencarian atau pastikan surat di office telah mengalami approval.</p>
        </div>
    @endif
</div>
