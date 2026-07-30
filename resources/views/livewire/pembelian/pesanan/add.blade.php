<div>
    {{-- form --}}
    <form wire:submit.prevent='submit' class="flex flex-col gap-6">
        {{-- input --}}
        <div class="flex w-full flex-row gap-2 rounded-lg border-gray-200 bg-gray-50 p-2 text-center shadow">

            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model='supplier' placeholder="Supplier" :request="route('api.supplier')" select="label:nama|value:id">

                    {{-- button add --}}
                    <x-slot:after>
                        <div class="mb-2 flex items-center justify-center px-2">
                            <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-supplier'}); $wire.set('createTerm',search)">
                                <span x-html="`Create <b>${search}</b>`"></span>
                            </x-ts:button>
                        </div>
                    </x-slot:after>
                </x-ts:select.styled>
            </div>

            <x-ts:date placeholder="Tanggal PO" wire:model.defer='tgl_pembelian' />
        </div>

        {{-- list barang --}}
        <div x-data="listPembelian" class="flex flex-col gap-2">

            {{-- cart --}}
            <div class="relative rounded-lg border border-indigo-200">
                <span class="absolute -left-0 -top-3 rounded-full border border-indigo-200 bg-indigo-50 px-2 text-xs font-thin italic text-indigo-500">
                    List Barang Dipesan
                </span>

                <div class="my-2 flex flex-col gap-1 p-4">

                    {{-- cari barang --}}
                    <div x-data="{ useSelect: false }" x-init="$nextTick(() => $refs.barcodeSearch.focus())" class="flex w-1/2 flex-row items-center gap-2 lg:w-1/3">

                        <div class="w-full">
                            {{-- option barang --}}
                            <template x-if="useSelect">
                                <x-ts:select.styled x-model='searchItem' searchable :request="route('api.barang.ref')" select="label:nama|value:id" placeholder="Cari barang"
                                    x-on:select="addingCart($event.detail.select.id)">

                                    {{-- button add --}}
                                    <x-slot:after>
                                        <div class="mb-2 flex items-center justify-center px-2">
                                            <x-ts:button sm x-on:click="show = false; $dispatch('open-modal',{id:'modal-new-barang'}); $wire.set('createTerm',search)">
                                                <span x-html="`Create <b>${search}</b>`"></span>
                                            </x-ts:button>
                                        </div>

                                    </x-slot:after>
                                </x-ts:select.styled>
                            </template>

                            {{-- search dengan barcode --}}
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

                    <table class="min-w-full table-fixed border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-100 text-left text-xs text-gray-600">
                                <th class="p-2">No.</th>
                                <th class="p-2">Tipe</th>
                                <th class="p-2">SKU</th>
                                <th class="p-2">Barang</th>
                                <th class="p-2">Satuan</th>
                                <th class="p-2">Jumlah</th>
                                <th class="p-2">Harga / Estimasi Harga</th>
                                <th class="p-2">Diskon</th>
                                <th class="p-2">PPN</th>
                                <th class="p-2">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item,index) in cartItems" :key="index">
                                <tr class="border-b border-dashed text-sm even:bg-gray-100/75 hover:bg-indigo-100">
                                    <td class="p-1" x-text="index + 1"></td>
                                    <td class="p-1" x-text="item.bhp ? 'BHP':'Barang'"></td>
                                    <td class="p-1" x-text="item.sku"></td>
                                    <td class="p-1" x-text="item.nama"></td>
                                    <td class="p-1" x-text="item.satuan"></td>

                                    <td class="p-1">
                                        <input required type="number" x-model.number="item.jumlah" @keyup="updateItem(index)" min="1"
                                            class="h-8 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="Jumlah" />
                                    </td>
                                    <td class="p-1">
                                        <input required type="number" x-model.number="item.harga" @keyup="updateItem(index)" class="max-w-42 h-8 rounded-lg border border-gray-100 text-sm"
                                            placeholder="Harga" />
                                    </td>
                                    <td class="p-1">
                                        <input required type="number" x-model.number="item.diskon" @keyup="updateItem(index)" class="h-8 max-w-32 rounded-lg border border-gray-100 text-sm"
                                            placeholder="Diskon" />
                                    </td>
                                    <td class="p-1">
                                        <input required type="number" x-model.number="item.ppn" @keyup="updateItem(index)" class="h-8 max-w-24 rounded-lg border border-gray-100 text-sm"
                                            placeholder="%" />
                                    </td>
                                    <td class="p-1">
                                        <span x-text="`Rp ${(item.jumlah * item.harga).toLocaleString()}`"></span>
                                    </td>
                                    <td class="flex justify-end p-2">
                                        <x-ts:icon role="button" x-on:click="removeItemFromCart(index)" name="tabler.trash" class="h-5 w-auto text-red-500 hover:text-red-700" />
                                    </td>
                                </tr>

                            </template>
                            <tr x-show="cartItems.length === 0">
                                <td colspan="8" class="p-2 text-center text-sm italic text-gray-400">
                                    Belum Ada List Pesanan Barang
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>


            {{-- Summary --}}
            <div class="grid w-full grid-cols-2 rounded-md border border-indigo-200 bg-indigo-100/75 px-4 py-2">
                <div class="flex flex-col">
                    <span class="text-xs font-thin italic text-gray-500">Item:</span>
                    <span class="text-xl font-semibold text-indigo-500" x-text="totalItem"></span>
                </div>

                <div class="justity-end flex flex-col text-sm">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Subtotal</span>
                        <span class="text-gray-800" x-text="`Rp ${totalBeli.toLocaleString()}`"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Diskon</span>
                        <span class="text-red-500" x-text="`(${itemDiskon})` +  ` Rp ${totalDiskon.toLocaleString()}`"></span>
                    </div>

                    <div class="flex justify-between border-t-2 border-dashed border-indigo-200 font-semibold">
                        <span class="text-xs text-gray-600">Subtotal setelah diskon</span>
                        <span class="text-gray-800" x-text="`Rp ${(totalBeli - totalDiskon).toLocaleString()}`"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">PPN</span>
                        <span class="text-gray-800" x-text="`(${itemPpn}) Rp ${totalPpn.toLocaleString()}`"></span>
                    </div>
                    <div class="flex justify-between border-t-2 border-dashed border-indigo-200 pt-1">
                        <span class="text-base font-bold text-indigo-700">Total Pembayaran</span>
                        <span class="text-base font-bold text-indigo-600" x-text="`Rp ${((totalBeli - totalDiskon) + totalPpn).toLocaleString()}`"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- actions form --}}
        <div>
            <div class="ml-auto flex flex-row justify-end gap-2">
                {{-- cancel button --}}
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
                            <x-ts:button outline sm color="green" x-on:click="$dispatch('close-modal',{id:'modal-new-pesanan'})">
                                Ya, Batalkan
                            </x-ts:button>
                        </div>
                    </div>

                </div>

                {{-- submit button --}}
                <x-ts:button sm type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>

            </div>
        </div>


    </form>


    {{-- modal add barang --}}
    <x-filament::modal id="modal-new-barang" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Tambah Barang</x-slot:heading>

        <livewire:Master.Barang.Add :nama="$createTerm" :key="Str::random()" @new-barang-created="$refresh" />
    </x-filament::modal>


    {{-- modal add supplier --}}
    <x-filament::modal id="modal-new-supplier" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Tambah Supplier</x-slot:heading>


        <livewire:Master.Supplier.Add :nama="$createTerm" :key="Str::random()" @new-supplier-created="$refresh" />
    </x-filament::modal>
</div>

@script
    <script>
        Alpine.data('listPembelian', () => {
            return {
                sku: '',
                searchItem: '',
                totalItem: 0,
                totalBeli: 0,
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
                                // Update jumlah item yang sudah ada
                                this.cartItems[existingItemIndex].jumlah += 1;
                                this.recalculateTotal();
                            } else {
                                const newItem = {
                                    id: barang.id,
                                    bhp: barang.bhp,
                                    sku: barang.sku,
                                    nama: barang.nama,
                                    satuan: barang.satuan,
                                    jumlah: 1,
                                    harga: parseInt(barang.latest_harga),
                                    diskon: 0,
                                    ppn: 0,
                                    ppnAmount: 0,
                                    subtotal: 0,
                                    batch: null,

                                };
                                this.cartItems.push(newItem);
                                // Calculate dengan index item baru (paling akhir)
                                this.updateItem(this.cartItems.length - 1);

                                this.sku = '';
                                this.searchItem = '';
                                // this.totalItem = this.cartItems.length;
                            }
                        }).catch(error => {
                            console.error('Error retrieving barang data:', error)
                        });

                    } catch (error) {
                        console.error('Error retrieving barang data:', error)
                    }
                },

                updateItem(index) {
                    const item = this.cartItems[index];
                    if (item) {
                        item.subTotal = item.jumlah * item.harga;
                        item.ppnAmount = ((item.subTotal - item.diskon) * (item.ppn / 100))

                        this.recalculateTotal();
                    }
                },


                recalculateTotal() {
                    // Update total item
                    this.totalItem = this.cartItems.length;

                    // Hitung total beli dari semua item
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


                },

                removeItemFromCart(index) {
                    if (index >= 0 && index < this.cartItems.length) {
                        this.cartItems.splice(index, 1);
                        this.recalculateTotal(); // Recalculate setelah hapus
                    }
                },
            }
        });
    </script>
@endscript
