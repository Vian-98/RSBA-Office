<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Penyimpanan Barang" />
            <x-ts:input wire:model.defer='deskripsi' placeholder="Deskripsi" />
        </div>

        <div class="mt-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold">Daftar Lemari / Sub-Lokasi (Opsional)</span>
                <x-ts:button sm color="primary" variant="light" wire:click="addLemari" type="button" icon="tabler.plus">
                    Tambah Lemari
                </x-ts:button>
            </div>

            @foreach ($lemaris as $index => $lemari)
                <div class="flex gap-2 items-center">
                    <x-ts:input wire:model.defer="lemaris.{{ $index }}.nama_lemari" placeholder="Nama Lemari (Misal: Lemari A-1)" class="flex-1" />
                    
                    <button type="button" wire:click="removeLemari({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                        <x-ts:icon name="tabler.trash" class="w-5 h-5" />
                    </button>
                </div>
            @endforeach
        </div>

        {{-- action --}}
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button x-on:click="$dispatch('close-modal',{id:'modal-new-penyimpanan'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>
</div>
