<!-- Edit History Section (Non-printable) -->
@if(!empty($selectedSlip['edit_logs']) && count($selectedSlip['edit_logs']) > 0)
    <div class="mt-6 border-t border-slate-200 pt-6 px-6 pb-4">
        <div class="flex items-center gap-2 mb-4">
            <x-tabler-history class="h-5 w-5 text-slate-500" />
            <h3 class="font-bold text-slate-800 text-sm">Riwayat Perubahan Data Gaji</h3>
        </div>
        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
            @foreach($selectedSlip['edit_logs'] as $log)
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs text-slate-600">
                    <div class="flex justify-between items-start gap-4 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="rounded-full bg-slate-200 text-slate-700 w-6 h-6 flex items-center justify-center font-bold text-[10px]">
                                {{ strtoupper(substr($log->editor_name, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-bold text-slate-800">{{ $log->editor_name }}</span>
                                <span class="text-slate-400 text-[10px] ml-1.5">• Mengubah data</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-semibold bg-white border border-slate-200/60 rounded-md px-2 py-0.5 shadow-sm">
                            {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 mt-2.5 pl-8">
                        @foreach($log->perubahan as $col => $change)
                            <div class="flex items-center justify-between py-1 border-b border-dashed border-slate-200 last:border-0">
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
    </div>
@endif
