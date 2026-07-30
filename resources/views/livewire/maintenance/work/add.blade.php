<div class="flex flex-col gap-4">

    <div class="flex w-full flex-col">
        <span class="text-lg font-bold text-indigo-500">#{{ $maintenanceWork->id }}</span>
        <span>Mulai Pengerjaan : {{ Carbon\Carbon::parse($maintenanceWork->mulai)->diffForHumans() }}</span>
        <span>Oleh : {{ $maintenanceWork->user_mulai }}</span>
    </div>

    <div class="flex w-full flex-col rounded-md border border-red-200 bg-red-50 p-2">
        <span class="py-2 text-sm italic text-red-500"> Permintaan</span>
        <div class="flex flex-col gap-2 text-sm text-gray-600">
            <span>Oleh : {{ $this->permintaan()->user_request }}</span>
            <span>Masalah / Kendala : {{ $this->permintaan()->note }}</span>
        </div>
    </div>


    <form wire:submit.prevent='submit' class="flex w-full flex-col gap-4 rounded-md border p-3">
        <span class="text-sm italic text-indigo-500"> Laporan Maintenance</span>
        @error('any')
            <span>{{ $message }}</span>
        @enderror
        {{-- riwayat perbaikan --}}
        <div class="flex flex-col gap-2 lg:grid lg:grid-cols-4">
            <x-ts:select.styled wire:model.defer='status' :options="$optionsSetelahPerbaikan" select="value:value|label:label" placeholder="Status Setelah Maintenance" />

            <div class="col-span-2">
                <x-ts:upload wire:model.defer='dokumentasis' placeholder="Dokumentasi" multiple delete accept="images/png"></x-ts:upload>

            </div>

            <div class="col-span-4">
                <x-ts:textarea wire:model.defer='keterangan' placeholder="Keterangan / Deskripsi Hasil Maintenance" />
            </div>
        </div>

        {{-- tambah parts jika ada --}}
        <div x-data="Parts" class="flex w-full flex-col border-t border-dashed border-gray-200">
            <div class="flex flex-row gap-4">

                <span @click="panelParts = true" class="py-2 text-sm italic text-indigo-500" :class="{ 'font-bold': panelParts }" role="button"> Alat & Komponen</span>

                <span @click="panelParts = false" x-show="cartPengajuan.length > 0" :class="{ 'font-bold': !panelParts }" class="py-2 text-sm italic text-indigo-500" role="button">
                    <span x-text="cartPengajuan.length" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-semibold text-white">
                    </span>
                    Pengajuan
                </span>
            </div>

            {{-- panel pengajuan item dengan stok kosong --}}
            <div x-show="!panelParts" class="flex flex-col gap-2">
                <div class="border-gray sticky top-0 grid grid-cols-9 gap-1 rounded-md bg-gray-100 p-1 text-xs font-semibold">
                    <span>Item Diganti</span>
                    <span class="col-span-2">Asset Kode</span>
                    <span>Barang</span>
                    <span>SKU Barang</span>
                    <span>Satuan</span>
                    <span>Stok Tersedia</span>
                    <span>Pengajuan</span>
                    <span></span>
                </div>
                <template x-for="(item, index) in cartPengajuan" :key="index">
                    <div class="border-gray grid grid-cols-9 gap-1 rounded-lg border border-dashed p-1 text-xs">
                        <span x-text="item.penggantian_nama"></span>
                        <span class="col-span-2" x-text="item.penggantian_asset_kode"></span>
                        <span x-text="item.barang"></span>
                        <span x-text="item.sku"></span>
                        <span x-text="item.satuan"></span>
                        <span x-text="item.stok"></span>
                        <span x-text="item.qty"></span>
                        <span class="flex items-center justify-end">
                            <x-ts:icon x-on:click="removeItem(index);" name="tabler.trash" class="h-5 w-auto text-red-500" role="button" />
                        </span>

                    </div>
                </template>
            </div>
            {{-- end panel pangajuan --}}


            {{-- Panel Part Penggantian --}}
            <div x-show="panelParts" x-data="listParts" class="flex flex-col gap-2">
                <div x-show="duplicateIds.size > 0" class="rounded-md bg-red-50 p-2 text-xs text-red-600">
                    Ada part yang duplikat. Harap periksa kembali daftar part Anda.
                </div>

                {{-- Pencarian Items --}}
                <div class="grid gap-2 lg:grid-cols-3">
                    <div>
                        <x-ts:select.styled searchable x-model="penggantianId" :options="$this->itemComponents()" select="value:value|label:label" placeholder="Untuk Penggantian"
                            x-on:select="$wire.set('partDigantiKategoriId', $event.detail.select.kategori_id);
                            partDigantiNama=$event.detail.select.label;
                            partDigantiAssetKode=$event.detail.select.kode;" />
                    </div>
                    <div x-data="{ useSelect: true }" class="flex flex-row gap-4">
                        <div class="w-full" wire:key="parts-select-{{ $partDigantiKategoriId }}">
                            {{-- option barang --}}
                            <template x-if="useSelect">
                                <x-ts:select.styled searchable x-model="selectNewParts" select="label:nama|value:id" :request="$routeNewPart" placeholder="Cari barang"
                                    x-on:select="getStoks($event.detail.select.id)">
                                </x-ts:select.styled>
                            </template>

                            {{-- search dengan barcode --}}
                            <template x-if="!useSelect">
                                <x-ts:input x-ref="barcodeSearch" x-model="scanNewParts" placeholder="Scan barang" icon="tabler.barcode" />
                            </template>

                        </div>
                        <span x-on:click="useSelect = !useSelect" role="button">
                            <x-tabler-direction class="h-8 w-8 text-indigo-500" title="Switch using select." />
                        </span>
                    </div>
                </div>
                {{-- end pencarian Items --}}



                {{-- List item penggantian --}}
                <div class="max-h-[60vh] w-full overflow-auto rounded-lg border border-dashed border-gray-200 p-1 text-sm md:max-h-none">
                    <!-- Headers -->
                    <div class="sticky top-0 z-10 mt-4 flex w-full gap-4 rounded-md px-2 text-xs font-semibold md:static">
                        <div class="flex w-2/5 min-w-fit items-center justify-between gap-1 rounded-md bg-red-50 p-2 text-red-500">
                            <span class="min-w-[80px] flex-1">Item Diganti</span>
                            <span class="min-w-[80px] flex-1">Asset Kode</span>
                            <span class="min-w-[80px] flex-1">Alasan</span>
                        </div>

                        <div class="flex w-3/5 min-w-fit items-center justify-between gap-1 rounded-md bg-indigo-50 p-2 text-indigo-500">
                            <span class="min-w-[80px] flex-1">Barang</span>
                            <span class="min-w-[80px] flex-1">SKU Barang</span>
                            <span class="min-w-[60px] flex-1">Satuan</span>
                            <span class="min-w-[80px] flex-1">Stok Tersedia</span>
                            <span class="min-w-[60px] flex-1">Qty</span>
                            <span class="flex min-w-[80px] flex-1 justify-end">Actions</span>
                        </div>
                    </div>

                    <!-- Groups BHP / Komponens -->
                    <template x-for="(group, groupName) in groupedItems" :key="groupName">
                        <div x-show="group.length > 0" class="relative my-4 rounded-lg border border-dashed p-2"
                            :class="{
                                'border-red-200': groupName === 'bhp',
                                'border-indigo-200': groupName !== 'bhp'
                            }">
                            <span class="absolute -left-0 -top-3 rounded-sm bg-white px-2 text-xs font-semibold italic"
                                :class="{
                                    'text-red-500': groupName === 'bhp',
                                    'text-indigo-500': groupName !== 'bhp'
                                }"
                                x-text="groupName === 'bhp' ? 'BHP' : 'Komponen'">
                            </span>

                            <!-- Each data row -->
                            <div class="flex w-full flex-col gap-1 text-sm">
                                <template x-for="(item, index) in group" :key="index">
                                    <div class="flex w-full items-center gap-4 py-1 hover:bg-gray-50">
                                        <!-- Left section -->
                                        <div class="flex w-2/5 min-w-fit items-center justify-between gap-1">
                                            <span class="min-w-[80px] flex-1 truncate" x-text="item.penggantian_nama"></span>
                                            <span class="min-w-[80px] flex-1 truncate" x-text="item.penggantian_asset_kode"></span>

                                            <select required x-model="item.alasan" :disabled="item.penggantian == 'bhp' || item.penggantian == 'baru'"
                                                class="min-w-[80px] flex-1 truncate border-none bg-transparent p-0 text-sm focus:outline-none focus:ring-0">
                                                <option value="">-</option>
                                                <option value="rusak">Rusak</option>
                                                <option value="hilang">Hilang</option>
                                                <option value="lainya" :selected="item.penggantian == 'bhp' || item.penggantian == 'baru'">Lainya</option>
                                            </select>
                                        </div>

                                        <!-- Right section -->
                                        <div class="flex w-3/5 min-w-fit items-center justify-between gap-1">
                                            <span class="min-w-[80px] flex-1 truncate" x-text="item.barang"></span>
                                            <span class="min-w-[80px] flex-1 truncate" x-text="item.sku"></span>
                                            <span class="min-w-[60px] flex-1 truncate" x-text="item.satuan"></span>
                                            <span class="min-w-[80px] flex-1 truncate" x-text="item.stok"></span>

                                            <div class="min-w-[60px] flex-1">
                                                <input type="number" x-model.number="item.qty" :value="item.qty" :max="item.stok"
                                                    class="w-full max-w-[80px] border-none bg-transparent p-0 text-sm focus:outline-none focus:ring-0" placeholder="Qty" />
                                            </div>

                                            <div class="flex min-w-[80px] flex-1 items-center justify-end gap-2">
                                                <x-ts:icon x-show="item.stok === 0" name="tabler.shopping-cart-plus" class="h-5 w-5 text-indigo-500 hover:text-indigo-700" role="button"
                                                    @click="addingToCart(index)" />
                                                <x-ts:icon name="tabler.trash" class="h-5 w-5 text-red-500 hover:text-red-700" role="button" @click="removeItem(index)" />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- End List item penggantian --}}
            </div>
            {{-- End Panel Penggantian --}}

        </div>

        {{-- tombol simpan --}}
        <div class="flex justify-end gap-2">
            <x-ts:button outline color="secondary" sm x-on:click="$dispatch('close-modal',{id:'modal-maintenance-work-add'})">Tutup</x-ts:button>
            <x-ts:button type="submit" sm>Simpan</x-ts:button>
        </div>
    </form>
</div>

@script
    <script>
        Alpine.data('Parts', () => {
            return {
                cartPengajuan: @entangle('komponens_diajukan'),
                panelParts: true,

                // remove item
                removeItem(index) {
                    this.cartPengajuan.splice(index, 1);
                    this.panelParts = cartPengajuan.length === 0;
                },
            }
        })
    </script>
@endscript

@script
    <script>
        Alpine.data('listParts', () => {

            return {
                penggantianId: null,
                selectNewParts: null,
                scanNewParts: null,
                partDigantiNama: null,
                partDigantiAssetKode: null,
                itemsParts: @entangle('komponens'),
                cartPengajuan: @entangle('komponens_diajukan'),
                duplicateIds: new Set(),

                get groupedItems() {
                    return {
                        part: this.itemsParts.filter(item => item.penggantian != 'bhp' || item.penggantian === 'baru'),
                        bhp: this.itemsParts.filter(item => item.penggantian === 'bhp'),
                    };

                },

                // init() {
                // Initialize duplicate tracking
                // this.updateDuplicateTracking();


                // Watch for livewire validation errors
                // Livewire.on('validationErrors', (errors) => {
                //     console.log('errors validation livewire : ' + errors)
                //     this.errors = errors;
                // })

                // },



                isDuplicate(item) {
                    if (!item.part) return false;
                    return this.itemsParts.filter(i =>
                        i.part === item.part &&
                        i.penggantian === item.penggantian
                    ).length > 1;
                },

                // Update our duplicate tracking
                updateDuplicateTracking() {
                    const idCounts = {};
                    this.duplicateIds.clear();

                    // Count occurrences of each part + penggantian combination
                    this.itemsParts.forEach(item => {
                        if (item.part && item.penggantian) {
                            const comboKey = `${item.part}-${item.penggantian}`;
                            idCounts[comboKey] = (idCounts[comboKey] || 0) + 1;
                        }
                    });

                    // Mark items as duplicates if their combination appears more than once
                    this.itemsParts.forEach(item => {
                        if (item.part && item.penggantian) {
                            const comboKey = `${item.part}-${item.penggantian}`;
                            if (idCounts[comboKey] > 1) {
                                this.duplicateIds.add(item.part);
                            }
                        }
                    });

                },


                // Get data stok
                getStoks(id) {
                    try {
                        $wire.getStokBarang(id).then(barang => {

                            const barangId = id;
                            const currentStock = parseInt(barang.stok) || 0;

                            // Hitung total qty yang sudah ada di cart untuk barang ini
                            const totalQtyInCart = this.itemsParts
                                .filter(item => item.barang_id === barangId)
                                .reduce((total, item) => total + item.qty, 0);


                            // Validasi stok tersedia
                            const availableStock = currentStock - totalQtyInCart;

                            console.log('currentStok : ' + currentStock);
                            console.log('totalQtyInCart : ' + totalQtyInCart);
                            console.log('availabelStok : ' + availableStock);

                            // if (availableStock <= 0) {
                            //     $interaction('toast')
                            //         .error('Stok Tidak Cukup', `Stok tersedia: ${currentStock}, sudah dipesan: ${totalQtyInCart}`)
                            //         .send();
                            //     return;
                            // }

                            const existingItem = this.itemsParts.find(item =>
                                (item.part === id || item.barang_id === id) && item.penggantian === this.penggantianId
                            );

                            if (existingItem) {
                                if (existingItem.qty >= availableStock) {
                                    $interaction('toast')
                                        .warning('Stok Terbatas', `Maksimal bisa ditambah: ${availableStock}`)
                                        .send();
                                    return;
                                }


                                existingItem.qty += 1;

                                // Update stok untuk semua item dengan barang yang sama
                                this.updateStockForItemAndSameBarang(barangId, currentStock);
                            } else {
                                console.log('availableStok : ' + availableStock);
                                this.itemsParts.push({
                                    penggantian: this.penggantianId,
                                    penggantian_nama: this.partDigantiNama,
                                    penggantian_asset_kode: this.partDigantiAssetKode,
                                    alasan: '',
                                    part: id,
                                    barang_id: id,
                                    sku: barang.sku || null,
                                    barang: barang.nama || null,
                                    // stok: parseInt(barang.stok) || 0,
                                    stok: availableStock,
                                    satuan: barang.satuan || 'Unit',
                                    qty: 1
                                });

                            }
                            // Update stok untuk semua item dengan barang yang sama
                            // this.updateStockForItemAndSameBarang(barangId, availableStock);

                            this.updateDuplicateTracking();
                            this.selectNewParts = null;
                            this.scanNewParts = null;

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



                updateStockForItemAndSameBarang(barangId, currentStock) {
                    this.itemsParts.forEach(item => {
                        if (item.barang_id === barangId) {
                            item.stok = currentStock;
                            // Update available_stock
                            const totalQtyForThisBarang = this.itemsParts
                                .filter(i => i.barang_id === barangId)
                                .reduce((total, i) => total + i.qty, 0);
                            item.available_stock = Math.max(0, currentStock - totalQtyForThisBarang);
                        }
                    });
                },


                addingToCart(index) {
                    const newItem = this.itemsParts[index];
                    this.cartPengajuan.push(newItem);
                    this.itemsParts.splice(index, 1);
                },


                // remove item
                removeItem(index) {
                    this.itemsParts.splice(index, 1);
                    this.updateDuplicateTracking();
                },
            }
        })
    </script>
@endscript
