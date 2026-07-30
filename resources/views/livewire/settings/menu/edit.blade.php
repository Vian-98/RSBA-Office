<div>
    <form wire:submit.prevent='update' class="flex w-full flex-col gap-2" autocomplete="off">

        <x-ts:input wire:model.defer='nama' placeholder="Nama" />

        <div>
            <x-ts:input wire:model.live.debounce.300='route' placeholder="Route (Route Name)" />
            @empty(!$route)
                @if ($route_avail)
                    <span class="text-xs text-primary-500">Route tersedia</span>
                @else
                    <span class="text-xs text-danger-500">Route tidak tersedia</span>
                @endif
            @endempty
        </div>

        <x-ts:input wire:model.defer='icon' hint="Icon libr : tabler.io" />

        <x-ts:select.styled wire:model.defer='parent' placeholder="Parent Menu" searchable :options="$this->parents" select="label:nama|value:id" />

        <x-ts:select.styled wire:model.defer='group' placeholder="Group Menu" searchable :options="$groups" select="label:label|value:value" />

        <div class="flex w-full flex-row items-stretch justify-items-center gap-2">
            <div class="w-10/12">
                <x-ts:select.styled wire:model.defer='permission' placeholder="Permission" searchable multiple :options="$this->permissionOptions" select="label:name|value:name" />
            </div>

            <div>
                <x-ts:button xs outline x-on:click="$dispatch('open-modal',{id:'new-permission'})">Tambah</x-ts:button>
            </div>
        </div>

        <div class="ml-auto flex justify-end">
            <x-ts:button type="submit" loading="update" icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>
    </form>

    <x-filament::modal id="new-permission">
        <x-slot name="header">
            Add New Permission
        </x-slot>

        <livewire:Settings.Permission.Add @new-permission-created="$parent.$refresh" :key="Str::random()" />
    </x-filament::modal>
</div>
