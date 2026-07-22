<div class="w-full">
    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="-mb-px flex space-x-6 min-w-max" aria-label="Tabs">
                <button wire:click="$set('tab', 'password')" @click="tab = 'password'"
                    :class="tab === 'password' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                    <x-ts:icon name="tabler.key" class="h-4 w-4" />
                    Ganti Password
                </button>

                @can('tanda-tangan-digital')
                    <button wire:click="$set('tab', 'signature')" @click="tab = 'signature'"
                        :class="tab === 'signature' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                        <x-ts:icon name="tabler.signature" class="h-4 w-4" />
                        Certificate Tanda Tangan
                    </button>
                @endcan

                <button wire:click="$set('tab', 'role-permission')" @click="tab = 'role-permission'"
                    :class="tab === 'role-permission' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                    <x-ts:icon name="tabler.lock-cog" class="h-4 w-4" />
                    Role & Permission
                </button>

                <button wire:click="$set('tab', 'email-aktivasi')" @click="tab = 'email-aktivasi'"
                    :class="tab === 'email-aktivasi' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                    <x-ts:icon name="tabler.mail" class="h-4 w-4" />
                    Email Aktivasi
                </button>

                <button wire:click="$set('tab', 'login-session')" @click="tab = 'login-session'"
                    :class="tab === 'login-session' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                    <x-ts:icon name="tabler.devices" class="h-4 w-4" />
                    Login Session
                </button>
            </nav>
        </div>

        <div class="mt-4">
            @if($tab === 'password' || $tab === 'Password')
                <livewire:Profile.GantiPassword />
            @elseif($tab === 'signature' || $tab === 'Certificate Tanda Tangan')
                @can('tanda-tangan-digital')
                    <livewire:Profile.SignatureCerts.Index />
                @endcan
            @elseif($tab === 'role-permission' || $tab === 'Role Permission')
                <livewire:Profile.RolePermission />
            @elseif($tab === 'email-aktivasi' || $tab === 'Email Aktivasi')
                <livewire:Profile.EmailAktivasi />
            @elseif($tab === 'login-session' || $tab === 'Login Session')
                <livewire:Profile.LoginSession />
            @endif
        </div>
    </div>
</div>
