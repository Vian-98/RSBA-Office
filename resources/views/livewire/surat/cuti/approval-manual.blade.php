<div class="flex flex-col gap-5 p-2">
    <div class="text-sm text-slate-500 bg-indigo-50/50 border border-indigo-100/50 rounded-xl p-4 flex items-start gap-2.5">
        <x-ts:icon name="tabler.info-circle" class="w-5 h-5 text-indigo-500 mt-0.5" />
        <span>Print pengajuan cuti secara manual, mintalah persetujuan tanda tangan fisik kepada atasan yang bersangkutan, kemudian kumpulkan lembar fisik tersebut ke bagian SDM.</span>
    </div>

    <div class="grid grid-cols-1 {{ count($this->approvalOptions) > 1 ? 'md:grid-cols-2' : '' }} gap-4 text-sm">
        @if (count($this->approvalOptions) > 1)
            <div class="w-full">
                <span class="block mb-1.5 font-semibold text-slate-700">Mengetahui</span>
                <x-ts:select.styled wire:model.defer='mengetahui' :options="$this->approvalOptions" select="label:nama|value:value" placeholder="Pilih yang mengetahui..." />
            </div>
        @endif

        <div class="w-full">
            <span class="block mb-1.5 font-semibold text-slate-700">Menyetujui <span class="text-rose-500">*</span></span>
            <x-ts:select.styled wire:model.defer='menyetujui' :options="$this->approvalOptions" select="label:nama|value:value" placeholder="Pilih yang menyetujui..." />
        </div>
    </div>

    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-2">
        <x-ts:button sm outline color="slate" x-on:click="$dispatch('close-modal',{id:'modal-options-approval-manual'})">Batal</x-ts:button>
        <x-ts:button sm color="indigo" class="font-bold" icon="tabler.printer" loading="printManual" wire:click="printManual()">Print & Simpan</x-ts:button>
    </div>
</div>
