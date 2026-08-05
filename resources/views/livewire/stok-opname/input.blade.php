<div class="flex h-screen flex-col gap-2 pl-24">

    <div class="flex-shrink-0">
        <span class="inline-flex max-w-fit flex-row items-center gap-2 rounded-lg border border-orange-200 bg-orange-100 px-2 py-1 text-xs text-orange-500">
            <x-ts:icon name="tabler.info-circle" />
            Silahkan input hanya pada stok yang selisih.
        </span>
    </div>

    {{-- kategori barang selected --}}
    <div class="flex flex-shrink-0 items-center gap-4 overflow-hidden">
        <x-ts:checkbox label="Stok Selisih." wire:click="$toggle('sudahTerinput')" />

        <div class="scrollbar-hidden -mr-4 flex-1 overflow-x-auto pb-2 pr-4">
            <div class="flex gap-2 p-2">
                <x-ts:button xs outline wire:click="$set('filteredKategori',null)" :color="$filteredKategori == null ? 'blue' : 'gray'" class="flex-shrink-0">Semua</x-ts:button>

                @foreach ($kategoriBarang as $kategori)
                    <x-ts:button xs outline wire:click="$set('filteredKategori',{{ $kategori->id }})" :color="$filteredKategori == $kategori->id ? 'blue' : 'gray'" class="flex-shrink-0">
                        {{ $kategori->nama }}
                    </x-ts:button>
                @endforeach
            </div>
        </div>
    </div>


    {{-- search item --}}
    <div class="flex w-full flex-col justify-between lg:flex-row">
        <div class="w-full flex-shrink-0 lg:w-1/4">
            <x-ts:input wire:model.live.debounce.500='cari' placeholder="Cari nama barang . . ." autocomplete="off"></x-ts:input>
        </div>
        <div class="flex text-sm">
            {{ $this->getDataSo->links() }}
        </div>

    </div>

    {{-- each data --}}
    <div class="flex min-h-0 flex-1 flex-col gap-2 rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="scrollbar-hidden max-h-[calc(100vh-200px)] overflow-auto">
            {{-- <div class="scrollbar-hidden flex-1 overflow-auto"> --}}
            <table class="w-full">
                <thead class="sticky top-0 z-10 bg-gray-100 shadow-sm">
                    <tr class="text-sm font-semibold text-gray-700">
                        <td class="px-4 py-2 text-left">Stok Id</td>
                        <td class="px-4 py-2 text-left">Batch / SN</td>
                        <td class="px-4 py-2 text-left">Nama Barang</td>
                        <td class="px-4 py-2 text-left">Satuan</td>
                        <td class="px-4 py-2 text-left">Harga Satuan</td>
                        <td class="px-4 py-2 text-left">Stok Sistem</td>
                        <td class="px-4 py-2 text-left">Stok Fisik</td>
                        <td class="px-4 py-2 text-left">Keterangan</td>
                        <td class="px-4 py-2 text-right">Selisih</td>
                    </tr>
                </thead>
                <tbody x-data="{ activeRowId: null }">
                    @foreach ($this->getDataSo as $item)
                        <tr x-data="opnameData({{ $item->id }}, {{ $item->stok_sistem_opname }}, {{ $item->stok_fisik }}, '{{ addslashes($item->ket ?? '') }}')"
                            x-on:click="activeRowId  = (activeRowId === {{ $item->id }}) ? null : {{ $item->id }}; $nextTick(()=> $refs.input_fisik_{{ $item->id }}.focus())"
                            :class="{
                                'bg-red-50': selisih,
                                'bg-indigo-50 h-[100px]': activeRowId === {{ $item->id }}
                            }"
                            class="text-sm transition-colors hover:bg-indigo-50" wire:key="opname-row-{{ $item->id }}">

                            <td class="px-4 py-2 text-left">{{ $item->stoks->id }}</td>
                            <td class="px-4 py-2 text-left">{{ $item->stoks->batch }}</td>
                            <td class="px-4 py-2 text-left">{{ $item->barang->nama }}</td>
                            <td class="px-4 py-2 text-left">{{ $item->barang->satuan->nama }}</td>
                            <td class="px-4 py-2 text-left"> {{ formatRupiah($item->harga_satuan, withDecimals: false) }}</td>
                            <td class="px-4 py-2 text-left">{{ $item->stok_sistem_opname }}</td>
                            <td class="px-4 py-2 text-left">
                                <div class="flex items-center justify-start gap-1">
                                    <template x-if="stokFisik != {{ $item->stok_fisik }}">
                                        <x-ts:icon name="tabler.corner-down-left" class="h-5 w-auto text-indigo-500" />
                                    </template>
                                    <input x-ref="input_fisik_{{ $item->id }}" x-model.number="stokFisik" @keydown.enter.prevent="save()" type="number"
                                        :class="{ 'h-[100px]': activeRowId === {{ $item->id }} }" class="h-8 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="Real" required />
                                </div>
                            </td>
                            <td class="px-4 py-2 text-left">
                                <div class="flex items-center justify-start gap-1">
                                    <template x-if="ket != '{{ addslashes($item->ket) }}'">
                                        <x-ts:icon name="tabler.corner-down-left" class="h-5 w-auto text-indigo-500" />
                                    </template>
                                    <textarea x-model="ket" @click.stop @keydown.enter.prevent="save()" :class="{ 'h-[100px]': activeRowId === {{ $item->id }} }"
                                        class="h-8 w-full rounded-lg border border-gray-100 px-3 py-2 text-sm focus:outline-none" placeholder="Keterangan"></textarea>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right" :class="selisih > 0 ? 'text-green-600' : (selisih < 0 ? 'text-red-600' : 'text-gray-600')">
                                <span x-text="textSelisih"></span>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

@script
    <script>
        Alpine.data('opnameData', (soDetId, stok_sistem, stok_fisik, ket) => ({
            // activeRowId: null,
            soDetId: soDetId,
            stokSistem: Number(stok_sistem),
            stokFisik: Number(stok_fisik) == 0 ? Number(stok_sistem) : Number(stok_fisik),
            // stok_fisik: Number(stok_fisik);
            ket: ket,

            get selisih() {
                const fisik = Number(this.stokFisik) || 0;
                const sistem = Number(this.stokSistem) || 0;
                return fisik - sistem;
            },

            textSelisih() {
                return this.selisih === 0 ? '0' : this.selisih > 0 ? '+' + this.selisih : this.selisih;
            },

            save() {
                // Kirim ke Livewire
                $wire.call('saveRow', soDetId, this.stokFisik, this.textSelisih(), this.ket)
                    .then(() => {
                        // Feedback visual halus
                        this.$el.classList.add('bg-green-50');
                        setTimeout(() => this.$el.classList.remove('bg-green-50'), 400);
                    })
                    .catch(() => {
                        this.$el.classList.add('bg-red-50');
                        setTimeout(() => this.$el.classList.remove('bg-red-50'), 600);
                    });
            }
        }));
    </script>
@endscript
