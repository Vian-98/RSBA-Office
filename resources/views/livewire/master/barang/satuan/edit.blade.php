<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div>
            <x-ts:input wire:model.defer='nama' placheholder="Nama Satuan" />
            <x-ts:input wire:model.defer='Deskripsi' placeholder="Deskripsi" />

        </div>

        <div class="flex justify-end gap-2">
            <x-ts:button outline color="black" x-on:click="$dispatch('close-modal',{id:'modal-edit-satuan'})">
                Tutup
            </x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>

    </form>
</div>
