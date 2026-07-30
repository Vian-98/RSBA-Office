<div class="flex flex-col gap-4">
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" x-data="{
        priority: {
            value: 'normal',
            label: 'Normal'
        }
    }">
        {{-- Flag Priority --}}
        <span
            x-bind:class="{
                'text-indigo-500 border-indigo-200 bg-indigo-100': priority.value === 'normal',
                'text-orange-500 border-orange-200 bg-orange-100': priority.value === 'penting',
                'text-red-500 border-red-200 bg-red-100': priority.value === 'darurat'
            }"
            class="max-w-1/4 rounded-lg border border-indigo-200 bg-indigo-100 px-2 py-1 text-indigo-500">Pangajuan <span x-text="priority.label"></span></span>

        <div class="flex flex-col gap-2">
            {{-- Options Priority --}}
            <x-ts:select.styled wire:model.defer='form.priority' :options="$priorityOptions" select="value:value|label:label" placeholder="Urgency / Prioritas" x-on:select="priority = $event.detail.select" />

            {{-- Keterangan --}}
            <x-ts:textarea wire:model.defer='form.keterangan' placeholder="Keterangan / Deskripsi Pengajuan" />

            {{-- Lampiran --}}
            <x-ts:upload wire:model.defer='form.lampirans' placeholder="Lampiran (Optional)" multiple delete accept="images/png/pdf"></x-ts:upload>
        </div>

        <div x-data="listPengajuanItems" class="flex flex-col gap-2">
            <span class="text-xs italic text-gray-500">Cari barang yang akan diajukan.</span>
            <div class="grid grid-cols-2">

                {{-- Select Options Barang --}}
                <x-ts:select.styled x-model.debounce.300ms='searchItem' :request="route('api.barang.ref')" select="label:nama|value:id" placeholder="Pilihan Barang" x-on:select="addingCart($event.detail.select.id)">
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
                {{--  --}}
            </div>
            <div class="w-full">
                <table class="min-w-full table-fixed border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-sm">
                            <td class="ms-4 flex py-2">No.</td>
                            <td class="py-2">Barang</td>
                            <td class="py-2">Kategori</td>
                            <td class="py-2">Consumable</td>
                            <td class="py-2">Satuan</td>
                            <td class="py-2">Jumlah Pengajuan</td>
                            <td class="py-2">Harga (Estimasi)</td>
                            <td class="flex justify-self-end py-2 pe-4 italic text-gray-500">Aksi</td>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item,index) in listItems" :key="index">
                            <tr class="text-sm odd:bg-gray-50 hover:bg-indigo-50">
                                <td class="ms-4 flex" x-text="index + 1"></td>
                                <td x-text="item.nama"></td>
                                <td x-text="item.kategori"></td>
                                <td x-text="(item.bhp)? 'Ya': 'Bukan'"></td>
                                <td x-text="item.satuan"></td>
                                <td>
                                    <input type="number" min="1" x-model.number="item.jumlah" class="h-6 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="Jumlah" />
                                </td>
                                <td>
                                    <input type="number" min="0" x-model.number="item.harga_est" class="h-6 max-w-32 rounded-lg border border-gray-100 text-sm" placeholder="Jumlah" />
                                </td>
                                <td class="flex flex-row gap-4 justify-self-end pe-4">
                                    <x-tabler-message-question class="h-5 w-5 text-indigo-500" role="button" @click="addingSpecsItem(index)" />

                                    <x-tabler-trash class="h-5 w-5 text-red-500" role="button" @click="removeItemFromList(index)" />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- footer --}}
        <div class="flex justify-end gap-2">
            <x-ts:button outline sm color="dark" @click="$dispatch('close-modal',{id:'modal-add-permintaan-beli'})">Tutup</x-ts:button>

            <x-ts:button type="submit" sm>Simpan</x-ts:button>
        </div>
    </form>


    {{-- MODAL:: SPESIFIKASI Item Yang diajukan --}}
    <x-filament::modal id="modal-specs-item" width="xl" :autofocus="false">
        <x-slot:heading>
            Spesfikasi Item :
            <span class="text-indigo-500"
                x-text="$store.pengajuanStore.currentEditingIndex !== null 
            ? $store.pengajuanStore.listItems[$store.pengajuanStore.currentEditingIndex]?.nama 
            : ''"></span>
        </x-slot:heading>

        <div x-data="{
            newSpecLabel: '',
            newSpecValue: '',
            currentSpecs: [],
        
            init() {
                // Initialize dan watch untuk changes
                this.updateSpecs();
        
                this.$watch('$store.pengajuanStore.currentEditingIndex', () => {
                    this.updateSpecs();
                });
            },
        
            // Computed property untuk mengecek apakah kedua field terisi
            get bothFieldsFilled() {
                return this.newSpecLabel.trim() !== '' && this.newSpecValue.trim() !== '';
            },
        
            updateSpecs() {
                this.currentSpecs = $store.pengajuanStore.getCurrentSpecs();
                this.newSpecLabel = '';
                this.newSpecValue = '';
            },
        
            addNewSpec() {
                if (this.newSpecLabel && this.newSpecValue) {
                    this.currentSpecs.push({
                        label: this.newSpecLabel,
                        value: this.newSpecValue
                    });
                    this.newSpecLabel = '';
                    this.newSpecValue = '';
                }
            },
        
            saveRowSpec() {
                if (this.newSpecLabel && this.newSpecValue) {
                    this.currentSpecs.push({
                        label: this.newSpecLabel,
                        value: this.newSpecValue
                    });
                    this.newSpecLabel = '';
                    this.newSpecValue = '';
                }
            }
        }">

            {{-- view test --}}
            <!-- Current Specs List -->
            <template x-for="(spec, index) in currentSpecs" :key="index">
                <div class="grid grid-cols-8 items-center gap-1">

                    <div class="col-span-2">
                        <x-filament::input x-model="spec.label" type="text" placeholder="Label" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                    </div>

                    <div class="col-span-5">
                        <x-filament::input x-model="spec.value" type="text" placeholder="Value" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                    </div>

                    <div class="justify-self-end pe-2">
                        <button type="button" @click="currentSpecs.splice(index, 1)" class="text-red-500">
                            <x-tabler-trash class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </template>

            <!-- Add New Spec -->
            <div class="grid grid-cols-8 items-center gap-1">

                <div class="col-span-2">
                    <x-filament::input x-model="newSpecLabel" type="text" placeholder="Label" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                </div>

                <div class="col-span-5">
                    <x-filament::input x-model="newSpecValue" type="text" placeholder="Value" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                </div>

                <div class="justify-self-end pe-2">
                    <x-tabler-device-floppy x-show="bothFieldsFilled" class="h-5 w-5 text-green-500" role="button" @click="saveRowSpec()" />
                </div>

            </div>
            <span role="button" class="text-xs text-indigo-500 hover:italic" @click="addNewSpec()">+ Tambah</span>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 pt-4">
                <x-ts:button outline xs color="dark" type="button" x-on:click="$dispatch('close-modal', { id: 'modal-specs-item' })">
                    Tutup
                </x-ts:button>
            </div>
        </div>
    </x-filament::modal>
    {{-- End modal spesifikasi pengajuan --}}

</div>

@script
    <script>
        // Alpine Store Data
        Alpine.store('pengajuanStore', {
            currentEditingIndex: null,
            listItems: [],

            setEditingIndex(index) {
                this.currentEditingIndex = index;
            },

            setListItems(items) {
                this.listItems = items;
            },

            saveSpecs(specsData) {
                if (this.currentEditingIndex !== null && this.listItems[this.currentEditingIndex]) {
                    const specsArray = typeof specsData === 'string' ?
                        JSON.parse(specsData) :
                        specsData;

                    this.listItems[this.currentEditingIndex].specs = specsArray;
                }
            },

            getCurrentSpecs() {
                if (this.currentEditingIndex !== null && this.listItems[this.currentEditingIndex]) {
                    return this.listItems[this.currentEditingIndex].specs || [];
                }
                return [];
            },

            resetEditing() {
                this.currentEditingIndex = null;
            }
        });


        // List Pengajuan
        Alpine.data('listPengajuanItems', () => {
            return {
                searchItem: '',
                listItems: $wire.entangle('form.items'),

                init() {
                    // Share listItems dengan store
                    this.$watch('listItems', (value) => {
                        Alpine.store('pengajuanStore').setListItems(value);
                    });
                },

                addingCart(id) {
                    try {
                        $wire.getBarang(id).then(barang => {
                            const existingItem = this.listItems.find(item => item.id === barang.id);

                            if (existingItem) {
                                existingItem.jumlah += 1;
                            } else {
                                const newItem = {
                                    id: barang.id,
                                    bhp: barang.bhp,
                                    sku: barang.sku,
                                    nama: barang.nama,
                                    kategori: barang.kategori,
                                    satuan: barang.satuan,
                                    jumlah: 1,
                                    harga_est: barang.harga_beli_latest,
                                    subTotal: 0,
                                    specs: []
                                };
                                this.listItems.push(newItem);
                                this.totalItem = this.listItems.length;
                                this.sku = '';
                                this.searchItem = '';
                            }

                        }).catch(error => {
                            console.error('Error :', error);
                        });
                    } catch (error) {
                        console.error('Error menambah list items:', error);
                    }
                },

                removeItemFromList(index) {
                    this.listItems.splice(index, 1);
                },

                addingSpecsItem(index) {
                    Alpine.store('pengajuanStore').setEditingIndex(index);
                    // Open modal
                    $dispatch('open-modal', {
                        id: 'modal-specs-item'
                    });
                },

            }
        })
    </script>
@endscript
