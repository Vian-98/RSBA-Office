<div class="flex flex-col">
    {{ $this->table }}

    {{-- modal --}}
    <x-filament::modal id="set-user-role" :close-by-clicking-away="false">
        <x-slot name="heading">
            Set Role <span class="text-primary-500">{{ $user?->karyawan->nama }}</span>
        </x-slot>

        <livewire:User.SetRole @updated-role-user="$refresh" :id="$user?->id" :key="Str::random()" />
    </x-filament::modal>


    <x-filament::modal id="edit-user-permission" width="6xl" :close-by-clicking-away="false">
        <x-slot name="heading">
            User Permission <span class="text-primary-500">{{ $user?->karyawan->nama }}</span>
        </x-slot>

        <livewire:User.PermissionEdit @updated-permission-user="$refresh" :id="$user?->id" :key="Str::random()" />
    </x-filament::modal>
</div>
