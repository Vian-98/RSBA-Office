<div class="space-y-6">
    <!-- Header Page -->
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-800">Master Tunjangan Golongan</h1>
        <p class="text-sm text-slate-500">Kelola nominal tunjangan melekat per golongan karyawan (Grade 1 - 15).</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Right Panel: Golongan Allowances -->
        <div class="space-y-6">
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
