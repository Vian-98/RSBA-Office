<div>
    <form wire:submit.prevent='update' class="flex flex-col gap-4" autocomplete="off">

        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder='Nama' />

            <x-ts:input wire:model.defer='singkatan' placeholder='Singkatan' />
        </div>
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-edit-spesialisasi'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="update" icon="tabler.checks">Simpan</x-ts:button>

        </div>

    </form>
</div>
