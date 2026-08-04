<div class="flex flex-col gap-4">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 p-5 shadow-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white">Audit Bank Surat</h1>
                    <p class="text-xs text-slate-400">Laporan keaslian surat — sumber data: Docstore (immutable)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($dataLoaded)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-400">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Terhubung ke Docstore
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-500/20 px-3 py-1 text-xs font-semibold text-red-400">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Docstore Tidak Tersedia
                    </span>
                @endif
                <button wire:click="refresh" class="rounded-lg bg-slate-600 p-2 text-slate-300 hover:bg-slate-500 hover:text-white transition-colors" title="Refresh">
                    <svg wire:loading.class="animate-spin" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         ERROR STATE
         ============================================================ --}}
    @if ($errorMsg)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800">Gagal Terhubung ke Bank Surat</p>
                    <p class="mt-1 text-sm text-red-700">{{ $errorMsg }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         STATISTIK RINGKASAN
         ============================================================ --}}
    @if ($dataLoaded)
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-medium text-slate-500">Total Surat</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ number_format($this->paginationMeta['total']) }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-emerald-100">
                <p class="text-xs font-medium text-emerald-600">Disetujui</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $this->stats['approved'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">halaman ini</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-amber-100">
                <p class="text-xs font-medium text-amber-600">Dalam Proses</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ $this->stats['pending'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">halaman ini</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-red-100">
                <p class="text-xs font-medium text-red-600">Ditolak</p>
                <p class="mt-1 text-2xl font-bold text-red-700">{{ $this->stats['rejected'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">halaman ini</p>
            </div>
        </div>
    @endif

    {{-- ============================================================
         FILTER
         ============================================================ --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Filter Tipe Surat --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Jenis Surat</label>
                <select wire:model.live="filterType" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="all">Semua Jenis</option>
                    <option value="cuti">Surat Cuti</option>
                    <option value="sp3">SP3 (Pembayaran)</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select wire:model.live="filterStatus" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="all">Semua Status</option>
                    <option value="approved">Disetujui</option>
                    <option value="pending">Menunggu Persetujuan</option>
                    <option value="rejected">Ditolak</option>
                    <option value="manual">TTD Manual</option>
                </select>
            </div>

            {{-- Dari Tanggal --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Dari Tanggal</label>
                <input wire:model.live="dateFrom" type="date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Sampai Tanggal</label>
                <input wire:model.live="dateTo" type="date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Search --}}
        <div class="mt-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nomor surat atau docstore key..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-4 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    {{-- ============================================================
         TABEL DATA
         ============================================================ --}}
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">

        {{-- Loading overlay --}}
        <div wire:loading class="bg-white/80 absolute inset-0 z-10 flex items-center justify-center rounded-xl">
            <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-2 shadow-md">
                <svg class="h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <span class="text-sm text-slate-600">Memuat data dari bank surat...</span>
            </div>
        </div>

        @if (!empty($this->documents))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor Surat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Penandatangan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Sync Terakhir</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Versi</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($this->documents as $doc)
                            <tr class="transition-colors hover:bg-slate-50">
                                {{-- Nomor Surat --}}
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $doc['document_number'] }}</div>
                                    <div class="font-mono text-xs text-slate-400 mt-0.5" title="Docstore Key">
                                        {{ Str::limit($doc['docstore_key'], 18) }}
                                    </div>
                                </td>

                                {{-- Jenis Surat --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        {{ $doc['document_type'] === 'cuti' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                        {{ strtoupper($doc['document_type']) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3">
                                    @php
                                        $docStatusLower = strtolower($doc['status'] ?? '');
                                        $isManual = !empty($doc['is_manual']) || in_array($docStatusLower, ['manual', 'approved manual', 'disetujui manual']);
                                        
                                        if ($isManual) {
                                            $statusLabel = '✍️ Disetujui Manual';
                                            $statusClass = 'bg-indigo-100 text-indigo-800 border border-indigo-200';
                                        } elseif ($docStatusLower === 'approved') {
                                            $statusLabel = 'Disetujui';
                                            $statusClass = 'bg-emerald-50 text-emerald-700';
                                        } elseif ($docStatusLower === 'rejected') {
                                            $statusLabel = 'Ditolak';
                                            $statusClass = 'bg-red-50 text-red-700';
                                        } else {
                                            $statusLabel = 'Menunggu';
                                            $statusClass = 'bg-amber-50 text-amber-700';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                {{-- Penandatangan --}}
                                <td class="px-4 py-3">
                                    @if (!empty($doc['manual_signers']))
                                        <div class="flex flex-col gap-1">
                                            @foreach ($doc['manual_signers'] as $msig)
                                                <div class="flex items-center gap-1.5 text-xs text-indigo-900 font-medium">
                                                    <span>✍️</span>
                                                    <span>{{ $msig['signer_name'] }}</span>
                                                    <span class="text-[10px] text-indigo-600">({{ $msig['signer_role'] ?? 'TTD Basah' }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif (!empty($doc['signatures']))
                                        <div class="flex flex-col gap-0.5">
                                            @foreach (array_slice($doc['signatures'], 0, 2) as $sig)
                                                @php
                                                    $isSigManual = !empty($sig['is_manual']) || in_array(strtolower($sig['status'] ?? ''), ['manual', 'approved manual']);
                                                @endphp
                                                <div class="flex items-center gap-1 text-xs text-slate-600">
                                                    <div class="h-1.5 w-1.5 rounded-full
                                                        {{ $isSigManual ? 'bg-indigo-600' : ($sig['status'] === 'approved' ? 'bg-emerald-500' : ($sig['status'] === 'rejected' ? 'bg-red-500' : 'bg-amber-400')) }}">
                                                    </div>
                                                    <span>{{ $sig['signer_name'] }}</span>
                                                    @if ($isSigManual)
                                                        <span class="text-[10px] text-indigo-600 font-semibold">(TTD Basah)</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if (count($doc['signatures']) > 2)
                                                <span class="text-xs text-slate-400">+{{ count($doc['signatures']) - 2 }} lainnya</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ada penandatangan</span>
                                    @endif
                                </td>

                                {{-- Sync Terakhir --}}
                                <td class="px-4 py-3">
                                    @if ($doc['synced_at'])
                                        <div class="text-xs text-slate-600">
                                            {{ \Carbon\Carbon::parse($doc['synced_at'])->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ \Carbon\Carbon::parse($doc['synced_at'])->translatedFormat('H:i') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Data lama</span>
                                    @endif
                                </td>

                                {{-- Versi --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                        v{{ $doc['version'] ?? 1 }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ $this->getVerifyUrl($doc['docstore_key']) }}"
                                       target="_blank"
                                       title="Verifikasi surat di verify app"
                                       class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                <div class="text-xs text-slate-500">
                    Menampilkan {{ ($this->paginationMeta['current_page'] - 1) * $this->paginationMeta['per_page'] + 1 }}
                    –
                    {{ min($this->paginationMeta['current_page'] * $this->paginationMeta['per_page'], $this->paginationMeta['total']) }}
                    dari {{ number_format($this->paginationMeta['total']) }} surat
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="previousPage"
                        @if ($this->paginationMeta['current_page'] <= 1) disabled @endif
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 transition-colors">
                        ← Sebelumnya
                    </button>
                    <span class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                        {{ $this->paginationMeta['current_page'] }} / {{ $this->paginationMeta['last_page'] }}
                    </span>
                    <button wire:click="nextPage"
                        @if ($this->paginationMeta['current_page'] >= $this->paginationMeta['last_page']) disabled @endif
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 transition-colors">
                        Berikutnya →
                    </button>
                </div>
            </div>

        @elseif ($dataLoaded)
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700">Tidak Ada Surat Ditemukan</h3>
                <p class="mt-1 text-sm text-slate-400">Coba ubah filter atau kata pencarian</p>
            </div>
        @endif
    </div>

    {{-- ============================================================
         INFO KEAMANAN
         ============================================================ --}}
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-emerald-800">Keamanan Data Surat</p>
                <p class="mt-1 text-xs leading-relaxed text-emerald-700">
                    Data pada halaman ini diambil langsung dari <strong>bank surat (docstore)</strong> yang bersifat immutable — bukan dari database office.
                    Setiap surat yang ditampilkan di sini adalah data yang <strong>telah terverifikasi keasliannya</strong>.
                    Jika ada perbedaan antara data di sini dengan tampilan di modul office, data di halaman ini adalah yang sahih.
                    Klik tombol <strong>Verifikasi</strong> untuk membuka verify app dan memvalidasi keaslian surat secara kriptografis.
                </p>
            </div>
        </div>
    </div>

</div>
