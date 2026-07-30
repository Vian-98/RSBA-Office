<div>
    <div class="flex flex-col gap-2">
        <span> Berikan Role :</span>
        <form wire:submit.prevent='submit' class="flex flex-col gap-2">
            @foreach ($roles as $item)
                <div class="hover:bg-primary-100 flex justify-between rounded-lg p-1">
                    <x-ts:radio wire:model.defer="role" id="{{ $item->name }}" value="{{ $item->name }}" label="{{ $item->name }}" />

                    <x-ts:icon name="tabler.edit" class="h-5 w-5 text-indigo-400" role="button" wire:click="editPermission({{ $item->id }})" />
                </div>
            @endforeach

            <div class="ml-auto mt-4 flex justify-end gap-2">
                <x-ts:button sm outline x-on:click="$dispatch('close-modal',{id:'set-user-role'})">Batal</x-ts:button>
                <x-ts:button sm type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
            </div>
        </form>


        <x-filament::modal id="set-permission">
            <x-slot name="heading">
                Edit Permission Role : <span class="text-primary-500">{{ $roleIdSelected?->name }}</span>
            </x-slot>

            <livewire:Settings.Role.SetPermission :id="$roleIdSelected?->id" :key="Str::random()" />
        </x-filament::modal>
    </div>
</div>
