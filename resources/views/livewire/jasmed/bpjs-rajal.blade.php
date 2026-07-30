<div>
    <h2 class="text-lg font-semibold text-indigo-500">Rawat Jalan</h2>

    {{-- impoort rajal --}}
    <label class="text-xs">Import kelompok jasa rawat jalan.
        <span role="button" class="text-blue-500" wire:click='downloadTemplate("rajal")'>Template</span>
        <x-spinner xs target="downloadTemplate('rajal')" />
    </label>
    <div class="w-3/4">
        <x-ts:upload wire:model='excelImportRajal'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importRajal" icon="tabler.database-import" loading="importRajal" class="w-full">Upload</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>


    {{-- proses calculasi rawat jalan --}}
    <form wire:submit.prevent='submitProsesRajal' class="mt-4 flex flex-row gap-2">
        <x-ts:input type="month" wire:model='bulan_rj' />
        <x-ts:select.styled wire:model='batch_rj' :options="[1, 2, 3]" placeholder="Batch" />

        <x-ts:button type='submit' loading='submitProsesRajal'>
            <x-tabler-plus-minus class="size-5" /> Proses Hitung
        </x-ts:button>
    </form>
    <form wire:submit.prevent='downloadRajal' class="mt-2 flex w-full flex-col gap-1 md:flex-row">
        <label class="form-control w-full md:w-1/3">
            @php
                $optionsRj = [['label' => 'Spesialis', 'value' => 'rj_sp'], ['label' => 'Umum', 'value' => 'rj_um'], ['label' => 'Mata', 'value' => 'rj_mata'], ['label' => 'HD', 'value' => 'rj_hd']];
            @endphp
            <x-ts:select.styled wire:model.live.change='pilih_download_rajal' :options="$optionsRj" select="label:label|value:value" />
        </label>

        <x-ts:button type='submit' loading='downloadRajal'>
            <x-tabler-file-download class='size-5' />
            Download
        </x-ts:button>
    </form>
    <span wire:loading wire:target='downloadRajal'>
        <x-spinner target="downloadRajal" sm />
        Dowloading {{ $pilih_download_rajal }}...
    </span>
</div>
