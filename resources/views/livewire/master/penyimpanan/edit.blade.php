<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Penyimpanan Barang" />
            <x-ts:input wire:model.defer='deskripsi' placeholder="Deskripsi" />
        </div>

        {{-- action --}}
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline color="black" x-on:click="$dispatch('close-modal',{id:'modal-edit-penyimpanan'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>
</div>
