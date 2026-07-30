<div>
    <form wire:submit.prevent='update' class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Nama Cuti" />
            <x-ts:input wire:model.defer='lama' placeholder="Lama Cuti" />
            <x-ts:input wire:model.defer='periode' placeholder="Periode Reset" />
        </div>
        <div>
            <x-ts:button sm outline type="submit">Update</x-ts:button>
        </div>
    </form>
</div>
