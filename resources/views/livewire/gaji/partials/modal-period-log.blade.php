<!-- Period Edit Logs Modal -->
<x-ts:modal wire="isPeriodLogModalOpen" size="4xl" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-history class="h-5 w-5 text-indigo-500" />
            Log Perubahan Data Gaji - Periode {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}
        </span>
    </x-slot:title>

    <div class="p-2 space-y-4">
        <!-- Search bar -->
        <div class="flex items-center gap-3 bg-white p-3 rounded-2xl border border-slate-150 shadow-2xs">
            <div class="relative flex-1">
                <x-ts:input wire:model.live.debounce.300ms="periodLogSearch" placeholder="Cari nama pengubah, karyawan, NIP, bagian, nominal, atau nama komponen..." icon="tabler.search" class="w-full text-xs" />
            </div>
        </div>

        @if(empty($periodLogs))
            <div class="py-12 text-center text-slate-400">
                <x-tabler-database-x class="mx-auto h-12 w-12 text-slate-350 mb-3" />
                <div class="text-sm font-semibold text-slate-700">Belum Ada Riwayat Perubahan</div>
                <p class="text-xs text-slate-400 mt-1">
                    @if(!empty($periodLogSearch))
                        Tidak ada log perubahan yang cocok dengan kata kunci "{{ $periodLogSearch }}".
                    @else
                        Perubahan nominal slip gaji pada periode ini akan dicatat di sini.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                @foreach($periodLogs as $log)
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs">
                        <!-- Editor and Target Employee Info -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-200/60">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800">{{ $log['editor_name'] }}</span>
                                    <span class="text-slate-400 font-medium">• mengedit slip gaji</span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                    <div class="flex items-center gap-1 text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 shadow-3xs">
                                        <x-tabler-user class="h-3.5 w-3.5" />
                                        {{ $log['employee_name'] }} (NIP: {{ $log['employee_nip'] }})
                                    </div>
                                    @if(!empty($log['employee_bagian']))
                                        <div class="flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 shadow-3xs">
                                            <x-tabler-building class="h-3.5 w-3.5" />
                                            Bagian: {{ $log['employee_bagian'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-semibold bg-white border border-slate-200/60 rounded-md px-2 py-0.5 shadow-sm shrink-0">
                                {{ \Carbon\Carbon::parse($log['created_at'])->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>

                        <!-- List of Changes -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 mt-3 pl-1">
                            @foreach($log['perubahan'] as $col => $change)
                                <div class="flex items-center justify-between py-1 border-b border-dashed border-slate-200/60 last:border-0">
                                    <span class="text-slate-500 font-medium">{{ $change['label'] }}</span>
                                    <div class="flex items-center gap-2 font-semibold">
                                        @if($col === 'bpjs_keluarga_tambahan')
                                            <span class="text-slate-400 font-normal line-through">{{ $change['old'] }}</span>
                                            <x-tabler-arrow-narrow-right class="h-3 w-3 text-slate-400" />
                                            <span class="text-indigo-600">{{ $change['new'] }}</span>
                                        @else
                                            <span class="text-slate-400 font-normal line-through">Rp {{ number_format($change['old'], 0, ',', '.') }}</span>
                                            <x-tabler-arrow-narrow-right class="h-3 w-3 text-slate-400" />
                                            <span class="text-indigo-600">Rp {{ number_format($change['new'], 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="flex justify-end">
            <x-ts:button size="sm" flat color="slate" wire:click="closePeriodLogModal">Tutup</x-ts:button>
        </div>
    </x-slot:footer>
</x-ts:modal>
