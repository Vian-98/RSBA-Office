<div>
    <nav class="text-primary-700 flex h-16 items-center px-6 text-xl">
        <div class="flex flex-row items-center gap-2">
            <a @click.prevent="toggle()" @keyup.enter="alert('Submitted!')" class="hover:text-danger-500" href="#">
                <div x-data="{ isHover: false }">
                    <x-tabler-menu-2 x-show="!isHover" @mouseover="isHover = true" />
                    <x-tabler-layout-sidebar-left-expand x-show="isHover && !isSidebarExpanded()" @mouseleave="isHover = false" />
                    <x-tabler-layout-sidebar-left-collapse x-show="isHover && isSidebarExpanded()" @mouseleave="isHover = false" />
                </div>
            </a>
            <a href="">
                <span class="text-primary-500 ml-4 hidden font-semibold uppercase lg:block">{{ $title }}</span>
            </a>
        </div>

        <div class="ml-auto flex">
            <div class="flex items-center">
                <div class="me-6 hidden space-x-4 lg:block">
                    <x-ts:dropdown position="bottom-end" width="lg">
                        <x-slot:action>
                            <x-ts:button.circle flat outline class="relative" x-on:click="show = !show">
                                <x-tabler-bell />
                                @if ($hasUnread)
                                    <span class="absolute right-0.5 top-1 block h-1.5 w-1.5 rounded-full bg-red-500 ring-2 ring-red-300"></span>
                                @endif
                            </x-ts:button.circle>
                        </x-slot:action>

                        <div class="w-full p-4 flex flex-col max-h-96">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3 shrink-0">
                                <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                    <x-tabler-bell class="size-4 text-indigo-500" />
                                    Notifikasi
                                </span>
                                <a href="{{ route('profile.notif') }}" class="text-xs font-semibold text-indigo-600 hover:underline">Lihat Semua</a>
                            </div>
                            <div class="flex-1 overflow-y-auto scrollbar-hidden">
                                <livewire:Profile.Notif :key="auth()->user()->id" />
                            </div>
                        </div>
                    </x-ts:dropdown>
                </div>
                <x-ts:dropdown>
                    <x-slot:action>
                        <div role="button" class="flex gap-3" x-on:click="show = !show">
                            <div class="flex flex-col">
                                <span class="text-lg font-semibold">{{ $nama }}</span>
                                <span class="text-xs text-gray-500/80">{{ $email }}</span>
                            </div>
                            <x-ts:avatar :image="$foto" :text="$textFoto" :color="$colorFoto" md borderless="{{ $hasFoto ? true : false }}" />
                        </div>
                    </x-slot:action>

                    <x-ts:dropdown.items text="Profile" icon="user" :href="route('profile.index')" />
                    <x-ts:dropdown.items text="Notifikasi" icon="bell" :href="route('profile.notif')" />
                    <x-ts:dropdown.items text="Settings" icon="cog" :href="route('profile.setting')" />
                    <x-ts:dropdown.items text="Logout" icon="arrow-left-on-rectangle" wire:click="logout" separator />
                </x-ts:dropdown>
            </div>
        </div>
