<div class="w-full">
    <x-ts:tab selected="Password" x-on:navigate="$wire.switchTab($event.detail.select)">
        <x-ts:tab.items tab="Password">
            <livewire:Profile.GantiPassword />
        </x-ts:tab.items>

        @can('tanda-tangan-digital')
            <x-ts:tab.items tab="Certificate Tanda Tangan">
                <livewire:Profile.SignatureCerts.Index />
            </x-ts:tab.items>
        @endcan

        <x-ts:tab.items tab="Role Permission">
            <livewire:Profile.RolePermission />
        </x-ts:tab.items>

        <x-ts:tab.items tab="Email Aktivasi">
            <livewire:Profile.EmailAktivasi />
        </x-ts:tab.items>

        <x-ts:tab.items tab="Login Session">
            <livewire:Profile.LoginSession />
        </x-ts:tab.items>
    </x-ts:tab>
</div>
