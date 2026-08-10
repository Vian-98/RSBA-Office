<!-- Left Panel: Pendapatan -->
<div class="space-y-4">
    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block pb-1 border-b border-indigo-150">Komponen Pendapatan (+)</span>
    
    <div class="space-y-3">
        <!-- Row 1: Gaji Pokok & Tunjangan Tetap -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Gaji Pokok (Base)</span>
                </div>
                <x-ts:input wire:model.defer="form_gaji_pokok" type="text" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Tetap</span>
                </div>
                <x-ts:input wire:model.defer="form_tunjangan_tetap" type="text" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>

        <!-- Row 2: Tunjangan Absensi & Tunjangan Jabatan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Absensi</span>
                </div>
                <x-ts:input wire:model.defer="form_tunjangan_absensi" type="text" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Jabatan</span>
                </div>
                <x-ts:input wire:model.defer="form_tunjangan_jabatan" type="text" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>

        <!-- Row 3: Tunjangan Shift & Tunjangan Radiologi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Shift</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_shift" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Radiologi</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_radiologi" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>

        <!-- Row 4: Tunjangan Lain & Uang Lembur -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Lain (Total)</span>
                </div>
                <x-ts:input wire:model.defer="form_tunjangan_lain" type="text" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Uang Lembur</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_uang_lembur" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
        </div>

        <!-- Row 5: THR & Placeholder -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="flex justify-between items-end h-8 mb-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Hari Raya</span>
                </div>
                <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_hari_raya" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
            </div>
            <div></div>
        </div>

        <!-- Tunjangan Lain-Lain Dinamis Section -->
        <div class="border-t border-slate-100 pt-4 mt-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Tunjangan Lain-Lain Dinamis</span>
            
            @if(!$isLocked)
            <div class="flex flex-col sm:flex-row gap-3 items-end mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="flex-1 w-full">
                    <span class="block text-xs font-semibold text-slate-500 mb-1">Pilih Jenis Tunjangan</span>
                    <select wire:model.defer="temp_allowance_type_id" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($allowanceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <x-ts:input label="Nominal" wire:model.defer="temp_allowance_nominal" type="text" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let attr = Array.from($el.attributes).find(a => a.name.startsWith('wire:model')) || Array.from(($el.querySelector('input') || {}).attributes || []).find(a => a.name.startsWith('wire:model')); if (attr) { let val = $wire.get(attr.value); let inp = $el.querySelector('input') || $el; inp.value = String(val === 0 || val === '0' ? 0 : (val || '')).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }" />
                </div>
                <div>
                    <x-ts:button type="button" wire:click="addTunjanganLain" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto">
                        Tambah
                    </x-ts:button>
                </div>
            </div>
            @endif

            <!-- Table / List of dynamic items -->
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white">
                <table class="w-full border-collapse text-left text-xs text-slate-600">
                    <thead>
                        <tr class="bg-slate-50 font-semibold text-slate-400 border-b border-slate-100">
                            <th class="px-4 py-2">Nama Tunjangan</th>
                            <th class="px-4 py-2 text-right">Nominal</th>
                            @if(!$isLocked)
                                <th class="px-4 py-2 text-center w-16">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($form_tunjangan_lain_items as $index => $item)
                            <tr>
                                <td class="px-4 py-2 font-bold text-slate-700">{{ $item['nama'] }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-800">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                @if(!$isLocked)
                                <td class="px-4 py-2 text-center">
                                    <button type="button" wire:click="removeTunjanganLain({{ $index }})" class="text-red-500 hover:text-red-700">
                                        <x-tabler-trash class="h-4 w-4 mx-auto" />
                                    </button>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isLocked ? '2' : '3' }}" class="px-4 py-3 text-center text-slate-400 italic">Belum ada tunjangan lain-lain tambahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
