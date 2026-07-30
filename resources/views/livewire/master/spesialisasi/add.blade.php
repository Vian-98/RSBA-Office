<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Nama" />
            <x-ts:input wire:model.defer='singkatan' placeholder="Singkatan / Gelar" />
            <x-ts:select.styled wire:model.defer='kategori' :options="$kategoriOptions" select="label:label|value:id" />
        </div>
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-new-spesialis'})">
                Tutup
            </x-ts:button>

            <x-ts:button type="submit" loading="submit" icon="tabler.checks">
                Tutup
            </x-ts:button>
        </div>
    </form>

</div>
