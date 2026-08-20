<div>
    <div class="flex flex-col gap-1" x-data="{
        searchItem: @entangle('searchPermissions'),
        search(term) {
            @this.call('SearchPermission', term);
    
        }
    }">
        <div class="flex items-center justify-between gap-2">
            <div class="w-1/2">
                <x-ts:tag :limit='1' x-on:add="search($event.detail.tag)" x-on:remove="search('')" x-on:erase="search('')" placeholder="Filter" />
            </div>
            <div class="flex items-center gap-2">
                <x-ts:button type="button" sm outline color="indigo" wire:click="selectAll">Pilih Semua</x-ts:button>
                <x-ts:button type="button" sm outline color="secondary" wire:click="deselectAll">Kosongkan</x-ts:button>
            </div>
        </div>

        <form wire:submit.prevent='submit'>
            <div class="flex flex-col gap-2">

                {{-- @dd($permissions) --}}
                @foreach ($permissions as $item)
                    <x-ts:checkbox wire:model.defer='permission' value="{{ $item->name }}" label="{{ $item->name }}" />
                @endforeach
            </div>

            <div class="ml-auto flex justify-end gap-2">
                <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'set-permission'})">Batal</x-ts:button>
                <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
            </div>
        </form>
    </div>

</div>
