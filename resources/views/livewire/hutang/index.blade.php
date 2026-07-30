<div class="flex flex-col gap-2">
    <div class="flex w-full flex-col items-center justify-between gap-3 rounded-lg bg-white px-4 py-2 sm:grid sm:grid-cols-2 lg:grid-cols-6">
        <x-ts:date wire:model.live.debounce.500='periode' month-year-only placeholder="Periode Pembelian" />
        <x-ts:select.styled wire:model.live.debounce.300='vendor' :request="route('api.supplier')" select="label:nama|value:id" placeholder="Vendor / Supplier" />
        {{-- <x-ts:select.styled :options="$optionsFaktur" select="label:label|value:value" placeholder="Jenis Pembelian" /> --}}
        <x-ts:date wire:model.live.debounce.500='due_date' placeholder="Jatuh Tempo" />
    </div>

    <div class="w-full rounded-lg border border-white">
        <livewire:Hutang.Stats :periode="$periode" :vendor="$vendor" :key="'stats-hutang-' . md5($periode . $vendor . $jenis . $due_date)" />
    </div>

    {{-- table list hutang --}}
    <div class="w-full rounded-lg bg-white px-4 py-2">
        <livewire:Hutang.ListHutang :periode="$periode" :supplier="$vendor" :key="'list-hutang-' . md5($periode . $vendor . $jenis . $due_date)" />
    </div>
</div>
