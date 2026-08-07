<div class="col-span-1 sm:col-span-2 mt-4 bg-indigo-50/50 border border-indigo-100/70 rounded-2xl p-4 text-slate-700 text-xs">
    <div class="flex items-center gap-1.5 font-bold text-indigo-800 mb-3 border-b border-indigo-100 pb-1.5">
        <x-tabler-calculator class="h-4.5 w-4.5 text-indigo-600" />
        <span>Rekonsiliasi PPh 21 Tahunan (Desember)</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
        <div class="flex justify-between">
            <span class="text-slate-500">Akumulasi Bruto (Jan-Nov):</span>
            <span class="font-bold">Rp {{ number_format($ytd_prior_bruto, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Bruto Bulan Ini (Des):</span>
            <span class="font-bold">Rp {{ number_format($calc_total_gaji, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between sm:col-span-2 border-t border-dashed border-indigo-100/70 pt-2 font-semibold text-indigo-950">
            <span>Total Bruto Setahun (YTD):</span>
            <span>Rp {{ number_format($ytd_total_bruto, 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between">
            <span class="text-slate-500">Biaya Jabatan (Max 6jt):</span>
            <span class="font-bold">-Rp {{ number_format($ytd_biaya_jabatan, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">BPJS Ketenagakerjaan YTD:</span>
            <span class="font-bold">-Rp {{ number_format($ytd_total_bpjs_tk, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between sm:col-span-2 border-t border-dashed border-indigo-100/70 pt-2 font-semibold text-indigo-950">
            <span>Neto Setahun (YTD):</span>
            <span>Rp {{ number_format($ytd_neto, 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between">
            <span class="text-slate-500">Status PTKP (1 Jan):</span>
            <span class="font-bold text-indigo-700">{{ $selectedKaryawan ? ($selectedKaryawan->ptkp_status ?: 'TK0') : 'TK0' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">PTKP Setahun:</span>
            <span class="font-bold">-Rp {{ number_format($ytd_ptkp, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between sm:col-span-2 border-t border-dashed border-indigo-100/70 pt-2 font-semibold text-indigo-950">
            <span>Penghasilan Kena Pajak (PKP):</span>
            <span>Rp {{ number_format($ytd_pkp, 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between">
            <span class="text-slate-500">Total PPh 21 Setahun:</span>
            <span class="font-bold text-slate-800">Rp {{ number_format($ytd_tax_annual, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">PPh 21 Paid (Jan-Nov):</span>
            <span class="font-bold text-emerald-600">-Rp {{ number_format($ytd_paid_jan_nov, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between sm:col-span-2 border-t border-slate-200/80 pt-2 font-bold text-indigo-700 text-xs">
            <span>PPh 21 Bulan Desember (Selisih):</span>
            <span>Rp {{ number_format($form_pph21_calculated, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
