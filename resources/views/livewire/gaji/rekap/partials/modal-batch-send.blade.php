<!-- Modal Pengiriman Massal Email (Instant Batch Queue) -->
<x-ts:modal wire="isBatchSendModalOpen" title="Kirim Massal Slip Gaji (Background Queue)" size="lg" class="relative z-50">
    <div class="space-y-5" @if($isBatchSending) wire:poll.1s="refreshBatchProgress" @endif>
        <div class="p-4 bg-sky-50 border border-sky-100 rounded-2xl flex items-start gap-3">
            <div class="p-2 bg-sky-500 text-white rounded-xl shrink-0">
                <x-tabler-rocket class="h-5 w-5" />
            </div>
            <div class="text-xs text-sky-900 space-y-1">
                <span class="font-bold block text-sm">Pengiriman Email Massal via Background Queue</span>
                <p class="text-sky-700">
                    Pengiriman email slip gaji untuk periode <b>{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</b> akan dimasukkan ke antrean background (*Queue Job*) secara instant (< 1 detik). Anda bebas menutup modal ini.
                </p>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                <span>Progres Pengiriman Antrean Background</span>
                <span class="font-mono text-sky-700 font-bold">{{ $batchProcessedCount }} / {{ $batchTotalCount }} Karyawan</span>
            </div>

            @php
                $percentage = $batchTotalCount > 0 ? round(($batchProcessedCount / $batchTotalCount) * 100) : 0;
            @endphp
            <div class="w-full bg-slate-200 rounded-full h-3.5 overflow-hidden p-0.5 border border-slate-300">
                <div class="bg-gradient-to-r from-sky-500 to-indigo-600 h-2.5 rounded-full transition-all duration-300 shadow-xs" style="width: {{ $percentage }}%"></div>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center text-xs pt-1">
                <div class="p-2 bg-emerald-50 border border-emerald-100 rounded-xl">
                    <span class="block text-[10px] uppercase font-bold text-emerald-600">Berhasil Terkirim</span>
                    <span class="text-base font-black text-emerald-700">{{ $batchSuccessCount }}</span>
                </div>
                <div class="p-2 bg-rose-50 border border-rose-100 rounded-xl">
                    <span class="block text-[10px] uppercase font-bold text-rose-600">Gagal</span>
                    <span class="text-base font-black text-rose-700">{{ $batchFailedCount }}</span>
                </div>
                <div class="p-2 bg-slate-100 border border-slate-200 rounded-xl">
                    <span class="block text-[10px] uppercase font-bold text-slate-500">Sisa Antrean</span>
                    <span class="text-base font-black text-slate-700">{{ max(0, $batchTotalCount - $batchProcessedCount) }}</span>
                </div>
            </div>
        </div>

        @if($isBatchSending)
            <div class="p-3.5 bg-sky-50/90 border border-sky-200 rounded-xl flex items-center gap-3 text-xs text-sky-900 shadow-xs">
                <x-tabler-loader-2 class="h-5 w-5 animate-spin text-sky-600 shrink-0" />
                <div class="space-y-0.5">
                    <span class="font-bold block text-sky-800">Antrean Background Berjalan...</span>
                    <span class="font-semibold text-sky-700 font-mono text-[11px]">{{ $currentSendingStatus ?: 'Memproses antrean...' }}</span>
                </div>
            </div>
        @endif

        @php
            $rekapModalFailedLogs = \App\Models\Sdm\PayrollSendLog::with('karyawan')
                ->where('periode', $periode)
                ->where('status', 'failed')
                ->orderByDesc('id')
                ->get();
        @endphp

        @if($rekapModalFailedLogs->count() > 0)
            <div class="p-4 bg-rose-50/80 border border-rose-200 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-rose-900 font-bold text-xs">
                        <x-tabler-alert-triangle class="h-4 w-4 text-rose-600 shrink-0" />
                        <span>Detail Email Slip Gaji Gagal Terkirim ({{ $rekapModalFailedLogs->count() }} Karyawan)</span>
                    </div>
                </div>

                <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                    @foreach($rekapModalFailedLogs as $failedLog)
                        <div class="p-3 bg-white border border-rose-200 rounded-xl flex items-center justify-between text-xs gap-3 shadow-xs hover:border-rose-300 transition-colors">
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-slate-900">{{ $failedLog->karyawan->full_nama ?? 'Karyawan ID: ' . $failedLog->karyawan_id }}</span>
                                    <span class="text-[10px] px-2 py-0.5 bg-slate-100 text-slate-700 font-mono rounded-md border border-slate-200">
                                        {{ $failedLog->email ?? 'Tanpa Email' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-rose-700 font-medium leading-relaxed bg-rose-50/60 p-1.5 rounded-lg border border-rose-100">
                                    <span class="font-bold text-rose-900">Alasan Gagal:</span> {{ $failedLog->error_message ?? 'Terjadi kesalahan sistem pengiriman email.' }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                <x-ts:button size="xs" color="rose" wire:click="sendSingleEmail({{ $failedLog->karyawan_id }})" loading="sendSingleEmail({{ $failedLog->karyawan_id }})">
                                    <x-tabler-refresh class="h-3 w-3 mr-1" />
                                    Coba Kirim Ulang
                                </x-ts:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-ts:button size="sm" flat color="slate" wire:click="closeBatchSendModal">Tutup</x-ts:button>
            <x-ts:button size="sm" color="sky" wire:click="dispatchBulkQueue" loading="dispatchBulkQueue" :disabled="$isBatchSending || ($batchTotalCount > 0 && $batchProcessedCount >= $batchTotalCount)">
                <x-tabler-rocket class="h-4 w-4 mr-1" />
                {{ $batchProcessedCount > 0 ? 'Mulai Ulang Antrean Background' : 'Mulai Kirim Massal (Queue)' }}
            </x-ts:button>
        </div>
    </x-slot:footer>
</x-ts:modal>
