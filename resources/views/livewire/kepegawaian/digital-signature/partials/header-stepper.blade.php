{{-- Mekari Sign Header & Workflow Stepper --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
    <div class="flex items-center space-x-3">
        <div class="p-3 bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white rounded-xl shadow-md shadow-indigo-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Studio Tanda Tangan Digital (Mekari Sign Workflow)</h2>
            <p class="text-xs text-slate-500">Isi identitas di sisi kiri, geser & atur ukuran stempel pada pratinjau PDF di sisi kanan</p>
        </div>
    </div>

    {{-- Stepper Badges --}}
    <div class="flex items-center space-x-2 text-xs">
        <span class="px-3.5 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-indigo-600 text-white shadow-sm' }}">
            1. Unggah PDF
        </span>
        <span class="text-slate-300">➔</span>
        <span class="px-3.5 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
            2. Identitas, Stempel & Ukuran
        </span>
        <span class="text-slate-300">➔</span>
        <span class="px-3.5 py-2 rounded-xl font-bold bg-slate-100 text-slate-400">
            3. Password & Sign
        </span>
    </div>
</div>
