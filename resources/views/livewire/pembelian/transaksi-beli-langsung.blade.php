<div>
    {{-- form --}}
    <form wire:submit.prevent='submit' class="flex flex-col gap-6" autocomplete="off">
        {{-- input --}}
        <div class="flex w-full flex-col gap-2 rounded-lg border-gray-200 bg-gray-50 p-2 shadow">

            <div class="flex w-full flex-col gap-2 lg:flex-row">
                <div class="w-full lg:w-1/3">
                    <x-ts:select.styled wire:model.defer='supplier' placeholder="Supplier" :request="route('api.supplier')" select="label:nama|value:id">

                        {{-- button new supplier --}}
                        <x-slot:after>
                            <div class="mb-2 flex items-center justify-center px-2">
                                <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-supplier'}); $wire.set('createTerm',search)">
                                    <span x-html="`Create <b>${search}</b>`"></span>
                                </x-ts:button>
                            </div>
                        </x-slot:after>
                    </x-ts:select.styled>
                </div>

                <div class="flex w-full flex-col gap-2 lg:grid lg:w-3/4 lg:grid-cols-4">
                    <x-ts:date placeholder="Tgl. Pembelian" wire:model.defer='tgl_pembelian' />

                    <x-ts:input placeholder="No Faktur / Nota" wire:model.defer='no_faktur' />

                    <x-ts:select.styled placeholder="Pembayaran" :options="$cabarOptions" select="label:nama|value:value" wire:model.defer='status_pembayaran' />

                    <x-ts:date placeholder="Tgl Pembayaran" wire:model.defer='tgl_pembayaran' />
                </div>
            </div>
            <div class="flex w-full flex-col gap-2 lg:grid lg:grid-cols-4">
                <x-ts:input wire:model.defer='keterangan' placeholder="Keterangan" />

                <x-ts:upload wire:model='lampirans' delete multiple placeholder="Lampiran" accept="image/png,image/jpeg,application/pdf" />

            </div>
        </div>

        {{-- list barang --}}
        {{-- listPembelian : Alpine on tags script --}}
        <div x-data="listPembelian" class="flex flex-col gap-2">

            {{-- manage cart --}}
            <div class="relative rounded-lg border border-indigo-200">
                <span class="absolute -left-0 -top-3 rounded-full border border-indigo-200 bg-indigo-50 px-2 text-xs font-thin italic text-indigo-500">
                    List Barang Yang Dibeli
                </span>

                <div class="my-2 flex flex-col gap-1 p-2">

                    {{-- cari barang --}}
                    <div x-data="{ useSelect: false }" x-init="$nextTick(() => $refs.barcodeSearch.focus())" class="ssm:w-3/4 flex w-full flex-row items-center gap-2 lg:w-1/3">
                        <div class="w-full">
                            <template x-if="useSelect">
                                <x-ts:select.styled x-model.debounce.300ms='searchItem' :request="route('api.barang.ref')" select="label:nama|value:id" placeholder="Pilih barang"
                                    x-on:select="addingCart($event.detail.select.id)">
                                    {{-- $wire.addingCart($event.detail.select.id) --}}

                                    {{-- button adding new --}}
                                    <x-slot:after>
                                        <div class="mb-2 flex items-center justify-center px-2">
                                            <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-barang'}); $wire.set('createTerm',search)">
                                                <span x-html="`Create <b>${search}</b>`"></span>
                                            </x-ts:button>
                                        </div>
                                    </x-slot:after>
                                </x-ts:select.styled>
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

                    <div class="text-sm text-red-500">
                        @error('cartItems')
                            <span>{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex overflow-x-auto overflow-y-visible">
                        <table class="min-w-full table-fixed border-collapse">
                            <thead>
                                <tr class="border-b bg-gray-100 text-left text-xs text-gray-600">
                                    <th class="p-2">No.</th>
                                    <th class="p-2">Tipe</th>
                                    <th class="p-2">SKU</th>
                                    <th class="p-2">Barang</th>
                                    <th class="p-2">Satuan</th>
                                    <th class="p-2">Jumlah Beli</th>
                                    <th class="p-2">Harga Satuan</th>
                                    <th class="p-2">Diskon</th>
                                    <th class="p-2">PPN</th>
                                    <th class="p-2">Sub Total</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <template x-for="(item, index) in cartItems" :key="index">
                                    <tr class="border-b border-dashed text-sm even:bg-gray-100/75 hover:bg-indigo-100">
                                        <td class="p-1" x-text="index + 1"></td>
                                        <td class="p-1">
                                            <span x-text="item.bhp ? 'BHP' : 'Barang'"></span>
                                        </td>
                                        <td class="p-1" x-text="item.sku"></td>
                                        <td class="p-1" x-text="item.nama"></td>
                                        <td class="p-1" x-text="item.satuan"></td>
                                        <td class="p-1">
                                            <input type="number" min="1" x-model.number="item.jumlah" @keyup="updateItem(index)" class="h-8 max-w-24 rounded-lg border border-gray-100 text-sm"
                                                placeholder="Qty" />
                                        </td>
                                        <td class="p-1">
                                            <input type="number" step="any" min="0" x-model.number="item.harga" @keyup="updateItem(index)"
                                                class="h-8 max-w-32 rounded-lg border border-gray-100 text-sm" placeholder="Harga Satuan" />
                                        </td>
                                        <td class="p-1">
                                            <input type="number" step="any" min="0" x-model.number="item.diskon" @keyup="updateItem(index)"
                                                class="h-8 max-w-32 rounded-lg border border-gray-100 text-sm" placeholder="Diskon" />
                                        </td>
                                        <td class="p-1">
                                            <input type="number" step="any" min="0" x-model.number="item.ppn" @keyup="updateItem(index)"
                                                class="h-8 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="%" />
                                        </td>
                                        <td class="p-1" x-text="item.subTotal.toLocaleString()"></td>

                                        <td class="flex flex-row items-center justify-end gap-4 p-1" x-data="{ open: false, style: '' }">

                                            {{-- tombol action garansi dan batch --}}
                                            <x-tabler-label title="Batch / Serial" class="h-5 w-auto cursor-pointer text-indigo-500" role="button"
                                                x-on:click="let r = $el.getBoundingClientRect(); style = 'top:'+(r.bottom+6)+'px; left:'+(r.left-180)+'px'; open = !open;" />

                                            {{-- tombol action hapus cart --}}
                                            <x-tabler-trash title="Hapus" class="h-5 w-auto cursor-pointer text-red-500" role="button" @click="removeItemFromCart(index)" />

                                            <!-- Tooltip Batch dan Garansi -->
                                            <div x-show="open" x-transition.scale.origin.top @click.outside="open = false" class="fixed right-0 z-50 w-56 rounded-lg border bg-white p-3 shadow-lg"
                                                :style="style">

                                                <!-- arrow -->
                                                <div class="absolute -top-1 right-4 h-2 w-2 rotate-45 border-l border-t bg-white"></div>

                                                <div class="space-y-2">
                                                    <div>
                                                        <label class="text-[11px] text-gray-500">
                                                            Batch / Serial
                                                        </label>
                                                        <input type="text" x-model="item.batch" class="h-7 w-full rounded-lg border border-gray-100 text-xs"
                                                            x-bind:placeholder="item.bhp ? 'Batch' : 'Serial Numbers'" />
                                                    </div>

                                                    <div>
                                                        <label class="text-[11px] text-gray-500" x-text="item.bhp ? 'Exp Date' : 'Warranty'">
                                                        </label>
                                                        <input type="date" x-model="item.waranty_date" class="h-7 w-full rounded-lg border border-gray-100 text-xs"
                                                            x-bind:placeholder="item.bhp ? 'Exp Date' : 'Waranty Date'" />
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="cartItems.length === 0">
                                    <td colspan="9" class="p-2 text-center text-sm italic text-gray-400">
                                        Belum ada list pembelian barang.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- summary cart --}}
            <div class="grid w-full grid-cols-2 rounded-md border border-indigo-200 bg-indigo-100/75 px-4 py-2 lg:grid-cols-2">
                <div class="flex flex-col">
                    <span class="text-xs font-thin italic text-gray-500">Item:</span>
                    <span class="text-xl font-semibold text-indigo-500" x-text="totalItem"></span>
                </div>

                <div class="col-span-2 flex w-full flex-col lg:col-span-1">
                    <div class="flex flex-col justify-end text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-800" x-text="`Rp ${totalBeli.toLocaleString()}`"></span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Diskon</span>
                            <span class="text-red-500" x-text="`(${itemDiskon})` +  ` Rp ${totalDiskon.toLocaleString()}`"></span>
                        </div>

                        <div class="flex justify-between border-t-2 border-dashed border-gray-200 font-semibold">
                            <span class="text-gray-600">Subtotal setelah diskon</span>
                            <span class="text-gray-800" x-text="`Rp ${(totalBeli - totalDiskon).toLocaleString()}`"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">PPN</span>
                            <span class="text-gray-800" x-text="`(${itemPpn}) Rp ${totalPpn.toLocaleString()}`"></span>
                        </div>
                        <div class="flex justify-between border-t-2 border-dashed border-indigo-200 pt-1">
                            <span class="text-base font-bold text-indigo-700">Total Pembayaran</span>
                            <span class="text-xl font-bold text-indigo-600" x-text="`Rp ${((totalBeli - totalDiskon) + totalPpn).toLocaleString()}`"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- actions form --}}
        {{-- TODO: Confirm on cancel form --}}
        <div class="ml-auto flex flex-row justify-end gap-2">

            {{-- cancel confirm popup --}}
            <div x-data="{ popUpCancelConfirm: false }" class="relative">
                <x-ts:button sm outline color="neutral" x-on:click="popUpCancelConfirm = true">Tutup</x-ts:button>

                <div x-show="popUpCancelConfirm" x-transition x-trap.noscroll="popUpCancelConfirm" x-on:click.away="popUpCancelConfirm = false"
                    x-on:keydown.escape.window="popUpCancelConfirm = false" class="absolute right-0 z-50 mt-2 max-w-fit rounded-lg bg-white p-4 shadow-lg">

                    <!-- Tooltip Header -->
                    <div class="mb-3 flex items-center justify-between">
                        <span class="flex flex-row items-center gap-2 whitespace-nowrap font-medium text-indigo-500">
                            <x-ts:icon name="tabler.alert-circle" class="h-5 w-5" />
                            Pembelian Dibatalkan ?
                        </span>
                    </div>

                    <!-- Options -->
                    <div class="flex items-center justify-between gap-4">
                        <span class="flex flex-row items-center gap-2 whitespace-nowrap text-sm font-light text-gray-500">
                            Data pada form pembelian akan hilang.
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex justify-end gap-2">
                        <x-ts:button outline sm color="red" x-on:click="popUpCancelConfirm = false">
                            Tidak
                        </x-ts:button>
                        <x-ts:button outline sm color="green" x-on:click="$dispatch('close-modal',{id:'modal-new-pembelian-langsung'})">
                            Ya, Batalkan
                        </x-ts:button>
                    </div>
                </div>

            </div>
            <x-ts:button sm type="submit" loading="submit" wire:loading.attr="disabled" wire:target="submit" icon="tabler.checks">Simpan</x-ts:button>

        </div>
    </form>


    {{-- modal add barang --}}
    <x-filament::modal id="modal-new-barang" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Tambah Barang</x-slot:heading>

        <livewire:Master.Barang.Add :term="$createTerm" :key="Str::random()" @new-barang-created="$refresh" />
    </x-filament::modal>


    {{-- modal add supplier --}}
    <x-filament::modal id="modal-new-supplier" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Tambah Supplier</x-slot:heading>


        <livewire:Master.Supplier.Add :term="$createTerm" :key="Str::random()" />
    </x-filament::modal>
</div>

@script
    <script>
        Alpine.data('listPembelian', () => {
            return {
                sku: '',
                searchItem: '',
                totalItem: 0,
                totalBeli: 0, //sum cartItems.subTotal
                totalDiskon: 0,
                itemDiskon: 0,
                totalPpn: 0,
                itemPpn: 0,
                cartItems: $wire.entangle('cartItems'),


                addingCart(id) {
                    try {
                        $wire.getBarang(id).then(barang => {
                            const existingItem = this.cartItems.find(item => item.id === barang.id);

                            if (existingItem) {
                                existingItem.jumlah += 1;
                            } else {
                                const newItem = {
                                    id: barang.id,
                                    bhp: barang.bhp,
                                    sku: barang.sku,
                                    nama: barang.nama,
                                    satuan: barang.satuan,
                                    jumlah: 1,
                                    harga: 0,
                                    diskon: 0,
                                    ppn: 0,
                                    ppnAmount: 0,
                                    batch: null,
                                    waranty_date: null,
                                    subTotal: 0
                                };
                                this.cartItems.push(newItem);
                                this.totalItem = this.cartItems.length;
                                this.sku = '';
                                this.searchItem = '';
                            }

                        }).catch(error => {
                            console.error('Error retrieving barang data:', error);
                        });
                    } catch (error) {
                        console.error('Error in addingCart:', error);
                    }

                },

                updateItem(index) {
                    const item = this.cartItems[index];
                    if (item) {
                        item.subTotal = (item.jumlah * item.harga);
                        item.ppnAmount = ((item.subTotal - item.diskon) * (item.ppn / 100))

                        this.recalculateTotal();
                    }
                },

                recalculateTotal() {
                    this.totalItem = this.cartItems.length;

                    this.totalBeli = this.cartItems.reduce((total, item) => {
                        return total + (item.subTotal || (item.jumlah * item.harga));
                    }, 0);

                    this.totalDiskon = this.cartItems.reduce((total, item) => {
                        return total + (item.diskon);
                    }, 0);


                    this.totalPpn = this.cartItems.reduce((total, item) => {
                        return total + ((item.ppn / 100) * (item.subTotal - item.diskon));
                    }, 0);

                    // item yang diskon
                    this.itemDiskon = this.cartItems.filter(item => Number(item.diskon) > 0).length;

                    // item yang ppn
                    this.itemPpn = this.cartItems.filter(item => Number(item.ppn) > 0).length;

                    const nettBayar = this.totalBeli - this.totalDiskon;

                    // this.totalPpn = (this.ppn / 100) * nettBayar;
                },

                removeItemFromCart(index) {
                    if (index >= 0 && index < this.cartItems.length) {
                        this.cartItems.splice(index, 1);
                        this.recalculateTotal(); // Recalculate setelah hapus
                    }
                },

                formatRupiah() {
                    let value = this.displayValue;
                    value = value.replace(/\D/g, '');
                    this.numerivalue = parseInt(value) || 0;

                    if (this.numerivalue === 0) {
                        this.displayValue = '';
                    } else {
                        this.displayValue = 'Rp ' + this.numericValue.toLocaleString('id-ID');
                    }
                }
            }
        })
    </script>
@endscript
