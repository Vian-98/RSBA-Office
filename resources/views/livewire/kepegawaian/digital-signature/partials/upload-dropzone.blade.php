{{-- STEP 1: PDF Upload Dropzone --}}
<div class="w-full">
    <div class="flex items-center justify-between mb-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih / Ganti Berkas PDF</label>
        @if ($pdf_file)
            <span class="text-xs text-emerald-600 font-bold flex items-center">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Berkas PDF Terverifikasi
            </span>
        @endif
    </div>

    <div class="relative border-2 border-dashed {{ $pdf_file ? 'border-indigo-400 bg-indigo-50/20' : 'border-slate-300 bg-slate-50/60' }} hover:border-indigo-500 rounded-2xl p-5 text-center transition-all group cursor-pointer w-full">
        <input type="file" wire:model="pdf_file" accept="application/pdf,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="text-sm font-bold text-slate-800">
                        @if ($pdf_file)
                            <span class="text-indigo-600">{{ $pdf_file->getClientOriginalName() }}</span>
                        @else
                            <span>Klik untuk unggah berkas PDF</span> atau seret berkas ke sini
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Format .PDF (Ukuran Maksimal 10 MB)</p>
                </div>
            </div>

            @if ($pdf_file)
                <div class="text-right text-xs">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full font-mono bg-indigo-100 text-indigo-800 font-semibold">
                        {{ $fileSizeFormatted }}
                    </span>
                </div>
            @endif
        </div>
    </div>
    @error('pdf_file') <span class="text-xs text-rose-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
</div>
