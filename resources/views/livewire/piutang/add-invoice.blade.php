
<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-2" x-data="listDetailInvoice">
        <div class="flex flex-col gap-4 lg:flex-row">
            <div class="flex w-full flex-col lg:w-1/4">
                <span class="text-xs italic text-indigo-500">Kepada</span>
                <div class="flex w-full flex-col gap-2">
                    <x-ts:input placeholder="Vendor / Rekanan" />
                    <x-ts:input placeholder="Mengetahui" />
                    <x-ts:textarea placeholder="Berita / Keterangan" />
                    <x-ts:textarea wire:model.defer='foot_note' placeholder="Catatan Kaki" />
                </div>
            </div>
            <div class="flex w-full flex-col gap-2 lg:w-3/4">
                <span class="text-xs italic text-indigo-500">Detail Tagihan</span>
                <div class="flex flex-col gap-2 rounded-lg border border-gray-100 bg-gray-50 px-4 py-2">
                    <template x-for="(item, index) in itemInvoice" :key="index">
                        <div class="flex w-full flex-row items-center gap-2">
                            <div class="w-1/4">
                                <x-ts:input type="text" x-model="item.nominal" @input="recalculateTotal" required placeholder="Nominal" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                            </div>
                            <div class="w-3/4">
                                <x-ts:input x-model="item.keterangan" placeholder="Keterangan Pembayaran" required />
                            </div>

                            {{-- remove item --}}
                            <x-tabler-trash x-on:click="removeItem(index)" sm class="text-red-400" role="button" />
                        </div>
                    </template>
                    <span role="button" class="flex flex-row text-sm italic text-indigo-500" x-on:click="addingItem">
                        + Tambah Rincian
                    </span>
                </div>

                <div class="flex flex-col gap-3 rounded-md border border-gray-100 bg-gray-50 px-4 py-2">
                    <div class="flex w-full flex-row items-center gap-2 lg:w-1/4">
                        <x-ts:input wire:model.defer='bank' type="number" placeholder="Diskon" />
                        @php
                            $diskonPersen = rand(1, 100);
                            $colors = [['min' => 0, 'max' => '25', 'color' => 'secondary'], ['min' => 26, 'max' => '50', 'color' => 'orange'], ['min' => 51, 'max' => '100', 'color' => 'red']];
                            $badgeColor = 'secondary';
                            foreach ($colors as $range) {
                                if ($diskonPersen >= $range['min'] && $diskonPersen <= $range['max']) {
                                    $badgeColor = $range['color'];
                                    break;
                                }
                            }
                        @endphp
                        <x-ts:badge outline color="{{ $badgeColor }}">{{ $diskonPersen }}%</x-ts:badge>
                    </div>
                    <div class="flex w-full items-center lg:w-1/4">
                        <x-ts:checkbox label="PPN 11%" />
                    </div>
                </div>
            </div>

        </div>
        <div class="grid grid-cols-2 rounded-md bg-gray-100 px-4 py-2">
            <div class="flex flex-col">
                <span class="text-xs italic text-gray-500">Rekening</span>
                <div class="flex flex-row gap-4 p-2">
                    @foreach ($bankOptions as $item)
                        <x-ts:radio wire:model.defer='bank' id="{{ $item['value'] }}" value="{{ $item['value'] }}" label="{{ $item['label'] }}" />
                    @endforeach
                </div>
                <div class="flex flex-col rounded-md border border-gray-200 p-2 text-sm text-gray-500">
                    <span>No. Rek :</span>
                    <span>A/N :</span>
                </div>
            </div>
            <div class="flex flex-col justify-end text-right">
                <span class="text-xs italic text-gray-500">Total Pembayaran</span>
                <span class="font-bold text-indigo-500" x-text="`Sub Total : Rp. ${totalPembayaran.toLocaleString()}`"></span>
                <span class="font-bold text-indigo-500">Diskon : </span>
                <span class="font-bold text-indigo-500">PPN : </span>
                <span class="font-bold text-indigo-500">Total Keseluruhan : </span>

            </div>
        </div>

        <div class="flex justify-end">

            <x-ts:button sm type="submit" icon="tabler.checks" loading="submit">Simpan</x-ts:button>
        </div>

    </form>
</div>

@script
    <script>
        Alpine.data('listDetailInvoice', () => {
            return {
                totalPembayaran: 0,
                itemInvoice: $wire.entangle('listDetailInvoice'),

                addingItem() {
                    const newItem = {
                        nominal: 0,
                        keterangan: ''
                    };
                    this.itemInvoice.push(newItem);
                },

                removeItem(index) {
                    this.itemInvoice.splice(index, 1);
                },

                recalculateTotal() {
                    this.totalPembayaran = this.itemInvoice.reduce((sum, item) => {
                        let nom = String(item.nominal || '').replace(/\D/g, '');
                        return sum + (Number(nom) || 0);
                    }, 0);
                },
            }
        })
    </script>
@endscript
