<!-- Modal Rincian Total Potongan -->
<x-ts:modal id="modal-potongan-breakdown" title="Rincian Total Potongan Payroll" size="lg" class="relative z-50">
    <div class="space-y-4">
        <div class="p-4 bg-rose-50/60 border border-rose-100 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 block">Total Seluruh Potongan</span>
                <h2 class="text-2xl font-black text-rose-950 mt-0.5">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h2>
                <span class="text-xs text-rose-700 font-medium">Periode: {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }} ({{ $jumlahKaryawan }} Karyawan)</span>
            </div>
            <div class="p-3 bg-rose-500 text-white rounded-xl shadow-sm">
                <x-tabler-receipt-off class="h-7 w-7" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            @php
                $items = [
                    ['label' => 'BPJS Kesehatan', 'key' => 'bpjs_kes', 'icon' => 'tabler-heart-rate-monitor', 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-700'],
                    ['label' => 'BPJS Ketenagakerjaan', 'key' => 'bpjs_tk', 'icon' => 'tabler-shield-check', 'color' => 'bg-blue-500', 'textColor' => 'text-blue-700'],
                    ['label' => 'PPh 21 (Pajak)', 'key' => 'pph21', 'icon' => 'tabler-receipt-tax', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-700'],
                    ['label' => 'Potongan Absensi / Telat', 'key' => 'absensi', 'icon' => 'tabler-clock-off', 'color' => 'bg-rose-500', 'textColor' => 'text-rose-700'],
                    ['label' => 'Potongan Cash Bon', 'key' => 'cash_bon', 'icon' => 'tabler-wallet-off', 'color' => 'bg-purple-500', 'textColor' => 'text-purple-700'],
                    ['label' => 'Potongan Obat / Rawat', 'key' => 'obat', 'icon' => 'tabler-pill', 'color' => 'bg-cyan-500', 'textColor' => 'text-cyan-700'],
                    ['label' => 'Potongan Bank', 'key' => 'bank', 'icon' => 'tabler-building-bank', 'color' => 'bg-indigo-500', 'textColor' => 'text-indigo-700'],
                    ['label' => 'Potongan Lain-Lain', 'key' => 'lain', 'icon' => 'tabler-dots', 'color' => 'bg-slate-500', 'textColor' => 'text-slate-700'],
                ];
            @endphp

            @foreach($items as $item)
                @php
                    $val = $potonganBreakdown[$item['key']] ?? 0;
                    $pct = $totalPotongan > 0 ? ($val / $totalPotongan) * 100 : 0;
                @endphp
                <div class="p-3.5 bg-white border border-slate-100 rounded-xl shadow-2xs flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-sm">
                            {{ number_format($pct, 1) }}%
                        </span>
                    </div>
                    <div class="flex items-baseline justify-between pt-1">
                        <span class="text-base font-extrabold text-slate-800">Rp {{ number_format($val, 0, ',', '.') }}</span>
                    </div>
                    <!-- Progress bar -->
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        <div class="{{ $item['color'] }} h-full rounded-full" style="width: {{ min(100, max(0, $pct)) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <x-slot:footer>
        <div class="flex justify-end">
            <x-ts:button size="sm" flat color="slate" x-on:click="$tsui.close.modal('modal-potongan-breakdown')">Tutup</x-ts:button>
        </div>
    </x-slot:footer>
</x-ts:modal>
