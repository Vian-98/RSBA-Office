<!-- Parameter Aktif Card -->
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs space-y-4 flex-1">
    <div class="flex items-center justify-between border-b border-slate-50 pb-3">
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Parameter Aktif</h2>
            <p class="text-[10px] text-slate-400 mt-0.5">Konfigurasi payroll saat ini</p>
        </div>
        <button type="button" x-on:click="$tsui.open.modal('modal-payroll-parameters')" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center gap-1">
            <x-tabler-edit class="h-3.5 w-3.5" />
            Edit
        </button>
    </div>

    <div class="flex-1 flex flex-col justify-between space-y-5">
        <!-- Config rows -->
        <div class="divide-y divide-slate-100/70">
            <div class="flex justify-between items-center py-3">
                <span class="text-slate-500 text-xs font-semibold">UMK Kantor (Base)</span>
                <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_umk, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-slate-500 text-xs font-semibold">Potongan Telat (Per Kejadian)</span>
                <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_potongan_telat, 0, ',', '.') }} / kejadian</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-slate-500 text-xs font-semibold">Toleransi Telat</span>
                <span class="font-extrabold text-slate-800 text-sm">{{ $config_toleransi_telat }} menit</span>
            </div>
        </div>

        <!-- Allocations summary -->
        <div class="border-t border-slate-100 pt-4">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Porsi Tunjangan (25% UMK)</span>
            <div class="divide-y divide-slate-100/70">
                @foreach($allocations as $alloc)
                    <div class="flex justify-between items-center py-2.5 text-xs">
                        <span class="text-slate-500 font-semibold flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $alloc['is_absensi'] ? 'bg-amber-500' : 'bg-indigo-500' }}"></span>
                            {{ $alloc['nama'] }}
                        </span>
                        <span class="font-extrabold text-slate-800 text-sm">{{ $alloc['persen'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Insight Bulanan -->
        <div class="border-t border-slate-100 pt-4 mt-auto">
            <div class="rounded-xl bg-indigo-600 p-5 text-white shadow-xs space-y-2.5">
                <div class="flex items-center gap-2 text-[10px] font-bold tracking-wider uppercase opacity-90">
                    <x-tabler-bulb class="h-4.5 w-4.5 shrink-0 text-amber-300 animate-pulse" />
                    <span>Insight Bulanan</span>
                </div>
                <p class="text-xs leading-relaxed opacity-95 font-medium">
                    {{ $insightText }}
                </p>
            </div>
        </div>
    </div>
</div>
