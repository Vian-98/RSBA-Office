<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        <div class="flex w-full flex-col gap-2">

            <x-ts:input wire:model.defer="nama" placeholder="Jenis Cuti" />

            <x-ts:input wire:model.defer="lama" placeholder="Lama Cuti" />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button sm loading="submit" type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
