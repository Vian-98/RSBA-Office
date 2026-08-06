<!-- Payroll Input Modal -->
<x-ts:modal wire="isInputModalOpen" size="4xl" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-calculator class="h-5 w-5 text-indigo-500" />
            {{ $isLocked ? 'Rincian Data Gaji (Terkunci)' : 'Input Data Gaji' }} - Periode {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}
        </span>
    </x-slot:title>

    @if($selectedKaryawan)
        <div x-data="{
            formatNominal(val) {
                if (val === 0 || val === '0') return '0';
                return String(val || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        }" class="p-2 space-y-5">
            <!-- Employee Summary Header -->
            <div class="bg-indigo-50/50 border border-indigo-100/75 rounded-2xl p-4 flex flex-col md:flex-row justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Identitas Karyawan</span>
                    <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $selectedKaryawan->full_nama }}</h3>
                    <p class="text-xs text-slate-500">NIP: {{ $selectedKaryawan->nip }} | Status: {{ $selectedKaryawan->status->nama() }}</p>
                </div>
                <div class="md:text-right space-y-1 text-slate-600 text-xs">
                    <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block">Kualifikasi</span>
                    <p>Jabatan: <b class="text-slate-700">{{ $selectedKaryawan->jabatan->first()?->nama ?? '-' }}</b></p>
                    <p>Masa Kerja: <b class="text-slate-700">{{ $selectedKaryawan->masakerja }}</b></p>
                </div>
            </div>

            @if($carriedOverFromPeriode)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-2xs flex items-start gap-3">
                    <x-tabler-alert-circle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
                    <div>
                        <span class="font-bold">Info Salin Data:</span> Data pada form ini otomatis disalin dari slip gaji periode <span class="font-bold">{{ \Carbon\Carbon::parse($carriedOverFromPeriode . '-01')->translatedFormat('F Y') }}</span>. Silakan periksa dan sesuaikan sebelum disimpan.
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="savePayroll" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
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
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

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
            </form>
        </div>
    @endif
</x-ts:modal>
