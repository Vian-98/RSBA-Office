@php
    $authUser = auth()->user();
    $authKaryawan = $authUser?->karyawan;
    $authInitials = '';
    if ($authKaryawan && $authKaryawan->nama) {
        $words = preg_split('/\s+/', trim($authKaryawan->nama));
        for ($i = 0; $i < min(2, count($words)); $i++) {
            $authInitials .= strtoupper(substr($words[$i], 0, 1));
        }
    }
@endphp

<div x-show="isOpen()" class="fixed inset-0 z-50 flex h-screen bg-white bg-opacity-75 md:static md:bg-transparent">
    <div @click.away="handleAway()" @keyup.escape.window="handleAway()"
        class="scrollbar-hidden flex h-full w-72 flex-col justify-between border-r border-slate-100 bg-white shadow-xl transition-all duration-300 ease-in-out">

        <div id="sidebar-scroll-container" class="scrollbar-hidden flex min-h-0 flex-1 flex-col gap-5 overflow-y-auto py-6">
            {{-- logo & Title --}}
            <div class="flex items-center gap-3 px-6">
                <div class="shrink-0">
                    <x-logo class="h-10 w-auto" />
                </div>
                <div class="flex flex-col">
                    <span class="whitespace-nowrap text-sm font-bold uppercase tracking-wider text-slate-800">{{ $rs->nama }}</span>
                    <span class="mt-0.5 text-[10px] uppercase leading-none tracking-widest text-slate-400">{{ config('app.name') }}</span>
                </div>
            </div>

            {{-- search --}}
            <div class="px-4">
                <div class="relative flex items-center rounded-full bg-slate-100 px-3 py-2 transition-all duration-200 hover:bg-slate-200/60">
                    <x-tabler-search class="h-4 w-4 shrink-0 cursor-pointer text-slate-400" />
                    <input wire:model.live.debounce.300ms="searchMenu" placeholder="Cari menu..."
                        class="ml-2 w-full border-none bg-transparent p-0 text-xs text-slate-700 outline-none focus:outline-none focus:ring-0" />
                </div>
            </div>

            {{-- menu list --}}
            <div class="px-3">
                <x-menus :menus="$menus" class="flex" />
            </div>
        </div>

        {{-- Profile Footer --}}
        @if ($authUser && $authKaryawan)
            <div class="border-t border-slate-100 bg-slate-50/50 p-4">
                <div class="flex items-center justify-between gap-3">
                    {{-- Avatar & Info --}}
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="shadow-3xs flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-indigo-50 text-xs font-bold text-indigo-600">
                            @if ($authKaryawan->foto)
                                <img src="{{ route('api.users.avatar', ['userId' => $authUser->id]) }}" class="h-full w-full object-cover" alt="Avatar" />
                            @else
                                {{ $authInitials }}
                            @endif
                        </div>

                        <div class="flex min-w-0 flex-col">
                            <span class="truncate whitespace-nowrap text-xs font-bold text-slate-800">{{ $authKaryawan->nama }}</span>
                            <span class="mt-0.5 truncate whitespace-nowrap text-[10px] text-slate-400">{{ $authUser->email }}</span>
                        </div>
                    </div>

                    {{-- Logout Button --}}
                    <button wire:click="logout" class="shrink-0 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600" title="Log out">
                        <x-tabler-logout class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>

@push('script')
    <script>
        function sidebar() {
            const breakpoint = 120
            return {
                open: {
                    sidebar: false,
                    navbar: false,
                },
                isCollapsed: localStorage.getItem('sidebar-collapsed') === 'true',

                isAboveBreakpoint: window.innerWidth >= breakpoint,

                handleResize() {
                    this.isAboveBreakpoint = window.innerWidth >= breakpoint
                },

                isOpen() {
                    if (this.isAboveBreakpoint) {
                        return this.open.sidebar
                    }
                    return this.open.navbar
                },

                isSidebarExpanded() {
                    if (this.isAboveBreakpoint) {
                        return this.open.sidebar
                    }
                    return this.open.navbar
                },

                handleOpen() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = true
                    } else {
                        this.open.navbar = true
                    }
                    this.scrollToActiveMenu()
                },

                toggle() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = !this.open.sidebar
                    } else {
                        this.open.navbar = !this.open.navbar
                    }
                    if (this.isOpen()) {
                        this.scrollToActiveMenu()
                    }
                },

                handleClose() {
                    this.open.sidebar = false
                    this.open.navbar = false
                },

                handleAway() {
                    if (!this.isAboveBreakpoint) {
                        this.open.navbar = false
                    }
                    this.open.sidebar = false
                },

                scrollToActiveMenu() {
                    setTimeout(() => {
                        const container = document.getElementById('sidebar-scroll-container')
                        if (!container) return
                        const activeItem = container.querySelector('.bg-primary-500, .active, [class*="bg-primary"]')
                        if (activeItem) {
                            activeItem.scrollIntoView({
                                block: 'center',
                                behavior: 'smooth'
                            })
                        }
                    }, 120)
                },


                init() {
                    this.scrollToActiveMenu()
                },
            }
        }
    </script>
@endpush
