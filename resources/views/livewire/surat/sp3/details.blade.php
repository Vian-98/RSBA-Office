<div class="flex flex-col gap-4 text-slate-800">
    {{-- Alert jika status ditolak --}}
    @if ($suratSp3->status === \App\Enums\StatusApproval::REJECTED)
        @php
            $rejectedAppr = $suratSp3->approvals()->where('status', 'rejected')->latest('id')->first();
            $rejectedLog = $suratSp3->logs()->where('status', 'rejected')->latest('id')->first();
            $alasan = $rejectedLog?->catatan ?: ($rejectedAppr?->keterangan ?: 'Tidak ada keterangan penolakan.');
            $penolak = $rejectedLog?->nama_pelaku ?: ($rejectedAppr?->disetujuiOleh?->karyawan?->full_nama ?? $rejectedAppr?->disetujuiOleh?->name ?? 'Verifikator');
        @endphp
        <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-900 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-600 text-white shadow-xs">
                    <x-tabler-alert-triangle class="size-5" />
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-sm text-red-800">Surat SP3 Ini Ditolak</h4>
                    <p class="mt-1 text-xs text-red-700 leading-relaxed">
                        Alasan Penolakan: <b class="text-red-900 font-semibold">{{ $alasan }}</b>
                        <span class="text-red-600">(oleh <u class="font-medium">{{ $penolak }}</u>)</span>
                    </p>
                    <p class="mt-1.5 text-[11px] text-red-600 font-medium">
                        💡 Anda dapat melakukan perbaikan data rincian atau keterangan melalui tombol <b>"Edit & Ajukan Ulang"</b> di bawah.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Info Header SP3 --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
        <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white text-xs font-bold">#</span>
                <span class="text-sm font-bold tracking-tight text-slate-900">{{ $suratSp3->no }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $suratSp3->status->color() === 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($suratSp3->status->color() === 'danger' ? 'bg-red-100 text-red-800 border border-red-200' : ($suratSp3->status->color() === 'warning' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200')) }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $suratSp3->status->color() === 'success' ? 'bg-emerald-500' : ($suratSp3->status->color() === 'danger' ? 'bg-red-500' : ($suratSp3->status->color() === 'warning' ? 'bg-amber-500' : 'bg-blue-500')) }}"></span>
                {{ $suratSp3->status->nama() }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3">
            <div class="flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penerima / Rekanan</span>
                <span class="text-sm font-bold text-slate-800">{{ $suratSp3->rekanan }}</span>
                <span class="text-xs text-slate-500">{{ date('d M Y', strtotime($suratSp3->tgl)) }}</span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode Pembayaran</span>
                <span class="text-xs font-bold text-indigo-700 uppercase">{{ $suratSp3->method_bayar }}</span>
                <span class="text-[11px] text-slate-500">Dibuat oleh: <b class="text-slate-700">{{ $suratSp3->dibuatOleh ?? '-' }}</b></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject / Berita</span>
                <span class="text-xs text-slate-700 leading-relaxed">{{ $suratSp3->keterangan ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Rincian Pembayaran --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <x-tabler-receipt class="size-4 text-indigo-500" />
                Rincian Pembayaran
            </span>
        </div>

        <x-table-static :$headers :rows="$this->rows()" headerless />
        
        <div class="mt-3 flex w-full justify-between items-center rounded-lg bg-slate-50 px-4 py-3 border border-slate-100">
            <span class="text-xs font-bold text-slate-600">Total Pembayaran:</span>
            <span class="text-lg font-black text-indigo-600">{{ formatRupiah($suratSp3->details->sum('nominal')) }}</span>
        </div>

        {{-- Tanda Tangan Format SP3 Klasik (Mengetahui: Atasan / Direktur) --}}
        <div class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
            @if ($this->ttdAtasan)
                @if ($this->ttdAtasan['is_manual'])
                    <div class="flex flex-col items-center text-center min-w-[160px] p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-600">Mengetahui,</span>
                        <span class="text-[10px] text-indigo-600 font-semibold mt-0.5">Persetujuan Manual</span>
                        <span class="block h-10 w-auto"></span>
                        <span class="text-slate-900 font-bold text-xs underline">{{ $this->ttdAtasan['nama'] }}</span>
                        <span class="text-[10px] text-slate-500">({{ $this->ttdAtasan['jabatan'] }})</span>
                        <span class="text-[9px] font-light text-slate-400 mt-0.5">TTD Basah</span>
                    </div>
                @else
                    <div class="flex flex-col items-center text-center min-w-[160px] p-3 rounded-lg bg-indigo-50/30 border border-indigo-100 shadow-xs">
                        <span class="text-xs font-bold text-slate-700">Mengetahui,</span>
                        <span class="text-[10px] font-semibold text-indigo-600 mt-0.5">Disetujui Digital</span>
                        <div class="my-1.5 p-1 bg-white rounded border border-indigo-100 shadow-2xs">
                            @if(!empty($this->ttdAtasan['barcode']))
                                <img src="data:image/png;base64,{{ $this->ttdAtasan['barcode'] }}" alt="Barcode Tanda Tangan" class="h-16 w-16">
                            @else
                                <img src="data:image/png;base64,{{ $this->generateBarcode }}" alt="Barcode Tanda Tangan" class="h-16 w-16">
                            @endif
                        </div>
                        <span class="text-indigo-900 font-bold text-xs underline">{{ $this->ttdAtasan['nama'] }}</span>
                        <span class="text-[10px] text-slate-600 font-medium">{{ $this->ttdAtasan['jabatan'] }}</span>
                        <span class="text-[9px] font-light text-slate-400 mt-0.5">{{ $this->ttdAtasan['approved_at'] }}</span>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center text-center min-w-[160px] p-3 rounded-lg bg-amber-50/60 border border-dashed border-amber-200">
                    <span class="text-xs font-bold text-slate-700">Mengetahui,</span>
                    <div class="my-2 flex flex-col items-center">
                        <x-tabler-clock class="size-5 text-amber-500" />
                        <span class="text-[11px] font-semibold text-amber-700 mt-1">Menunggu Persetujuan</span>
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ optional($suratSp3->jabatans)->nama ?? 'Atasan / Direktur' }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================
         RIWAYAT & LOG PERSETUJUAN (AUDIT TRAIL CLEAN TABLE/FEED)
         ============================================================ --}}
    {{-- ============================================================
         RIWAYAT & LOG PERSETUJUAN (ACCORDION / MINIMIZE)
         ============================================================ --}}
    <div x-data="{ showLogs: false }" class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-xs transition-all">
        <div 
            x-on:click="showLogs = !showLogs"
            class="flex items-center justify-between bg-slate-50/90 hover:bg-slate-100/90 px-4 py-3 cursor-pointer select-none transition"
        >
            <div class="flex items-center gap-2">
                <x-tabler-history class="size-4.5 text-indigo-600" />
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Riwayat & Log Persetujuan</span>
                <span class="rounded-full bg-slate-200/80 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">
                    {{ count($this->logs) }} Riwayat
                </span>
            </div>

            <div class="flex items-center gap-1 text-xs font-bold text-indigo-600">
                <span x-text="showLogs ? 'Sembunyikan' : 'Buka Log'" class="text-[11px]"></span>
                <x-tabler-chevron-down 
                    class="size-4 text-indigo-600 transition-transform duration-200" 
                    x-bind:class="showLogs ? 'rotate-180' : ''" 
                />
            </div>
        </div>

        <div x-show="showLogs" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="divide-y divide-slate-100 border-t border-slate-100">

            @forelse ($this->logs as $log)
                @php
                    $statusStr = strtolower($log->status ?? '');
                    $isApproved = in_array($statusStr, ['approved', 'disetujui']);
                    $isRejected = $statusStr === 'rejected';
                    $isManual   = $statusStr === 'manual';
                    $isPending  = in_array($statusStr, ['pending', 'waiting']);

                    if ($isApproved) {
                        $iconBg = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-300';
                        $statusLabel = 'DISETUJUI';
                    } elseif ($isRejected) {
                        $iconBg = 'bg-red-100 text-red-700 border-red-200';
                        $statusBadge = 'bg-red-50 text-red-700 border-red-300';
                        $statusLabel = 'DITOLAK';
                    } elseif ($isManual) {
                        $iconBg = 'bg-indigo-100 text-indigo-700 border-indigo-200';
                        $statusBadge = 'bg-indigo-50 text-indigo-700 border-indigo-300';
                        $statusLabel = 'MANUAL (TTD BASAH)';
                    } else {
                        $iconBg = 'bg-blue-100 text-blue-700 border-blue-200';
                        $statusBadge = 'bg-blue-50 text-blue-700 border-blue-300';
                        $statusLabel = 'MENUNGGU';
                    }
                @endphp
                <div class="p-4 flex items-start gap-3.5 hover:bg-slate-50/50 transition">
                    {{-- Ikon Status (In-Flow, Bersih, Tidak Menimpa Teks) --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border {{ $iconBg }} shadow-2xs">
                        @if($isApproved)
                            <x-tabler-check class="size-5 stroke-[2.5]" />
                        @elseif($isRejected)
                            <x-tabler-x class="size-5 stroke-[2.5]" />
                        @elseif($isManual)
                            <x-tabler-pencil class="size-5 stroke-[2]" />
                        @else
                            <x-tabler-send class="size-5 stroke-[2]" />
                        @endif
                    </div>

                    {{-- Konten Utama --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-800">{{ $log->aksi }}</span>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-bold tracking-wide {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <span class="text-xs text-slate-400 font-medium flex items-center gap-1 font-mono">
                                <x-tabler-clock class="size-3.5 text-slate-400" />
                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-600 mb-1">
                            <span class="font-bold text-slate-700">{{ $log->nama_pelaku }}</span>
                            @if(!empty($log->jabatan_pelaku))
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-500 font-medium">{{ $log->jabatan_pelaku }}</span>
                            @endif
                        </div>

                        @if(!empty($log->catatan))
                            <div class="mt-2 rounded-lg p-2.5 text-xs {{ $isRejected ? 'bg-red-50 border border-red-200 text-red-800 font-medium' : 'bg-slate-50 border border-slate-200 text-slate-700' }}">
                                <span class="font-bold text-[11px] block {{ $isRejected ? 'text-red-600' : 'text-slate-500' }}">Catatan:</span>
                                {{ $log->catatan }}
                            </div>
                        @endif

                        @if(!empty($log->perubahan) && is_array($log->perubahan))
                            <div class="mt-2.5 rounded-lg border border-amber-200 bg-amber-50/60 p-2.5 text-xs">
                                <div class="flex items-center gap-1.5 font-bold text-amber-900 text-[11px] mb-1.5">
                                    <x-tabler-pencil class="size-3.5 text-amber-600" />
                                    <span>Rincian Perubahan Data:</span>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach ($log->perubahan as $chg)
                                        <div class="flex items-start gap-2 bg-white rounded p-1.5 border border-amber-100/90 text-xs">
                                            <span class="font-bold text-slate-700 min-w-[130px] shrink-0 text-[11px]">{{ $chg['field'] ?? 'Data' }}:</span>
                                            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                                                <span class="line-through text-red-600 bg-red-50 px-1.5 py-0.5 rounded text-[11px] font-medium">{{ $chg['dari'] ?: '-' }}</span>
                                                <x-tabler-arrow-right class="size-3 text-slate-400 shrink-0" />
                                                <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold text-[11px]">{{ $chg['menjadi'] ?: '-' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400 italic">Belum ada riwayat persetujuan tercatat.</div>
            @endforelse
        </div>
    </div>

    {{-- Tombol Aksi di Bawah Modal --}}
    <div class="ml-auto flex flex-row justify-end gap-2 pt-1">
        @if ($suratSp3->status === \App\Enums\StatusApproval::REJECTED || $suratSp3->status === \App\Enums\StatusApproval::PENDING)
            @if ($suratSp3->created_by === auth()->id() || auth()->user()->hasRole('Super-Admin'))
                <x-ts:button sm color="info" icon="tabler.edit" x-on:click="$dispatch('open-modal', {id: 'modal-edit-sp3'}); $dispatch('close-modal', {id: 'modal-detail-sp3'})">
                    Edit & Ajukan Ulang
                </x-ts:button>
            @endif
        @endif

        {{-- Tombol Cetak hanya aktif jika SP3 disetujui atau manual --}}
        @if ($suratSp3->status === \App\Enums\StatusApproval::APPROVED || $suratSp3->status === \App\Enums\StatusApproval::MANUAL)
            <div id="print-sp3" class="hidden">
                <livewire:Surat.Sp3.PrintSp3 :$suratSp3 />
            </div>
            <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-sp3')">Print</x-ts:button>
        @endif
    </div>

</div>
