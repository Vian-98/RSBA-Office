<div class="space-y-6">
    {{-- Header Banner & Action Button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 rounded-2xl text-white shadow-lg border border-slate-800">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-500/20 border border-indigo-400/30 rounded-xl text-indigo-300 backdrop-blur-md">
                <x-tabler-archive class="size-8" />
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    Arsip Surat Resmi (Bank Surat Docstore)
                    <span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <x-tabler-lock class="size-3" /> Source of Truth
                    </span>
                </h1>
                <p class="text-xs text-slate-300 mt-1">
                    Pusat arsip terpusat dari seluruh jenis dokumen resmi RS Bintang Amin yang tersinkronisasi ke Bank Surat Vault Docstore.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="openModalKategori" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-xl shadow-md transition-all border border-indigo-400/30 cursor-pointer">
                <x-tabler-plus class="size-4" />
                <span>Tambah Jenis Arsip</span>
            </button>
        </div>
    </div>

    {{-- Alert jika Docstore Offline --}}
    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3 shadow-xs">
            <x-tabler-alert-triangle class="size-5 text-rose-600 flex-shrink-0 mt-0.5" />
            <div>
                <strong class="font-bold block text-sm mb-0.5">Koneksi Bank Surat Docstore Terkendala</strong>
                <span>{{ $errorMessage }}</span>
                <p class="text-[11px] text-rose-600 mt-1">
                    💡 Pastikan server Docstore aktif di <code>http://localhost:8000/api</code>.
                </p>
            </div>
        </div>
    @endif

    {{-- Filter & Tab Kategori Arsip --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {{-- Navigation Tabs Kategori --}}
        <div class="border-b border-slate-200 bg-slate-50/60 p-2 overflow-x-auto">
            <div class="flex items-center gap-1.5 min-w-max">
                <button 
                    wire:click="$set('filterType', 'all')" 
                    type="button"
                    class="px-4 py-2 text-xs font-semibold rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $filterType === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/70' }}"
                >
                    <x-tabler-files class="size-4" />
                    <span>Semua Kategori</span>
                    @if(isset($docstoreData['meta']['total']))
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $filterType === 'all' ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $docstoreData['meta']['total'] }}
                        </span>
                    @endif
                </button>

                @foreach ($kategoriList as $cat)
                    @php
                        $isActive = $filterType === $cat->kode;
                    @endphp
                    <button 
                        wire:click="$set('filterType', '{{ $cat->kode }}')" 
                        type="button"
                        class="px-3.5 py-2 text-xs font-semibold rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $isActive ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/70' }}"
                    >
                        @switch($cat->icon)
                            @case('school')
                                <x-tabler-school class="size-4" />
                                @break
                            @case('microscope')
                                <x-tabler-microscope class="size-4" />
                                @break
                            @case('clipboard-list')
                                <x-tabler-clipboard-list class="size-4" />
                                @break
                            @case('file-alert')
                                <x-tabler-file-alert class="size-4" />
                                @break
                            @case('receipt-2')
                                <x-tabler-receipt-2 class="size-4" />
                                @break
                            @default
                                <x-tabler-file-text class="size-4" />
                        @endswitch
                        <span>{{ $cat->nama }}</span>
                        @if (!$cat->is_system)
                            <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold rounded-full {{ $isActive ? 'bg-indigo-400 text-white' : 'bg-amber-100 text-amber-800' }}">Kustom</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Form Pencarian & Range Filter --}}
        <div class="p-4 bg-white border-b border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
            {{-- Search Bar --}}
            <div class="md:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-tabler-search class="size-4" />
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari Nomor Surat / Docstore Key / Nama Terkait..." 
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50"
                />
            </div>

            {{-- Status Filter --}}
            <div>
                <select wire:model.live="filterStatus" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50">
                    <option value="all">Semua Status</option>
                    <option value="approved">VALID / Approved</option>
                    <option value="manual">DISETUJUI MANUAL</option>
                    <option value="pending">PENDING</option>
                    <option value="rejected">DITOLAK</option>
                </select>
            </div>

            {{-- Reset Filters Button --}}
            <div class="flex justify-end">
                <button wire:click="resetFilters" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors w-full md:w-auto justify-center cursor-pointer">
                    <x-tabler-refresh class="size-3.5" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

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
                                            <x-tabler-lock class="size-3" /> VALID & VAULT SIGNED
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
                                        <span class="font-mono">Docstore Key: {{ Str::limit($doc['docstore_key'], 18) }}</span>
                                        <span>•</span>
                                        <span>Selesai Sync: {{ $syncedAtIndo }}</span>
                                        <span>•</span>
                                        <span class="font-bold text-indigo-600">v{{ $doc['version'] ?? 1 }}</span>
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
    </div>

    {{-- MODAL TAMBAH JENIS ARSIP BARU --}}
    <x-ts:modal wire:model="modalKategori" title="Tambah Jenis / Kategori Arsip Surat" blur>
        <form wire:submit.prevent="simpanKategori" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Jenis / Kategori Surat <span class="text-rose-500">*</span></label>
                <input 
                    wire:model.live="newNama" 
                    type="text" 
                    placeholder="Contoh: Surat Keputusan Direktur, Memo Internal" 
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                />
                @error('newNama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kode Unik Kategori <span class="text-rose-500">*</span></label>
                <input 
                    wire:model="newKode" 
                    type="text" 
                    placeholder="sk_direktur" 
                    class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                />
                <span class="text-[10px] text-slate-400 block mt-0.5">Gunakan huruf kecil dan garis bawah (contoh: <code>kuitansi</code>, <code>memo_internal</code>).</span>
                @error('newKode') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Icon Kategori</label>
                <select wire:model="newIcon" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="file-text">📄 File Text (Default)</option>
                    <option value="school">🎓 Kampus / Akademik</option>
                    <option value="microscope">🔬 Penelitian / Lab</option>
                    <option value="clipboard-list">📋 Checklist / Perintah Tugas</option>
                    <option value="file-alert">⚠️ Peringatan / SP3</option>
                    <option value="receipt-2">🧾 Kuitansi / Keuangan</option>
                    <option value="briefcase">💼 Kepegawaian / SDM</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi Kategori</label>
                <textarea 
                    wire:model="newDeskripsi" 
                    rows="2" 
                    placeholder="Penjelasan singkat mengenai kategori arsip ini..." 
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <x-ts:button x-on:click="$modalKategori = false" color="slate" variant="flat" size="sm">
                    Batal
                </x-ts:button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                    Simpan Kategori Baru
                </button>
            </div>
        </form>
    </x-ts:modal>

    {{-- DRAWER / MODAL DETAIL DOKUMEN DOCSTORE --}}
    <x-ts:modal wire:model="modalDetail" title="Detail Arsip Bank Surat (Docstore Vault)" size="2xl" blur>
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
                {{-- Header Box --}}
                <div class="p-3.5 bg-slate-900 text-white rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-indigo-300 font-mono block uppercase tracking-wider">Docstore Key</span>
                        <span class="font-mono font-bold text-xs select-all">{{ $selectedDoc['meta']['docstore_key'] ?? '-' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Immutability Version: v{{ $selectedDoc['meta']['version'] ?? 1 }}
                    </span>
                </div>

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

                {{-- JSON Payload Preview --}}
                <div>
                    <span class="text-slate-700 font-bold block mb-1">Rincian Konten Surat (JSON Payload):</span>
                    <pre class="p-3 bg-slate-950 text-indigo-300 rounded-xl text-[11px] font-mono overflow-x-auto max-h-60 leading-relaxed">{{ json_encode($sContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </x-ts:modal>
</div>
