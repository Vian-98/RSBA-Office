<div class="{{ auth()->check() ? 'w-full' : 'mx-auto max-w-4xl w-full' }}">
    <!-- Header Card -->
    <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 shrink-0">
                    <x-ts:icon name="tabler.file-check" class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-800">Verifikasi Dokumen Digital</h1>
                    <p class="text-xs text-slate-400">Verifikasi keaslian tanda tangan digital surat keputusan / berkas</p>
                </div>
            </div>
            
            @if(auth()->check())
                <div class="sm:ml-auto text-[10px] font-bold uppercase tracking-wider text-indigo-650 bg-indigo-50/50 px-2 py-0.5 rounded border border-indigo-100/50">
                    Mode Admin
                </div>
            @endif
        </div>

        <!-- Tab Radio Selectors -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 rounded-xl bg-slate-50 p-2.5 sm:p-2 border border-slate-100 mb-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:pl-2">Tipe Dokumen:</span>
            <div class="flex gap-4 flex-wrap">
                <x-ts:radio sm wire:model.live.debounce='tab' id="sp3" value="sp3" label="SP3" />
                <x-ts:radio sm wire:model.live.debounce='tab' id="cuti" value="cuti" label="Cuti" />
                <x-ts:radio sm wire:model.live.debounce='tab' id="sppd" value="sppd" label="SPPD" />
            </div>
        </div>

        <!-- Verify Content Slot -->
        <div>
            @if ($tab === 'sp3')
                <livewire:Surat.Sp3.Verify />
            @elseif($tab === 'cuti')
                <livewire:Surat.Cuti.Verify />
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/30 p-8 text-center text-xs text-slate-400">
                    <x-tabler-info-circle class="h-6 w-6 text-slate-350 mx-auto mb-2" />
                    Silakan pilih tipe dokumen di atas untuk melakukan verifikasi.
                </div>
            @endif
        </div>
    </div>
</div>
