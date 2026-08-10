<!-- Summary & Previews Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 pt-4 mt-2">
    <!-- Rincian Alokasi UMK (25%) Preview -->
    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-semibold text-slate-650">
        <span class="text-[10px] text-indigo-650 font-bold uppercase tracking-wider block mb-2">Rincian Alokasi UMK (25% UMK)</span>
        @foreach($form_umk_allocations as $alloc)
            <div class="flex justify-between py-0.5">
                <span>{{ $alloc['nama'] }} ({{ $alloc['persen'] }}%)</span>
                <span class="text-slate-800 font-bold">Rp {{ number_format($alloc['nominal'], 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>

    <!-- Automatic Deductions Preview -->
    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2 text-xs font-semibold text-slate-655">
        <span class="text-[10px] text-slate-450 font-bold uppercase tracking-wider block mb-2">Estimasi Potongan Otomatis (Auto)</span>
        <div class="flex justify-between">
            <span>Pot. BPJS Kesehatan (1% + Add-on)</span>
            <span class="text-slate-800 font-bold">Rp {{ number_format($calc_bpjs_kes, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Pot. BPJS Ketenagakerjaan (3%)</span>
            <span class="text-slate-800 font-bold">Rp {{ number_format($calc_bpjs_tk, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>PPh Pasal 21 (TER / Psl 17)</span>
            <span class="text-slate-800 font-bold">Rp {{ number_format($calc_pph21, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<!-- Live Totals Footer -->
<div class="border-t border-slate-100 pt-4 mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50 p-4 rounded-2xl">
    <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm text-slate-500 font-medium w-full sm:w-auto">
        <div>Total Gaji Kotor:</div>
        <div class="font-bold text-slate-700 text-right">Rp {{ number_format($calc_total_gaji, 0, ',', '.') }}</div>
        <div>Total Potongan:</div>
        <div class="font-bold text-slate-700 text-right">Rp {{ number_format($calc_total_potongan + $calc_pph21 + $form_potongan_bank, 0, ',', '.') }}</div>
    </div>
    <div class="text-center sm:text-right w-full sm:w-auto">
        <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block">Gaji Bersih (Penghasilan Netto)</span>
        <span class="text-2xl font-black text-indigo-600 leading-tight">Rp {{ number_format($calc_gaji_bersih, 0, ',', '.') }}</span>
    </div>
</div>

<div class="flex justify-end gap-2 pt-2">
    <x-ts:button type="button" flat color="slate" wire:click="closeInputModal">{{ $isLocked ? 'Tutup' : 'Batal' }}</x-ts:button>
    @if(!$isLocked)
        <x-ts:button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
            Simpan Data Gaji
        </x-ts:button>
    @else
        <span class="inline-flex items-center gap-1 rounded-lg px-4 py-2 text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200">
            <x-tabler-lock class="h-3.5 w-3.5" />
            Terbaca Saja
        </span>
    @endif
</div>
