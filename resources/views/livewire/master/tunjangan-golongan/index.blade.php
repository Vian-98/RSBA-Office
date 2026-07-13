<div class="space-y-6">
    <!-- Header Page -->
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-800">Master Konfigurasi Penggajian</h1>
        <p class="text-sm text-slate-500">Kelola UMK, persentase alokasi tunjangan, dan nominal tunjangan melekat per golongan karyawan.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Panel: Global Settings -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-slate-700 mb-4 flex items-center gap-2">
                    <x-tabler-settings class="h-5 w-5 text-indigo-500" />
                    Pengaturan Global
                </h2>
                
                <form wire:submit.prevent="saveSettings" class="space-y-4">
                    <div>
                        <x-ts:input label="Nilai UMK (Rupiah)" wire:model.defer="umk" type="number" placeholder="Contoh: 3000000" prefix="Rp" />
                    </div>

                    <div class="border-t border-slate-100 pt-4 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alokasi Tunjangan (25% UMK)</span>
                            <x-ts:button type="button" size="xs" color="indigo" outline wire:click="addAllocation" class="text-xs">
                                + Tambah
                            </x-ts:button>
                        </div>
                        
                        <div class="space-y-4">
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
                        
                        <div class="p-3 bg-indigo-50/50 rounded-lg text-xs text-slate-500 border border-indigo-100/50">
                            <span class="font-bold text-indigo-700 block mb-1">Catatan:</span>
                            Total persentase dari seluruh alokasi yang ditambahkan harus tepat **100%** agar alokasi tunjangan bernilai pas 25% dari UMK.
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-ts:button type="submit" loading="saveSettings" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full justify-center">
                            Simpan Pengaturan
                        </x-ts:button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Panel: Golongan Allowances -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-slate-700 mb-4 flex items-center gap-2">
                    <x-tabler-coin class="h-5 w-5 text-indigo-500" />
                    Tunjangan Golongan (Grade 1 - 15)
                </h2>
                
                <form wire:submit.prevent="saveAllowances" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach($golongansList as $gol)
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-xl space-y-1.5 hover:border-slate-200 transition-colors">
                                <span class="text-xs font-semibold text-slate-500 block">Golongan {{ $gol->golongan }}</span>
                                <x-ts:input wire:model.defer="allowances.{{ $gol->golongan }}" type="number" min="0" placeholder="0" prefix="Rp" />
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <x-ts:button type="submit" loading="saveAllowances" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
                            Update Tunjangan Golongan
                        </x-ts:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
