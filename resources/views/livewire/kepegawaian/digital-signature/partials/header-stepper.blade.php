{{-- RSBA Digital Signature Header & Workflow Stepper --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
    <div class="flex items-center space-x-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Studio Tanda Tangan Digital RS Bintang Amin</h2>
            <p class="text-xs text-slate-500">Isi identitas di sisi kiri, geser & atur ukuran stempel pada pratinjau PDF di sisi kanan</p>
        </div>
    </div>

    {{-- Stepper Badges --}}
    <div class="flex items-center space-x-2 text-xs">
        <span class="px-4 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-indigo-600 text-white shadow-sm' }}">
            1. Unggah PDF
        </span>
        <span class="text-slate-300">➔</span>
        <span class="px-4 py-2 rounded-xl font-bold {{ $pdf_file ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
            2. Identitas, Stempel & Ukuran
        </span>
        <span class="text-slate-300">➔</span>
        <span class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-400">
            3. Password & Sign
        </span>
    </div>
</div>
