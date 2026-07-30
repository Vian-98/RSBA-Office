<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-2" autocomplete="off">
        <div class="rounded-md border border-gray-200 p-2">
            <span class="text-lg font-bold text-indigo-500">{{ $assetBarang->barang->nama }}</span>
            <div class="flex flex-row gap-3 text-sm text-gray-400">
                <span>{{ $assetBarang->barang->kategori->nama }}, </span>

                Di : {{ $assetBarang->ruangan->nama }}
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <x-ts:select.styled wire:model.live.debounce='form.main' :request="route('api.asset.main_item', ['in' => $assetBarang->ruangan_id])" select="label:label|value:value" placeholder="Pilih Asset Utama" hint="Kosongkan jika ini adalah asset utama." />


            <x-ts:date wire:model.defer='form.tgl_catat' placeholder="Tgl Pencatatan" />

            <x-ts:select.styled wire:model.defer='form.status' :options="$statusOptions" select="label:label|value:value" placeholder="Kondisi Saat Dicatat" />

            <x-ts:input wire:model.defer='form.keterangan' placeholder="Keterangan" />

        </div>
        <div class="mt-4 flex justify-end gap-2">
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>

    </form>
</div>
