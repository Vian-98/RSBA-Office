<div class="w-full">
    <div class="rounded-2xl bg-white p-3 sm:p-5 shadow-sm border border-slate-100" x-data="{ tab: @entangle('tab') }">
        <div class="overflow-x-auto scrollbar-hidden pb-1">
            <nav class="inline-flex gap-2 sm:gap-3 min-w-max p-0.5" aria-label="Tabs">
                <button wire:click="$set('tab', 'password')" @click="tab = 'password'"
                    :class="tab === 'password' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                    class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                    <x-ts:icon name="tabler.key" class="h-4 w-4 shrink-0" />
                    Ganti Password
                </button>

                @can('tanda-tangan-digital')
                    <button wire:click="$set('tab', 'signature')" @click="tab = 'signature'"
                        :class="tab === 'signature' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                        class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                        <x-ts:icon name="tabler.signature" class="h-4 w-4 shrink-0" />
                        Certificate Tanda Tangan
                    </button>
                @endcan

                <button wire:click="$set('tab', 'role-permission')" @click="tab = 'role-permission'"
                    :class="tab === 'role-permission' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                    class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                    <x-ts:icon name="tabler.lock-cog" class="h-4 w-4 shrink-0" />
                    Role & Permission
                </button>

                <button wire:click="$set('tab', 'email-aktivasi')" @click="tab = 'email-aktivasi'"
                    :class="tab === 'email-aktivasi' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                    class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                    <x-ts:icon name="tabler.mail" class="h-4 w-4 shrink-0" />
                    Email Aktivasi
                </button>

                <button wire:click="$set('tab', 'login-session')" @click="tab = 'login-session'"
                    :class="tab === 'login-session' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                    class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                    <x-ts:icon name="tabler.devices" class="h-4 w-4 shrink-0" />
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
