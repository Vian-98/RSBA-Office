<div>
    <form wire:submit.prevent="submit">
        <div class="flex flex-col gap-3 lg:flex-row">

            <div class="flex w-full flex-col gap-3 rounded-lg bg-white p-4 lg:w-1/4">

                <h1 class="flex flex-row items-center gap-2 font-semibold text-indigo-500">
                    <x-ts:icon name="tabler.map-pin" class="h-5 w-5" />
                    Tujuan
                </h1>

                <x-ts:date wire:model.defer='form.tgl_distribusi' placeholder="Tgl Distribusi" />

                <x-ts:select.styled wire:model.defer='form.ruangan' searchable :request="route('api.ruangan')" select="label:nama|value:id" placeholder="Ruangan" />

                <x-ts:select.styled wire:model.defer='form.penerima' searchable :request="route('api.karyawan.ref')" select="label:nama|value:id" placeholder="Penerima" />

                <label class="text-sm text-gray-500">Catat Sebagai :</label>
                <div class="flex flex-row gap-2">
                    <x-ts:radio wire:model='form.sebagai' id="keluar" value="keluar" label="Pengeluaran" />
                    <x-ts:radio wire:model='form.sebagai' id="asset" value="asset" label="Asset" />
                </div>

                <x-ts:textarea wire:model.defer='form.keterangan' placeholder="Keterangan" />

            </div>

            <div x-data="distribusiCart" class="flex w-full flex-col gap-3 lg:w-3/4">

                <div class="flex flex-col gap-3 rounded-lg bg-white p-4">
                    <h1 class="flex flex-row items-center gap-2 font-semibold text-indigo-500">
                        <x-ts:icon name="tabler.box" class="h-5 w-5" />
                        Barang
                    </h1>
                    <div class="w-full lg:w-1/2">

                        {{-- cari barang --}}
                        <div x-data="{ useSelect: false }" x-init="$nextTick(() => $refs.barcodeSearch.focus())" class="flex flex-row items-center gap-2">
                            <div class="w-full">
                                <template x-if="useSelect">
                                    {{-- select --}}
                                    <x-ts:select.styled x-model.debounce.300ms='searchItem' :request="route('api.barang.ref')" select="label:nama|value:id" placeholder="Pilih barang"
                                        x-on:select="addingCart($event.detail.select.id)">
                                    </x-ts:select.styled>
                                    {{-- end select --}}
                                </template>
                                <template x-if="!useSelect">
                                    <x-ts:input x-ref="barcodeSearch" x-model="sku" @input.debounce="addingCart(sku)" placeholder="Scan barang" icon="tabler.barcode" />
                                </template>
                            </div>

                            <span x-on:click="useSelect = !useSelect" role="button">
                                <x-tabler-barcode class="h-8 w-8 text-indigo-500" title="Switch using barcode." x-show="useSelect" />
                                <x-tabler-direction class="h-8 w-8 text-indigo-500" title="Switch using select." x-show="!useSelect" />
                            </span>

                        </div>

                    </div>
                    <div class="text-sm text-red-500">
                        @error('form.cartItems')
                            <span>{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="max-h-96 overflow-x-auto overflow-y-auto">
                        <table class="min-w-full table-fixed border-collapse">
                            <thead>
                                <tr class="border-b text-left text-sm text-gray-600">
                                    <th class="px-4 py-2">No.</th>
                                    <th class="px-4 py-2">SKU</th>
                                    <th class="px-4 py-2">Barang</th>
                                    <th class="px-4 py-2">Satuan</th>
                                    <th class="px-4 py-2">Stok Tersedia </th>
                                    <th class="px-4 py-2">Jumlah</th>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in cartItems" :key="index">
                                    <tr :class="{ 'bg-red-100/50': item.stok == 0, 'even:bg-gray-200/25': item.stok != 0 }" class="border-b text-left text-sm text-gray-600">
                                        <td class="px-4 py-2" x-text="index+1"></td>
                                        <td class="px-4 py-2" x-text="item.sku"></td>
                                        <td class="px-4 py-2" x-text="item.barang"></td>
                                        <td class="px-4 py-2" x-text="item.satuan"></td>
                                        <td class="flex items-center gap-2 px-4 py-2">
                                            <span x-text="item.stok"></span>
                                            <span class="text-xs italic text-red-500" x-show="item.stok == 0">
                                                Habis
                                            </span>
                                            <span class="text-xs italic" x-show="item.stok == null">Belum pernah melakukan pebelian</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" :disabled="item.stok == 0 || item.stok == null" x-model.number="item.jumlah" class="h-8 max-w-32 rounded-lg border border-gray-100"
                                                placeholder="Jumlah" />
                                        </td>
                                        <td class="flex items-center justify-end px-4 py-2">
                                            <x-tabler-trash class="text-red-500" role="button" @click="removeItemFromCart(index)" />
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="cartItems.length === 0" class="border-b text-left text-sm text-gray-600 even:bg-gray-200/25">
                                    <td colspan="7" class="px-4 py-2 text-center italic">
                                        Belum ada data.
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer action --}}
                <div class="ml-auto flex w-full justify-end rounded-lg bg-white p-4">

                    <x-button-confirm-popup title="Yakin Checkout ?" confirm="Ya, Lanjutkan" cancel="Batal" description="Data yang sudah di checkout tidak bisa diubah lagi.">

                        <x-slot:trigger>
                            <x-ts:button type="submit" loading="submit" icon="tabler.shopping-cart">
                                Checkout
                            </x-ts:button>
                        </x-slot:trigger>

                    </x-button-confirm-popup>

                </div>

            </div>

        </div>

    </form>

</div>

@script
    <script>
        Alpine.data('distribusiCart', () => {
            return {
                sku: '',
                searchItem: '',
                cartItems: $wire.entangle('form.cartItems', true),

                addingCart(id) {
                    try {
                        $wire.getBarang(id).then(barang => {
                            const existingItem = this.cartItems.find(item => item.id === barang.id);
                            if (existingItem) {
                                existingItem.jumlah += 1;
                            } else {
                                const newItem = {
                                    id: barang.id,
                                    sku: barang.sku,
                                    barang: barang.nama,
                                    satuan: barang.satuan,
                                    stok: barang.stok,
                                    jumlah: (barang.stok == 0 || barang.stok == null) ? 0 : 1
                                };
                                this.cartItems.push(newItem);

                            }

                        }).catch(error => {
                            $interaction('toast')
                                .error('Tidak Ditemukan', 'Kode / Nama barang tidak sesuai.')
                                .send();
                        });

                    } catch (error) {
                        $interaction('toast')
                            .error('Error Menambah Item', error)
                            .send();
                    }
                },

                removeItemFromCart(index) {
                    this.cartItems.splice(index, 1);
                }

            }
        })
    </script>
@endscript
