<!-- Parameter Payroll Modal -->
<x-ts:modal id="modal-payroll-parameters" title="Parameter & Alokasi Payroll" size="2xl" class="relative z-50">
    <form wire:submit.prevent="saveParameters" class="space-y-5 p-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <x-ts:input label="UMK Kantor (Rupiah)" wire:model.defer="config_umk" type="text" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
            </div>
            <div>
                <x-ts:input label="Potongan Telat (Rupiah/Kejadian)" wire:model.defer="config_potongan_telat" type="text" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
            </div>
            <div>
                <x-ts:input label="Toleransi Telat (Menit)" wire:model.defer="config_toleransi_telat" type="number" suffix="Min" />
            </div>
        </div>

        <!-- Alokasi Tunjangan (25% UMK) -->
        <div class="border-t border-slate-100 pt-4 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alokasi Tunjangan (25% UMK)</span>
                <x-ts:button type="button" size="xs" color="indigo" outline wire:click="addAllocation" class="text-[10px] font-bold py-1 px-2.5">
                    + Tambah
                </x-ts:button>
            </div>
            
            <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                @foreach($allocations as $index => $alloc)
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl space-y-2 relative">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Kategori #{{ $index + 1 }}</span>
                            <button type="button" wire:click="removeAllocation({{ $index }})" class="text-red-500 hover:text-red-700">
                                <x-tabler-trash class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <x-ts:input label="Nama Tunjangan" wire:model.defer="allocations.{{ $index }}.nama" placeholder="Tunjangan Tetap / Absensi" />
                            </div>
                            <div class="flex gap-2 items-center">
                                <div class="flex-1">
                                    <x-ts:input label="Porsi (%)" wire:model.defer="allocations.{{ $index }}.persen" type="number" min="0" max="100" suffix="%" />
                                </div>
                                <div class="pt-5 pl-2">
                                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                        <input type="checkbox" wire:model.defer="allocations.{{ $index }}.is_absensi" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" />
                                        <span class="text-xs font-semibold text-slate-600">Absensi?</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="p-3 bg-indigo-50/50 rounded-lg text-[10px] text-slate-500 border border-indigo-100/50 leading-relaxed">
                <span class="font-bold text-indigo-700 block mb-0.5">Catatan:</span>
                Total persentase dari seluruh alokasi yang ditambahkan harus tepat <strong>100%</strong> agar alokasi tunjangan bernilai pas 25% dari UMK.
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
            <x-ts:button type="button" flat color="slate" x-on:click="$tsui.close.modal('modal-payroll-parameters')">Batal</x-ts:button>
            <x-ts:button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">
                Simpan Parameter
            </x-ts:button>
        </div>
    </form>
</x-ts:modal>
