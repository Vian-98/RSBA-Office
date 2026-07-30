<div class="w-full">
    <x-ts:tab selected="Pembelian" x-on:navigate="$wire.set('tab', $event.detail.select)">
        {{-- Tab Pembelian --}}
        <x-ts:tab.items tab="Pembelian">
            <x-slot:left>
                <x-ts:icon name="tabler.shopping-cart-plus" class="h-5 w-5" />
            </x-slot:left>

            @island
                <div class="flex flex-col gap-2">
                    {{-- Reusable filter form for Pembelian --}}
                    <livewire:laporan.umum.filter-form type="pembelian" wire:key="filter-pembelian" />

                    {{-- Results --}}
                    <div class="rounded-lg bg-white px-4 py-2">
                        <livewire:Laporan.Umum.Pembelian wire:key="laporan-pembelian" />
                    </div>
                </div>
            @endisland
        </x-ts:tab.items>

        {{-- Tab Distribusi --}}
        <x-ts:tab.items tab="Distribusi">
            <x-slot:left>
                <x-ts:icon name="tabler.shopping-cart-share" class="h-5 w-5" />
            </x-slot:left>

            @island
                <div class="flex flex-col gap-2">
                    {{-- Reusable filter form for Distribusi --}}
                    <livewire:laporan.umum.filter-form type="distribusi" wire:key="filter-distribusi" />

                    {{-- Results --}}
                    <div class="rounded-lg bg-white px-4 py-2">
                        <livewire:Laporan.Umum.Distribusi wire:key="laporan-distribusi" />
                    </div>
                </div>
            @endisland
        </x-ts:tab.items>
    </x-ts:tab>
</div>
