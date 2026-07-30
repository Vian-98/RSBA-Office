<div class="flex flex-col gap-2">

    <x-ts:input wire:model.live.debounce.300='search' placeholder="Cari Nama Barang ..." />

    <div class="flex flex-col gap-2 overflow-y-auto">
        @forelse ($this->investigations as $item)
            @php
                $color = 'bg-indigo-50';
                if ($item->selisih < 0) {
                    $color = 'bg-red-50';
                }
            @endphp

            <div x-data="opnameInvestigasi({{ $item->id }})" class="flex w-full flex-col gap-2 rounded-md border border-gray-200 p-4" wire:key="investigasi-row-{{ $item->id }}">
                <div class="{{ $color }} flex gap-6 rounded-md p-2 text-left">
                    <div class="font-semibold">
                        <span>Stok Id: {{ $item->stok_id }} | </span>
                        <span>{{ $item->barang->nama }}</span>
                    </div>

                    <div @click="save()" class="ml-auto flex items-center justify-end" x-show="hasilInvestigasi && catatan && stokFisik">

                        <x-ts:icon x-show="!loading && !isSaved" sm role="button" class="text-indigo-500" name="tabler.device-floppy"></x-ts:icon>

                        <x-ts:icon x-show="!loading && isSaved" sm class="text-green-500" name="tabler.checks"></x-ts:icon>

                        <span x-show="loading" class="flex items-center text-xs text-gray-500">
                            <svg class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Menyimpan...
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div class="flex flex-col text-sm">
                        <span>Stok Saat Opname : {{ $item->stok_sistem_opname }}</span>
                        <span>Stok Fisik : {{ $item->stok_fisik }}</span>
                        <span>Selisih : <label x-text="textSelisih"></label></span>
                        <span>Opname Oleh : {{ $item->opname_oleh }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <x-ts:select.styled x-model="hasilInvestigasi" searchable :options="$this->getHasilInvestigasi()" select="label:label|value:id" placeholder="Hasil Investigasi" required />
                        <x-ts:input x-model="stokFisik" type="number" placeholder="Stok Fisik Sebenarnya" required />
                    </div>
                    <div class="col-span-2">
                        <x-ts:textarea x-model="catatan" placeholder="Keterangan / Justifikasi" required></x-ts:textarea>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm italic">
                Tidak ada data selisih, tidak perlu justifikasi.
            </div>
        @endforelse
    </div>
    <div>
        {{ $this->investigations->links() }}
    </div>

    <div class="mt-4 flex justify-end text-right">

        <div x-data="{ popUpConfirmSubmit: false }" class="relative inline-block">

            <div x-show="popUpConfirmSubmit" x-transition x-trap.noscroll="popUpConfirmSubmit" x-on:click.away="popUpConfirmSubmit = false" x-on:keydown.escape.window="popUpConfirmSubmit = false"
                x-anchor.bottom-end="$refs.simpanButton" class="absolute z-50 mt-2 w-max max-w-sm rounded-lg border border-gray-300 bg-white p-4 shadow-lg">

                <div class="mb-3 flex items-center justify-between">
                    <span class="flex flex-row items-center gap-2 whitespace-nowrap font-medium text-indigo-500">
                        <x-ts:icon name="tabler.alert-circle" class="h-5 w-5" />
                        Yakin Simpan Validasi ?
                    </span>
                </div>
                <!-- Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <x-ts:button outline sm color="red" x-on:click="popUpConfirmSubmit = false">
                        Tidak
                    </x-ts:button>

                    {{-- button action validasi --}}
                    <x-ts:button type="submit" loading="submitInvestigasiOpname" outline sm color="green" x-on:click="$wire.submitInvestigasiOpname()">
                        Ya, Simpan
                    </x-ts:button>
                </div>

            </div>

            <div x-ref="simpanButton">
                <x-ts:button x-on:click="popUpConfirmSubmit = true" icon="tabler.checks" sm color="green">Validasi & Sesuaikan Stok</x-ts:button>
            </div>
        </div>
    </div>
</div>
@script
    <script>
        Alpine.store('investigasiData', @js($this->investigationsData));

        Alpine.data('opnameInvestigasi', (id) => ({
            loading: false,
            soDetId: id,
            stokFisik: 0,
            catatan: '',
            hasilInvestigasi: '',
            get itemData() {
                return Alpine.store('investigasiData').find(item => item.id === this.soDetId);
            },

            init() {
                this.stokFisik = Number(this.itemData.stok_fisik) == 0 ? this.stokSistem : Number(this.itemData.stok_fisik);
                this.catatan = this.itemData?.investigasi?.catatan_investigasi ?? '';
                this.hasilInvestigasi = this.itemData?.investigasi?.hasil_investigasi ?? '';
            },

            get stokSistem() {
                return Number(this.itemData?.stok_sistem_opname ?? 0);
            },

            get isSaved() {
                if (!this.itemData?.investigasi) {
                    return false;
                }
                return this.catatan === this.itemData.investigasi.catatan_investigasi &&
                    this.hasilInvestigasi === this.itemData.investigasi.hasil_investigasi;
            },


            get selisih() {
                const fisik = Number(this.stokFisik) || 0;
                const sistem = Number(this.stokSistem) || 0;
                return fisik - sistem;
            },

            textSelisih() {
                return this.selisih === 0 ? '0' : this.selisih > 0 ? '+' + this.selisih : this.selisih;
            },

            validateForm() {
                this.showErrors = true;

                if (!this.hasilInvestigasi) {
                    return false;
                }

                if (!this.catatan.trim()) {
                    return false;
                }

                if (this.stokFisik === '' || this.stokFisik === null) {
                    return false;
                }

                return true;
            },

            save() {
                this.loading = true;
                // Kirim ke Livewire
                $wire.call('saveRow', this.soDetId, this.stokFisik, this.selisih, this.catatan, this.hasilInvestigasi)
                    .then((response) => {

                        // console.log('response : ' + response);

                        // Update the store with the saved data
                        const store = Alpine.store('investigasiData');
                        const itemIndex = store.findIndex(item => item.id === this.soDetId);

                        if (itemIndex !== -1) {
                            // // Update the investigasi data in the store
                            store[itemIndex] = response;
                        }
                        // Feedback visual halus
                        this.$el.classList.add('bg-g1100reen-50');
                        setTimeout(() => this.$el.classList.remove('bg-green-50'), 400);
                    })
                    .catch(() => {
                        this.$el.classList.add('bg-red-50');
                        setTimeout(() => this.$el.classList.remove('bg-red-50'), 600);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }));
    </script>
@endscript
