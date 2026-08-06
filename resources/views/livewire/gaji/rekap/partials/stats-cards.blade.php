<!-- Quick Stats Section -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <!-- Card: Gaji Bersih -->
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
        <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-indigo-50/40 blur-xl"></div>
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Gaji Bersih</span>
            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                <x-tabler-cash class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-[5px]">
            <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}</h3>
            <div class="mt-2.5 flex items-center gap-1.5">
                @if($percentChange > 0)
                    <span class="inline-flex items-center gap-0.5 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[12px] font-bold text-emerald-700 border border-emerald-100/60">
                        <x-tabler-trending-up class="h-3 w-3" />
                        +{{ number_format($percentChange, 1) }}%
                    </span>
                @elseif($percentChange < 0)
                    <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-50 px-1.5 py-0.5 text-[12px] font-bold text-rose-700 border border-rose-100/60">
                        <x-tabler-trending-down class="h-3 w-3" />
                        {{ number_format($percentChange, 1) }}%
                    </span>
                @else
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-1.5 py-0.5 text-[12px] font-bold text-slate-500 border border-slate-100/60">
                        Stagnan
                    </span>
                @endif
                <span class="text-[12px] text-slate-400 font-medium">vs bulan lalu (Rp {{ number_format($lastMonthNet, 0, ',', '.') }})</span>
            </div>
        </div>
    </div>

    <!-- Card: Total Potongan -->
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
        <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-rose-50/30 blur-xl"></div>
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Potongan</span>
            <div class="flex items-center gap-1.5">
                <button type="button" x-on:click="$tsui.open.modal('modal-potongan-breakdown')" class="text-rose-600 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-md text-[11px] font-bold border border-rose-100 transition-colors flex items-center gap-1">
                    <x-tabler-list-details class="h-3.5 w-3.5" />
                    Rincian
                </button>
                <div class="rounded-lg bg-rose-50 p-2 text-rose-600">
                    <x-tabler-receipt-off class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="mt-[5px]">
            <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h3>
            <div class="mt-2.5 flex items-center justify-between text-[12px] text-slate-400 font-medium">
                <span>Dihitung dari {{ $jumlahKaryawan }} slip gaji bulan ini.</span>
                <button type="button" x-on:click="$tsui.open.modal('modal-potongan-breakdown')" class="text-rose-600 font-bold hover:underline">
                    Lihat 8 Kategori &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Card: Estimasi Bulan Depan -->
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
        <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-amber-50/40 blur-xl"></div>
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estimasi Beban Rutin Bulan Depan</span>
            <div class="rounded-lg bg-amber-50 p-2 text-amber-600">
                <x-tabler-calculator class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-[5px]">
            <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($estimasiBulanDepan, 0, ',', '.') }}</h3>
            <div class="mt-2.5 flex items-center gap-1.5 text-[12px] text-slate-400 font-medium">
                <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-50 px-1.5 py-0.5 text-[12px] font-bold text-amber-700 border border-amber-100/60">
                    Info
                </span>
                <span>Lembur & THR dikecualikan karena fluktuatif.</span>
            </div>
        </div>
    </div>
</div>
