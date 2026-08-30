{{-- STEP 1: PDF Upload Dropzone with Rich Animated Uploading States --}}
<div 
    x-data="{ isUploading: false, progress: 0 }"
    x-on:livewire-upload-start="isUploading = true; progress = 0"
    x-on:livewire-upload-finish="isUploading = false; progress = 100"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    class="w-full"
>
    <div class="flex items-center justify-between mb-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih / Ganti Berkas PDF</label>
        @if ($pdf_file)
            <span class="text-xs text-emerald-600 font-bold flex items-center">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Berkas PDF Siap Diproses
            </span>
        @endif
    </div>

    {{-- Main Dropzone Box --}}
    <div class="relative border-2 border-dashed {{ $pdf_file ? 'border-indigo-400 bg-indigo-50/20' : 'border-slate-300 bg-slate-50/60' }} hover:border-indigo-500 rounded-2xl p-5 text-center transition-all group cursor-pointer w-full overflow-hidden">
        <input 
            type="file" 
            wire:model="pdf_file" 
            accept="application/pdf,.pdf" 
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
            :disabled="isUploading"
        />

        {{-- Normal Idle State --}}
        <div x-show="!isUploading" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="text-sm font-bold text-slate-800">
                        @if ($pdf_file)
                            <span class="text-indigo-600 font-semibold">{{ $pdf_file->getClientOriginalName() }}</span>
                        @else
                            <span class="group-hover:text-indigo-600 transition-colors">Klik untuk unggah berkas PDF</span> atau seret berkas ke sini
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Format .PDF (Ukuran Maksimal 10 MB)</p>
                </div>
            </div>

            @if ($pdf_file)
                <div class="text-right text-xs">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full font-mono bg-indigo-100 text-indigo-800 font-semibold shadow-xs">
                        {{ $fileSizeFormatted }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Livewire Uploading Animated State --}}
        <div 
            x-show="isUploading" 
            x-cloak
            class="py-3 px-2 flex flex-col items-center justify-center space-y-3 animate-fadeIn"
        >
            <div class="flex items-center space-x-3 text-indigo-600">
                {{-- Spinning Spinner & Cloud Upload Icon --}}
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <svg class="animate-spin w-10 h-10 text-indigo-200" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg class="absolute w-5 h-5 text-indigo-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>Mengunggah Berkas PDF...</span>
                        <span class="text-indigo-600 font-mono text-xs px-2 py-0.5 bg-indigo-50 rounded-full font-bold" x-text="progress + '%'"></span>
                    </div>
                    <p class="text-xs text-slate-400">Mohon tunggu, sistem sedang memverifikasi ukuran dan integritas dokumen.</p>
                </div>
            </div>

            {{-- Smooth Gradient Animated Progress Bar --}}
            <div class="w-full max-w-md bg-slate-100 rounded-full h-2.5 overflow-hidden shadow-inner border border-slate-200/60 p-0.5">
                <div 
                    class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 rounded-full transition-all duration-200 ease-out shadow-sm"
                    :style="`width: ${progress}%`"
                ></div>
            </div>
        </div>

        {{-- Fallback Server Processing Loading Indicator --}}
        <div 
            wire:loading 
            wire:target="pdf_file" 
            x-show="!isUploading"
            class="py-3 px-2 flex items-center justify-center space-x-3 text-indigo-600"
        >
            <svg class="animate-spin w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-bold text-slate-700">Mempersiapkan pratinjau lembar PDF...</span>
        </div>
    </div>

    @error('pdf_file') 
        <div class="flex items-center gap-1.5 text-xs text-rose-500 mt-2 font-semibold bg-rose-50 border border-rose-200/80 rounded-xl p-2.5">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror
</div>
