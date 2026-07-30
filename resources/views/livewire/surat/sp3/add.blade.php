<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-2" autocomplete="off">

        <div class="grid grid-cols-2 gap-2">
            <x-ts:date wire:model.defer='tgl' placeholder="Tgl Surat" />

            <div x-data="{ isRekanan: true }" class="flex w-full flex-row items-center gap-1">
                <div x-show="!isRekanan" class="w-full">
                    <x-ts:input wire:model.defer="rekanan" placeholder="Input Rekanan" />
                </div>

                <div x-show="isRekanan" class="w-full items-center">
                    <x-ts:select.styled wire:model.live.debounce='rekananId' placeholder="Rekanan" :request="route('api.supplier')" select="label:nama|value:id" is>

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

                <span x-on:click="isRekanan = !isRekanan" role="button">
                    <x-tabler-direction class="h-d w-6 text-indigo-500" title="Switch using input." x-show="isRekanan" />
                    <x-tabler-direction class="w-d h-6 text-indigo-500" title="Switch using select." x-show="!isRekanan" />
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <x-ts:select.styled wire:model.defer='method_bayar' searchable :options="$caraBayarOptions" select="label:label|value:value" placeholder="Metode Bayar" />
            <x-ts:select.styled wire:model.live.debounce.300='jabatan' searchable :options="$mengetahuiOptions" select="label:label|value:value" placeholder="Mengetahui" />
        </div>
        <div>
            <x-ts:textarea wire:model.defer='keterangan' placeholder="Subject / Berita / Keterangan" />
        </div>

        {{-- error tidak ada list item --}}
        <div class="flex flex-row items-center text-sm text-red-500">
            @error('listSp3')
                <x-tabler-info-circle class="size-4" />
                <span> {{ $message }}</span>
            @enderror
        </div>

        <div x-data="listSp3" class="mt-2 flex flex-col border-t-2 border-dashed py-4">
            <span class="text-xs italic text-gray-500">Rincian Pembayaran</span>
            <template x-for="(item, index) in itemsSp3" :key="index">
                <div class="flex w-full flex-row items-center gap-2">
                    {{-- item --}}
                    <div class="w-1/4">
                        <x-ts:input type="text" x-model="item.nominal" @input="recalculateTotal" required placeholder="Nominal" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />

                    </div>
                    <div class="w-3/4">
                        <x-ts:input x-model="item.keterangan" placeholder="Untuk Pembayaran" required />
                    </div>

                    {{-- remove item --}}
                    <x-tabler-trash x-on:click="removeItem(index)" class="text-red-500" role="button" />
                </div>

                {{-- addin item --}}
            </template>
            <span class="mt-2 flex-row items-center rounded-md px-2 text-indigo-500 hover:bg-indigo-200/35 hover:font-semibold" role="button" x-on:click="addingItem">
                + Tambah Item
            </span>


            <div class="mt-2 flex w-full flex-col rounded-md bg-indigo-200/25 px-4 py-2">
                <span class="text-xs italic text-gray-500">Total Pembayaran</span>
                <span class="text-xl font-bold text-indigo-500" x-text="`Rp. ${totalPembayaran.toLocaleString()}`"></span>

            </div>
        </div>


        <div x-data="{ popUpConfirmSubmit: false }" class="ml-auto flex justify-end gap-2">
            <x-ts:button outline sm x-on:click="$dispatch('close-modal',{id:'modal-add-sp3'})">Tutup</x-ts:button>
            <x-ts:button x-on:click="popUpConfirmSubmit = true" x-ref="simpanSp3" sm icon="tabler.checks">Simpan</x-ts:button>


            <div x-show="popUpConfirmSubmit" x-transition x-trap.noscroll="popUpConfirmSubmit" x-on:click.away="popUpConfirmSubmit = false" x-on:keydown.escape.window="popUpConfirmSubmit = false"
                x-anchor.bottom-end="$refs.simpanSp3" class="absolute z-50 mt-2 w-max max-w-sm rounded-lg border border-gray-300 bg-white p-4 shadow-lg">


                <div class="flex items-center justify-between">
                    <span class="flex flex-row items-center gap-2 whitespace-nowrap text-sm font-medium text-indigo-500">
                        <x-ts:icon name="tabler.alert-circle" class="h-5 w-5" />
                        Simpan Surat SP3 ?
                    </span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="ms-4 flex flex-row items-center gap-2 whitespace-nowrap text-wrap text-xs font-light text-gray-500">
                        Pilih kirim untuk tanda tangan secara digital, atau manual untuk untuk printout.
                    </span>
                </div>
                <!-- Actions -->
                <div class="mt-2 flex justify-end gap-2">
                    <x-ts:button outline xs color="indigo" icon="tabler.send" loading="submit" x-on:click="$wire.submit(true)">
                        Kirim
                    </x-ts:button>

                    {{-- button action validasi --}}
                    <x-ts:button xs loading="submitManual" icon="tabler.file" outline sm color="gray" x-on:click="$wire.submit(false)">
                        Manual, Cetak
                    </x-ts:button>
                </div>

            </div>
        </div>

    </form>


    <div id="print-sp3" class="hidden" x-on:print-out-sp3.window="$nextTick(() => printArea('print-sp3'))">
        @if ($suratSp3)
            <livewire:Surat.Sp3.PrintSp3 :$suratSp3 :key="Str::random(5)" />
        @endif
    </div>

</div>

@script
    <script>
        Alpine.data('listSp3', () => {
            return {
                totalPembayaran: 0,
                itemsSp3: $wire.entangle('listSp3'),

                addingItem() {
                    const newItem = {
                        nominal: 0,
                        keterangan: ''
                    };
                    this.itemsSp3.push(newItem);
                },

                removeItem(index) {
                    this.itemsSp3.splice(index, 1);
                },

                recalculateTotal() {
                    this.totalPembayaran = this.itemsSp3.reduce((sum, item) => {
                        let nom = String(item.nominal || '').replace(/\D/g, '');
                        return sum + (Number(nom) || 0);
                    }, 0);
                },
            }
        })
    </script>
@endscript
