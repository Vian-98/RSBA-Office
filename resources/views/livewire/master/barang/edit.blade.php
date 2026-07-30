<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex w-full flex-col gap-2">

            <x-ts:input wire:model.defer='sku' placeholder="SKU" readonly />

            <x-ts:input wire:model.defer='nama' placeholder="Nama Barang" />

            <div class="flex items-center gap-1">
                <div class="w-full">
                    <x-ts:select.styled wire:model.defer='kategori' placeholder="Kategori" searchable :options="$kategoriOptions" select="label:nama|value:id" />
                </div>

                <x-ts:icon role="button" name="tabler.square-plus" class="h-10 w-8 rounded font-thin text-gray-300 hover:bg-neutral-200"
                    x-on:click="$dispatch('open-modal',{id:'modal-new-kategori'})" />
            </div>

            <div class="flex items-center gap-1">
                <div class="w-full">
                    <x-ts:select.styled wire:model.defer='satuan' placeholder="Satuan" searchable :options="$satuanOptions" select="label:nama|value:id" />
                </div>

                <x-ts:icon role="button" name="tabler.square-plus" class="h-10 w-8 rounded font-thin text-gray-300 hover:bg-neutral-200"
                    x-on:click="$dispatch('open-modal',{id:'modal-new-satuan'})" />
            </div>

            <x-ts:select.styled wire:model='tipe' placeholder="Tipe Barang" searchable :options="$tipeOptions" select="label:label|value:value" />

            <x-ts:number wire:model='min_stok' placeholder="Minimal Stok" type="number" />

            <div class="mt-3 flex-col">
                <x-ts:checkbox sm label="Consumable" wire:model.defer='bhp' />
                <span class="text-xs font-thin italic text-gray-400">Checklist jika barang BHP.</span>
            </div>
            
            <!-- Satuan Konversi -->
            <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-700">Satuan Konversi (Opsional)</h4>
                    <x-ts:button sm type="button" outline x-on:click="$wire.addKonversi()">Tambah Satuan</x-ts:button>
                </div>
                
                @if (count($konversiSatuans) > 0)
                    <div class="flex flex-col gap-2">
                        @foreach ($konversiSatuans as $index => $konversi)
                            <div class="flex items-center gap-2">
                                <div class="w-1/2">
                                    <x-ts:select.styled wire:model.defer="konversiSatuans.{{ $index }}.satuan_id" placeholder="Pilih Satuan" searchable :options="$satuanOptions" select="label:nama|value:id" />
                                </div>
                                <div class="w-1/3">
                                    <x-ts:number wire:model.defer="konversiSatuans.{{ $index }}.rasio" placeholder="Rasio" type="number" min="1" />
                                </div>
                                <div>
                                    <x-ts:button sm type="button" color="red" outline x-on:click="$wire.removeKonversi({{ $index }})">Hapus</x-ts:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-xs font-thin italic text-gray-400">Belum ada satuan konversi ditambahkan.</span>
                @endif
            </div>
        </div>

        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-edit-barang'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>


    {{-- modal kategori --}}
    <x-filament::modal id="modal-new-kategori" :close-by-clicking-away="false" :autofocus="false">
        <x-slot name="heading">
            Kategori Baru
        </x-slot>

        <livewire:Master.Barang.Kategori.Add :key="Str::random()" @new-kategori-created="$refresh" />
    </x-filament::modal>


    {{-- modal satuan --}}
    <x-filament::modal id="modal-new-satuan" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>
            Tambah Satuan
        </x-slot:heading>

        <livewire:Master.Barang.Satuan.Add :key="Str::random()" @satuan-created="$refresh" />
    </x-filament::modal>
</div>
