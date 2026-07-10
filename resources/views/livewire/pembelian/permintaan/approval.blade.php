<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-4 rounded-md border border-gray-200 px-4 py-2">
        <span class="italic">
            Permintaan
            <span class="text-lg font-semibold text-indigo-500">#{{ $PembelianReq->id }}</span>
        </span>

        <div class="space-y-1">
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">User Pengaju</span>
                <span class="ml-2">: {{ $PembelianReq->user_request }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                <span class="ml-2">: {{ $PembelianReq->note }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">Lampiran</span>
                <span role="button" x-on:click="$dispatch('open-modal',{id:'modal-lampiran-permintaan-beli'})" class="ml-2 flex flex-row items-center gap-1"> :
                    <x-ts:icon name="tabler.paperclip" class="h-4 w-auto" />
                    <span class="text-indigo-500">
                        {{ count($PembelianReq->lampirans ?? []) }}</span>
                    Lampiran
                </span>

            </div>
        </div>

    </div>



    <form x-data="{ status: @entangle('status') }" wire:submit.prevent='submit' class="flex flex-col gap-4 rounded-md border border-indigo-200 px-4 py-2">
        <div>
            <span class="italic text-indigo-500">Persetujuan</span>
            <div class="flex w-full flex-col gap-2">
                <div class="flex w-1/2 justify-between">
                    @foreach ($optionsApproval as $item)
                        <div @click="status = '{{ $item['value'] }}'"
                            :class="status === '{{ $item['value'] }}'
                                ?
                                'bg-{{ $item['color'] }}-200' :
                                ''"
                            class="rounded-md p-1">

                            <x-ts:radio sm wire:model.defer='status' id="{{ $item['value'] }}" value="{{ $item['value'] }}" label="{{ $item['label'] }}" color="{{ $item['color'] }}" />
                        </div>
                    @endforeach
                </div>

                <div class="w-full" x-show="status === 'rejected'">
                    <x-ts:textarea wire:model.defer='keterangan' placeholder="Keterangan / Alasan Ditolak" resize-auto class="h-12" />
                </div>
            </div>
        </div>

        {{-- Items Untuk Disetujui --}}
        <div class="flex flex-col gap-2 rounded-md">
            <span class="text-sm italic text-indigo-400">Items Diajukan</span>
            {{-- @dd($pembelian_request_details) --}}
            <table>
                <thead>
                    <tr class="bg-gray-100 text-sm font-semibold text-gray-700">
                        <td class="px-4 py-2 text-left">Barang</td>
                        <td class="px-4 py-2 text-left">Kategori</td>
                        <td class="px-4 py-2 text-left">Consumable</td>
                        <td class="px-4 py-2 text-left">Satuan</td>
                        <td class="px-4 py-2 text-left">Jumlah Permintaan</td>
                        <td class="px-4 py-2 text-left">Harga (Estimasi)</td>
                        <td class="px-4 py-2 text-left">Disetujui</td>
                        <td class="px-4 py-2 text-right">Keterangan</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pembelian_request_details as $item)
                        <tr class="text-sm even:bg-gray-50 hover:bg-indigo-50">
                            <td class="px-4 text-left">{{ $item->barang->nama }}</td>
                            <td class="px-4 text-left">{{ $item->barang->kategori->nama }}</td>
                            <td class="px-4 text-left">{{ $item->barang->bhp ? 'Ya' : 'Bukan' }}</td>
                            <td class="px-4 text-left">{{ $item->barang->satuan->nama }}</td>
                            <td class="px-4 text-left">{{ $item->jml_req }}</td>
                            <td class="px-4 text-left">{{ $item->harga_est }}</td>
                            <td class="px-4 text-left">
                                <input type="number" min='0' wire:model="approvedItems.{{ $item->id }}.jml_disetujui" x-bind:disabled="status === 'rejected'"
                                    x-bind:value="(status === 'rejected') ? 0: ''" class="h-6 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="Jumlah" />
                            </td>
                            <td class="px-4">
                                <textarea type="text" wire:model="approvedItems.{{ $item->id }}.keterangan" class="h-10 max-w-24 rounded-lg border border-gray-100 text-sm" placeholder="Ket.."></textarea>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Simpan Actions --}}
        <div class="ml-auto flex flex-row justify-end gap-2">
            <div class="relative" x-data="{ passwordPopUp: false }">
                <x-ts:button sm icon="tabler.checks" x-on:click="passwordPopUp = true" @keyup.enter.window="passwordPopUp = true">Simpan</x-ts:button>

                <div class="absolute right-0 z-50 mt-1 w-96 rounded-lg border bg-white px-6 py-4 shadow-lg" x-show="passwordPopUp" x-transition x-trap.noscroll="passwordPopUp"
                    x-on:click.away="passwordPopUp = false" x-on:keydown.escape.window="passwordPopUp = false">

                    <div class="flex flex-col gap-3">
                        <h3 class="text-sm font-medium text-gray-700">Password Tanda Tangan</h3>

                        <div class="w-full">
                            <x-ts:password wire:model.defer='password' placeholder="Your Certificate Password" class="w-full" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex justify-end gap-2">
                        <x-ts:button outline sm icon="tabler.file-isr" x-on:click="passwordPopUp = false">
                            Batal
                        </x-ts:button>
                        <x-ts:button type="submit" sm color="green" icon="tabler.checks" loading="submit">
                            Konfirmasi
                        </x-ts:button>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Simpan Actions --}}

    </form>


    {{-- Modal lampiran --}}
    <x-filament::modal id="modal-lampiran-permintaan-beli" width="max-w-3xl">
        <x-slot name="heading">
            <span class="text-lg font-semibold">Lampiran Permintaan</span>
        </x-slot>

        <livewire:Pembelian.Permintaan.Lampiran :lampiranRequest="$this->getPembelianRequest()->lampirans" :key="'lampiran-beli' . $this->getPembelianRequest()->id" />
    </x-filament::modal>

</div>
