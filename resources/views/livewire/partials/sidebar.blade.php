<div x-show="isOpen()" class="fixed inset-0 z-50 flex h-screen bg-slate-900 bg-opacity-30 md:static md:bg-transparent">
    
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

    <div @click.away="handleAway()" @keyup.escape.window="handleAway()" 
         :class="isCollapsed && isAboveBreakpoint ? 'w-20' : 'w-72'"
         class="relative scrollbar-hidden flex flex-col justify-between h-full bg-white shadow-xl border-r border-slate-100 transition-all duration-300 ease-in-out overflow-x-hidden">
        
        <div id="sidebar-scroll-container" class="flex flex-col gap-6 overflow-y-auto overflow-x-hidden scrollbar-hidden py-6">
            {{-- logo --}}
            <div class="flex items-center px-6 transition-all duration-300"
                 :class="isCollapsed && isAboveBreakpoint ? 'justify-center' : 'justify-start gap-3'">
                <div class="transition-transform duration-300 shrink-0" :class="isCollapsed && isAboveBreakpoint ? 'scale-110' : ''">
                    <x-logo class="h-10 w-auto" />
                </div>
                <div x-show="!(isCollapsed && isAboveBreakpoint)" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-x-2" x-transition:enter-end="opacity-100 transform translate-x-0" class="flex flex-col">
                    <span class="text-sm font-bold text-gray-800 tracking-wider whitespace-nowrap">RS Bintang Amin</span>
                    <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-widest leading-none mt-0.5">Office Portal</span>
                </div>
            </div>

            {{-- search --}}
            <div class="px-3">
                <div class="relative flex items-center rounded-full bg-slate-100 hover:bg-slate-200/60 transition-all duration-200"
                     :class="isCollapsed && isAboveBreakpoint ? 'justify-center p-2.5 mx-1' : 'px-3 py-2'">
                    <x-ts:icon name="tabler.search" class="h-5 w-5 text-gray-400 shrink-0 cursor-pointer"
                               @click="if(isCollapsed && isAboveBreakpoint) toggleCollapse()" />
                    <input x-show="!(isCollapsed && isAboveBreakpoint)" 
                           wire:model.live.debounce.300ms="searchMenu" 
                           placeholder="Cari menu..."
                           class="ml-2 w-full bg-transparent text-xs text-gray-700 outline-none border-none p-0 focus:ring-0 focus:outline-none" />
                </div>
            </div>

            {{-- menu list --}}
            <div class="px-3">
                <x-menus :menus="$menus" class="flex" />
            </div>
        </div>

        {{-- Profile Footer --}}
        @if ($authUser && $authKaryawan)
            <div class="border-t border-slate-100 p-4 bg-slate-50/50">
                <div class="flex items-center transition-all duration-300"
                     :class="isCollapsed && isAboveBreakpoint ? 'justify-center' : 'justify-between gap-3'">
                    
                    {{-- Avatar & Info --}}
                    <div class="flex items-center gap-3 min-w-0" :class="isCollapsed && isAboveBreakpoint ? 'justify-center' : ''">
                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-indigo-50 flex items-center justify-center font-bold text-indigo-600 shadow-3xs"
                             :class="isCollapsed && isAboveBreakpoint ? 'cursor-pointer hover:ring-2 hover:ring-indigo-500/20' : ''"
                             @click="if(isCollapsed && isAboveBreakpoint) toggleCollapse()">
                            @if ($authKaryawan->foto)
                                <img src="{{ route('api.users.avatar', ['userId' => $authUser->id]) }}" class="h-full w-full object-cover" alt="Avatar" />
                            @else
                                {{ $authInitials }}
                            @endif
                        </div>
                        
                        <div x-show="!(isCollapsed && isAboveBreakpoint)" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 transform -translate-x-2" 
                             x-transition:enter-end="opacity-100 transform translate-x-0"
                             class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-gray-800 truncate whitespace-nowrap">{{ $authKaryawan->nama }}</span>
                            <span class="text-[10px] text-gray-400 truncate whitespace-nowrap mt-0.5">{{ $authUser->email }}</span>
                        </div>
                    </div>

                    {{-- Logout Icon --}}
                    <button x-show="!(isCollapsed && isAboveBreakpoint)" 
                            wire:click="logout" 
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-rose-50 hover:text-rose-600 transition-colors shrink-0" 
                            title="Log out">
                        <x-ts:icon name="tabler.logout" class="h-5 w-5" />
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>


@push('script')
    <script>
        function sidebar() {
            const breakpoint = 768
            return {
                open: {
                    sidebar: true,
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
                        return !this.isCollapsed
                    }
                    return this.open.navbar
                },

                handleOpen() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = true
                    } else {
                        this.open.navbar = true
                    }
                },

                toggle() {
                    if (this.isAboveBreakpoint) {
                        this.isCollapsed = !this.isCollapsed
                        localStorage.setItem('sidebar-collapsed', this.isCollapsed)
                    } else {
                        this.open.navbar = !this.open.navbar
                    }
                },

                toggleCollapse() {
                    this.isCollapsed = !this.isCollapsed
                    localStorage.setItem('sidebar-collapsed', this.isCollapsed)
                },

                handleClose() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = false
                    } else {
                        this.open.navbar = false
                    }
                },

                handleAway() {
                    if (!this.isAboveBreakpoint) {
                        this.open.navbar = false
                    }
                },

            }
        }
    </script>
    <script>
        function initSidebarScroll() {
            const container = document.getElementById('sidebar-scroll-container');
            if (container) {
                const savedScroll = sessionStorage.getItem('sidebar-scroll-top');
                if (savedScroll) {
                    container.scrollTop = parseInt(savedScroll, 10);
                }
                container.addEventListener('scroll', () => {
                    sessionStorage.setItem('sidebar-scroll-top', container.scrollTop);
                });
            }
        }
        document.addEventListener('DOMContentLoaded', initSidebarScroll);
        document.addEventListener('livewire:navigated', initSidebarScroll);
    </script>
@endpush
