<div>
    <x-ts:step selected="1" helpers navigate-previous>
        <x-ts:step.items step="1">
            <livewire:Pembelian.Detail :id="$pembelian->id" :key="'detail-' . $pembelian->id" />
        </x-ts:step.items>

        <form autocomplete="off">
            <x-ts:step.items step="2">
                @if ($status)
                    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-2">
                        <div class="w-full">
                            <span class="text-sm italic text-gray-500">Riwayat Pembayaran</span>
                            <livewire:Hutang.Bayar.ListHutangBayar :id="$pembelian->id" :key="'list-pembayaran-' . $pembelian->id" />
                        </div>
                        <div>
                            <span class="text-sm italic text-gray-500">Pembayaran</span>
                            <div class="flex w-full flex-col gap-2 p-2 lg:grid lg:grid-cols-2">
                                <x-ts:date wire:model.defer='tanggal' placeholder="Tanggal Bayar" />
                                <x-ts:input type="number" wire:model.defer='nominal' placeholder="Nominal Bayar" />
                                <x-ts:upload wire:model='lampiran' close-after-upload placeholder="Lampiran"></x-ts:upload>
                            </div>
                        </div>
                    </div>
                @else
                    <span>Pembelian belum diselesaikan.</span>
                @endif
            </x-ts:step.items>
            <x-slot:finish>
                <x-ts:button type="submit" wire:click='submit' loading="submit">Submit</x-ts:button>
            </x-slot:finish>
        </form>
    </x-ts:step>
</div>
