<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Nama Satuan" />
            <x-ts:input wire:model.defer='deskripsi' placeholder="Deskripsi" />

        </div>
        <div class="flex justify-end gap-2">
            <x-ts:button outline color="black" type="button" x-on:click="$dispatch('close-modal',{id:'modal-new-satuan'})" class="btn-bs-secondary">Batal</x-ts:button>
            <x-ts:button type="submit" class="btn-bs-primary">Simpan</x-ts:button>
        </div>
    </form>
</div>
