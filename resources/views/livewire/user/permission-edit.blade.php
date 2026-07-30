<div class="flex flex-col gap-2" x-data="{ menu: @entangle('menu') }">

    <div class="border-gray flex flex-row gap-4 overflow-auto rounded-md border p-4">
        @foreach ($groups as $group)
            <x-ts:radio wire:model.live.debounce.500='group' id="{{ $group['label'] }}" value="{{ $group['value'] ?? null }}" label="{{ $group['value'] ? $group['label'] : 'Dashboard' }}" />
        @endforeach
    </div>


    <form wire:submit.prevent='submit' class="flex flex-col gap-2">

        <span wire:loading wire:target='group' class="animate-pulse italic text-indigo-500">
            loading...
        </span>

        <div wire:loading.remove wire:target='group' class="flex w-full flex-col gap-4">
            @forelse ($this->menus as $groupId => $groupMenus)
                <div class="w-full divide-y divide-indigo-200 overflow-hidden rounded-md border">
                    <div class="grid grid-cols-6 bg-indigo-100 text-sm font-semibold uppercase text-secondary-500">
                        <div class="col-span-4 px-2 py-1">{{ $this->groupMenu[$groupId] ?? 'Tanpa Group' }}</div>
                        <div class="px-2 py-1">Role</div>
                        <div class="px-2 py-1">User</div>
                    </div>


                    @foreach ($groupMenus as $menu)
                        @if ($menu->parent_id === $mainMenu->id)
                            <div class="bg-indigo-100/50 px-2 text-sm font-semibold text-indigo-500">{{ $menu->nama }}</div>
                            <!-- Each Permission -->
                            @foreach ($menu->permission ?? [] as $permission)
                                <div class="grid grid-cols-6 text-sm text-gray-800 hover:bg-indigo-50">
                                    <div class="col-span-4 px-2 py-1 italic text-gray-500">{{ $permission }}</div>
                                    <div class="px-2 py-1">
                                        <x-ts:checkbox sm color="cyan" wire:model.defer='rolePermission' value="{{ $permission }}" />
                                    </div>
                                    <div class="px-2 py-1">
                                        <x-ts:checkbox sm wire:model.defer='permission' value="{{ $permission }}" />
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @foreach ($menu?->submenus as $sub)
                            <div class="bg-sky-100/50 px-2 text-sm font-semibold text-indigo-500">
                                <span class="ms-8">{{ $sub->nama }}</span>
                            </div>
                            <!-- Each Permission -->
                            @foreach ($sub->permission ?? [] as $subPermission)
                                <div class="grid grid-cols-6 text-sm text-gray-800 hover:bg-indigo-50">
                                    <div class="col-span-4 px-2 py-1 italic text-gray-500">
                                        <span class="ms-8"> {{ $subPermission }}</span>
                                    </div>
                                    <div class="px-2 py-1">
                                        <x-ts:checkbox sm color="cyan" wire:model.defer='rolePermission' value="{{ $subPermission }}" />
                                    </div>
                                    <div class="px-2 py-1">
                                        <x-ts:checkbox sm wire:model='permission' value="{{ $subPermission }}" />
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach

                    {{-- group --}}
                </div>
            @empty
                <span class="text-sm italic">Tidak ada menu</span>
            @endforelse
        </div>


        <div class="ml-auto mt-4 flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'edit-user-permission'})">Batal</x-ts:button>
            <x-ts:button type="submit" loading="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
