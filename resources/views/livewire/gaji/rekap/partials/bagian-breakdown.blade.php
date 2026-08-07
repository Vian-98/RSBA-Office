<!-- Distribusi Gaji per Bagian -->
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs h-full flex flex-col justify-between space-y-4">
    <div>
        <h2 class="text-md font-bold text-slate-800">Distribusi Gaji per Bagian</h2>
        <p class="text-xs text-slate-400 mt-0.5">Analisis porsi pengeluaran payroll bulan {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}</p>
    </div>

    @if(empty($bagianBreakdown))
        <div class="py-12 text-center text-slate-400 my-auto">
            <x-tabler-database-x class="mx-auto h-10 w-10 text-slate-300 mb-3" />
            <p class="text-xs">Tidak ada data penggajian untuk periode ini.</p>
        </div>
    @else
        @php
            $maxBagianSum = collect($bagianBreakdown)->max('total_gaji_bersih') ?: 1;
        @endphp

        <div class="flex-1 overflow-y-auto pr-1.5 space-y-3.5 max-h-[260px] scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
            @foreach($bagianBreakdown as $bagName => $data)
                @php
                    $widthPercent = ($data['total_gaji_bersih'] / $maxBagianSum) * 100;
                @endphp
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="truncate mr-2">
                            <span class="font-bold text-slate-700">{{ $bagName }}</span>
                            <span class="text-slate-400 ml-1">({{ $data['karyawan_count'] }} staf)</span>
                        </div>
                        <span class="font-extrabold text-slate-800 shrink-0">Rp {{ number_format($data['total_gaji_bersih'], 0, ',', '.') }}</span>
                    </div>
                    <!-- Bar gauge -->
                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div style="width: {{ $widthPercent }}%" class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
