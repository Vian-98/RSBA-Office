<!-- Right Panel: Potongan -->
<div class="space-y-4 md:pl-10 md:border-l md:border-slate-200">
    <span class="text-xs font-bold text-red-600 uppercase tracking-wider block pb-1 border-b border-red-200">Komponen Potongan & Pajak (-)</span>
    
    <div class="space-y-3">
        <!-- Row 1: Absensi & Cash Bon -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Absensi</span>
                    @if($calculatedLateCount > 0)
                        <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">{{ $calculatedLateCount }}x Terlambat ({{ $calculatedLateMinutes }} mnt)</span>
                    @endif
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_absensi" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Cash Bon</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_cash_bon" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>
        
        <!-- Row 2: Obat & Lain-Lain -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Obat / Rawat</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_obat" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Lain-Lain</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_lain" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>
        
        <!-- Row 3: BPJS Kesehatan & BPJS Ketenagakerjaan (Manual Input) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">BPJS Kesehatan</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_bpjs_kes" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">BPJS Ketenagakerjaan</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_bpjs_tk" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>
        
        <!-- Row 4: Bank & Keluarga Add-on -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Potongan Bank</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_bank" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Keluarga Add-on BPJS</span>
                    <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">+1% / kepala</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_bpjs_keluarga_tambahan" type="number" min="0" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
            </div>
        </div>

        <!-- Row 5: PPh Pasal 21 & Override Controls -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 pt-4 mt-2">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <div class="flex items-center gap-1.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Pajak PPh Pasal 21</span>
                        @if($form_pph21_is_overridden)
                            <span class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">Manual (Override)</span>
                        @else
                            <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">Auto (Sistem)</span>
                        @endif
                    </div>
                    @if($form_pph21_is_overridden && !$isLocked)
                        <button type="button" wire:click="resetPph21ToAuto" class="text-[9px] font-bold text-indigo-650 hover:underline">Reset ke Auto</button>
                    @endif
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_potongan_pph21" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            @if($form_pph21_is_overridden)
                <div>
                    <div class="flex justify-between items-end h-8 mb-1">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Alasan Perubahan Pajak <span class="text-red-500">*</span></span>
                    </div>
                    <x-ts:input wire:model="form_pph21_override_reason" type="text" :disabled="$isLocked" placeholder="Wajib diisi, cth: Pajak Natura / Koreksi PTKP" class="text-xs" />
                </div>
            @else
                <div class="flex flex-col justify-center text-[9px] font-semibold text-slate-400 mt-6 leading-normal">
                    <span>* Pajak bulanan dihitung berdasarkan status PTKP & UMK dengan tarif TER PMK 168/2023.</span>
                    <span>* Ubah angka di samping untuk meng-override secara manual.</span>
                </div>
            @endif

            @if($form_pph21_is_overridden && $form_pph21_calculated > 0 && abs((double)$form_potongan_pph21 - (double)$form_pph21_calculated) / (double)$form_pph21_calculated > 0.2)
                <div class="col-span-1 sm:col-span-2 mt-2 bg-amber-50 border border-amber-100 rounded-xl p-3 flex gap-2 items-start text-amber-700 text-xs">
                    <x-tabler-alert-triangle class="h-4.5 w-4.5 text-amber-500 shrink-0 mt-0.5" />
                    <div>
                        <span class="font-bold block">Peringatan: Perubahan Signifikan</span>
                        <span>Nilai PPh 21 manual yang Anda masukkan (Rp {{ number_format((double)$form_potongan_pph21, 0, ',', '.') }}) berbeda lebih dari 20% dibandingkan hasil hitung otomatis sistem (Rp {{ number_format((double)$form_pph21_calculated, 0, ',', '.') }}). Pastikan alasan yang dimasukkan sudah benar.</span>
                    </div>
                </div>
            @endif

            @if($is_december)
                @include('livewire.gaji.partials.modal-input.ytd-card')
            @endif
        </div>
    </div>
</div>
