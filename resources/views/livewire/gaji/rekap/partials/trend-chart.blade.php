<!-- Interactive Trend Chart -->
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs">
    <h2 class="text-md font-bold text-slate-800 mb-1">Grafik Tren Pengeluaran Gaji</h2>
    <p class="text-xs text-slate-400 mb-6">Klik pada grafik batang untuk mengganti periode analisis.</p>

    @php
        $maxNet = collect($trendMonths)->max('total_gaji_bersih') ?: 1;
    @endphp

    <div class="h-64 flex items-end justify-between gap-4 pt-6 border-b border-slate-100 pb-2">
        @foreach($trendMonths as $item)
            @php
                $heightPercent = ($item['total_gaji_bersih'] / $maxNet) * 100;
                $isActive = $item['periode'] === $this->periode;
            @endphp
            <div class="flex flex-col items-center flex-1 h-full justify-end group">
                <!-- Tooltip / Label value -->
                <div class="mb-2 text-[10px] font-extrabold text-slate-800 bg-slate-950 text-white px-2 py-1 rounded-md shadow-lg hidden group-hover:block transition-all duration-200">
                    Rp {{ number_format($item['total_gaji_bersih'], 0, ',', '.') }}
                </div>
                
                <!-- Bar -->
                <div 
                    wire:click="setPeriode('{{ $item['periode'] }}')"
                    style="height: calc({{ $heightPercent }}% - 20px)" 
                    class="w-full rounded-t-lg transition-all duration-300 cursor-pointer shadow-3xs hover:opacity-90 
                           {{ $isActive ? 'bg-gradient-to-t from-indigo-600 to-indigo-500 ring-2 ring-indigo-200 ring-offset-2' : 'bg-slate-200 hover:bg-slate-300' }}">
                </div>

                <!-- Label -->
                <span class="mt-2.5 text-[10px] font-semibold {{ $isActive ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                    {{ \Carbon\Carbon::parse($item['periode'] . '-01')->translatedFormat('M y') }}
                </span>
            </div>
        @endforeach
    </div>
</div>
