<div class="flex flex-col gap-2">
    <div class="flex flex-row items-center gap-2 font-semibold text-indigo-500">
        <x-ts:icon name="tabler.file-check" class="w-5" />
        <span class="text-lg">Verifikasi Surat</span>
    </div>


    <div class="flex flex-row gap-6 rounded-md bg-indigo-50 p-2">
        <x-ts:radio sm wire:model.live.debounce='tab' id="sp3" value="sp3" label="SP3" />
        <x-ts:radio sm wire:model.live.debounce='tab' id="cuti" value="cuti" label="Cuti" />
        <x-ts:radio sm wire:model.live.debounce='tab' id="sppd" value="sppd" label="SPPD" />
    </div>

    <div class="mt-5 w-full rounded-md bg-gray-100 p-2">
        @if ($tab === 'sp3')
            <livewire:Surat.Sp3.Verify />
        @elseif($tab === 'cuti')
            <livewire:Surat.Cuti.Verify />
        @else
            <span>Click Options</span>
        @endif

    </div>

</div>
