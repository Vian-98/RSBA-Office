<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-ts:select.styled wire:model.lazy='resign' placeholder="Resign Status" :options="$resign_options" select="label:label|value:id" />

            <x-ts:input wire:model.lazy='keterangan' placeholder="Keterangan" />

            <x-ts:date wire:model.lazy='tgl_resign' placeholder="Tgl Resign" />
        </div>
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$tsui.close.modal('modal-resign-karyawan')">
                Batal
            </x-ts:button>
            <x-ts:button type="submit" wire:click='submit' icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>
    </form>
</div>
