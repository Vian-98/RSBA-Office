<div>
    @if($suratSp3)
    <form wire:submit.prevent='submit' novalidate class="flex flex-col gap-2" autocomplete="off">

        {{-- Info Nomor SP3 & Peringatan Status --}}
        <div class="flex items-center justify-between rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-800">
            <span class="font-bold">Mengedit Surat SP3: {{ $suratSp3->no }}</span>
            <span class="italic text-[11px] text-amber-700">Setelah disimpan, status akan kembali ke PENDING untuk verifikasi ulang Keuangan.</span>
        </div>

        {{-- Error Alert jika ada validasi yang gagal --}}
        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-2.5 text-xs text-red-600 border border-red-200">
                <div class="font-bold mb-1 flex items-center gap-1">
                    <x-tabler-alert-circle class="size-4" />
                    Harap lengkapi isian berikut:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2">
            <x-ts:date wire:model.defer='tgl' placeholder="Tgl Surat" />

            <div x-data="{ isRekanan: @entangle('isRekanan') }" class="flex w-full flex-row items-center gap-1">
                <div x-show="!isRekanan" class="w-full">
                    <x-ts:input wire:model.defer="rekanan" placeholder="Input Rekanan (Teks Bebas)" />
                </div>

                <div x-show="isRekanan" class="w-full items-center">
                    <x-ts:select.styled wire:model.live.debounce='rekananId' placeholder="Pilih Rekanan / Supplier" :request="route('api.supplier')" select="label:nama|value:id">
                        <x-slot:after>
                            <div class="mb-2 flex items-center justify-center px-2">
                                <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-supplier'}); $wire.set('createTerm',search)">
                                    <span x-html="`Create <b>${search}</b>`"></span>
                                </x-ts:button>
                            </div>
                        </x-slot:after>
                    </x-ts:select.styled>
                </div>

                <span x-on:click="isRekanan = !isRekanan; if (!isRekanan && !$wire.rekanan) $wire.rekanan = '';" role="button" class="cursor-pointer">
                    <x-tabler-direction class="h-6 w-6 text-indigo-500" title="Ganti mode teks/pilihan." />
                </span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <x-ts:select.styled wire:model.defer='method_bayar' searchable :options="$caraBayarOptions" select="label:label|value:value" placeholder="Metode Bayar" />
            <x-ts:select.styled wire:model.live.debounce.300='jabatan' searchable :options="$mengetahuiOptions" select="label:label|value:value" placeholder="Mengetahui (Atasan TTD)" />
            <x-ts:select.styled wire:model.defer='verifikator_keuangan_id' searchable placeholder="Verifikator Keuangan" :request="route('api.karyawan.verifikator.keuangan')" select="label:label|value:id" />
        </div>
        <div>
            <x-ts:textarea wire:model.defer='keterangan' placeholder="Subject / Berita / Keterangan" />
        </div>

        <div x-data="listSp3Edit" class="mt-2 flex flex-col border-t-2 border-dashed py-4">
            <span class="text-xs italic text-gray-500">Rincian Pembayaran</span>
            <template x-for="(item, index) in itemsSp3" :key="index">
                <div class="flex w-full flex-row items-center gap-2 mb-2">
                    {{-- item --}}
                    <div class="w-1/4">
                        <x-ts:input type="text" x-model="item.nominal" @input="recalculateTotal" placeholder="Nominal" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                    </div>
                    <div class="w-3/4">
                        <x-ts:input x-model="item.keterangan" placeholder="Untuk Pembayaran" />
                    </div>

                    {{-- remove item --}}
                    <x-tabler-trash x-on:click="removeItem(index)" class="text-red-500 cursor-pointer" role="button" />
                </div>
            </template>
            <span class="mt-1 inline-flex items-center rounded-md px-2 py-1 text-xs text-indigo-500 hover:bg-indigo-50 hover:font-semibold cursor-pointer w-fit" role="button" x-on:click="addingItem">
                + Tambah Item
            </span>

            <div class="mt-3 flex w-full flex-col rounded-md bg-indigo-50/70 px-4 py-2 border border-indigo-100">
                <span class="text-xs italic text-gray-500">Total Pembayaran</span>
                <span class="text-xl font-bold text-indigo-600" x-text="`Rp${totalPembayaran.toLocaleString('id-ID')}`"></span>
            </div>
        </div>

        <div class="ml-auto flex justify-end gap-2 pt-2">
            <x-ts:button outline sm x-on:click="$dispatch('close-modal',{id:'modal-edit-sp3'})">Batal</x-ts:button>
            <x-ts:button type="submit" wire:click.prevent="submit" color="primary" sm icon="tabler.send" loading="submit">
                Simpan & Ajukan Ulang ke Keuangan
            </x-ts:button>
        </div>
    </form>
    @else
        <div class="text-center py-6 text-slate-400 text-sm">Memuat data SP3...</div>
    @endif
</div>

@script
    <script>
        Alpine.data('listSp3Edit', () => {
            return {
                totalPembayaran: 0,
                itemsSp3: $wire.entangle('listSp3'),

                init() {
                    this.recalculateTotal();
                },

                addingItem() {
                    const newItem = {
                        nominal: 0,
                        keterangan: ''
                    };
                    this.itemsSp3.push(newItem);
                },

                removeItem(index) {
                    this.itemsSp3.splice(index, 1);
                    this.recalculateTotal();
                },

                recalculateTotal() {
                    this.totalPembayaran = (this.itemsSp3 || []).reduce((sum, item) => {
                        let nom = String(item.nominal || '').replace(/\D/g, '');
                        return sum + (Number(nom) || 0);
                    }, 0);
                },
            }
        })
    </script>
@endscript
