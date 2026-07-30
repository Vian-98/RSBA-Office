<div x-data="{ panelActive: '' }" class="flex flex-col gap-2">
    <span class="text-sm italic text-gray-500">Sumber penerimaan barang dari :</span>
    <div class="flex flex-row gap-4">
        <x-ts:radio id="sumber_hibah" x-model="panelActive" x-on:click="togglePanel('hibah')" value="hibah" label="Hibah" />
        <x-ts:radio id="sumber_pembelian" x-model="panelActive" x-on:click="togglePanel('pembelian')" value="pembelian" label="Pembelian" />
        <x-ts:radio id="sumber_po" x-model="panelActive" x-on:click="togglePanel('preorder'); $nextTick(() =>{$refs.searchInput.focus()} )" value="preorder" label="Pre Order" />
    </div>
    <hr class="border-gray-300 p-2">


    <span x-show="!panelActive" x-cloak class="text-sm italic text-gray-300">Pilih Sumber Penerimaan</span>

    <div x-show="panelActive === 'hibah'">
        <livewire:Pembelian.Penerimaan.Hibah :key="'penerimaan-hibah-' . uniqid()" />
    </div>

    <div x-show="panelActive === 'pembelian'">
        <livewire:Pembelian.TransaksiBeliLangsung :key="Str::random()" @new-transaksi-langsung-created="$refresh" />
    </div>

    {{-- terima pre order --}}
    <div x-show="panelActive === 'preorder'" class="flex flex-col gap-3">
        {{-- Cari No PO --}}
        <div class="w-full lg:w-1/2">
            <livewire:Pembelian.Cari key="cari-pembelian" />
        </div>
    </div>
    {{-- end terima pre order  --}}
</div>
