<form wire:submit.prevent="cari" class="grid grid-cols-1 gap-3 rounded-lg bg-white px-4 py-2 lg:grid-cols-12">
    {{-- Periode --}}
    <div class="{{ $type === 'pembelian' ? 'lg:col-span-4' : 'lg:col-span-6' }}">
        <x-ts:date range wire:model.lazy="periode" helpers placeholder="Periode {{ ucfirst($type) }}" />
        @error('periode')
            <span class="text-xs text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Barang --}}
    <div class="lg:col-span-3" wire:key='select-items'>
        <x-ts:select.styled wire:model.lazy="items" multiple :request="route('api.barang.ref')" select="label:nama|value:id" placeholder="Pilih barang">
            <x-slot:after>
                <div class="mb-2 flex justify-center px-2">
                    <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-barang'}); $wire.set('createTerm', search)">
                        <span x-html="`Create <b>${search}</b>`"></span>
                    </x-ts:button>
                </div>
            </x-slot:after>
        </x-ts:select.styled>
    </div>

    {{-- Type-specific fields --}}
    @if ($type === 'pembelian')
        <div class="lg:col-span-2" wire:key='select-vendor'>
            <x-ts:select.styled wire:model.lazy="vendor" :request="route('api.supplier')" select="label:nama|value:id" placeholder="Vendor / Supplier" />
        </div>

        <div class="lg:col-span-2" wire:key='select-jenis'>
            <x-ts:select.styled wire:model.lazy="jenis" :options="$optionsFaktur" select="label:label|value:value" placeholder="Jenis Pembelian" />
        </div>
    @else
        <div class="lg:col-span-2" wire:key='select-ruangan'>
            <x-ts:select.styled wire:model.lazy="ruangan" :request="route('api.ruangan')" select="label:nama|value:id" placeholder="Ruangan" />
        </div>
    @endif

    {{-- Submit --}}
    <div class="lg:col-span-1">
        <x-ts:button sm outline type="submit" loading="cari" icon="tabler.zoom" position="left" class="w-full lg:w-auto" loading="cari">
            Cari
        </x-ts:button>
    </div>
</form>
