<div>
    <h2 class="text-lg font-semibold text-indigo-500">Rawat Inap</h2>
    <label class="text-xs">Import kelompok jasa rawat inap.
        <span role="button" class="text-blue-500" wire:click='downloadTemplate("ranap")'>Template</span>
        <x-spinner xs target='downloadTemplate("ranap")' />
    </label>
    <div class="w-3/4">
        <x-ts:upload wire:model='excelImportKelompokRanap'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importKelompokRanap" loading="importKelompokRanap" icon="tabler.database-import" class="w-full">Upload</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>

    <form wire:submit='sumbitProcessRanap' class="mt-4 flex flex-row gap-4">
        <x-ts:input type="month" wire:model='bulan_ri' />
        <x-ts:select.styled wire:model='batch_ri' :options="[1, 2, 3]" placeholder="Batch" />

        <x-ts:button type='submit' loading='sumbitProcessRanap' class="btn btn-primary">
            <x-tabler-plus-minus class="size-5" /> Proses Hitung
        </x-ts:button>
    </form>

    <form wire:submit.prevent='downloadRanap' class="mt-2 flex w-full flex-col gap-1 md:flex-row">
        @php
            $optionsRanap = [
                ['label' => 'Non Operatif', 'value' => 'ri_no'],
                ['label' => 'Operatif', 'value' => 'ri_op'],
                ['label' => 'Mata', 'value' => 'ri_mata'],
                ['label' => 'Partus', 'value' => 'ri_partus'],
                ['label' => 'SC', 'value' => 'ri_sc'],
                ['label' => 'Curet', 'value' => 'ri_curet'],
                ['label' => 'HD', 'value' => 'ri_hd'],
            ];
        @endphp

        <x-ts:select.styled wire:model.live.change='pilih_download_ranap' :options="$optionsRanap" select="label:label|value:value" />
        <x-ts:button type='submit' loading='downloadRanap'>
            <x-tabler-file-download class='size-5' />
            Download
        </x-ts:button>
    </form>

    <span wire:loading wire:target='downloadRanap'>
        <span class="loading loading-spinner loading-xs text-success"> </span>
        Dowloading {{ $pilih_download_ranap }}...
    </span>
</div>
