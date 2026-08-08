<div x-show="isOpen()" class="fixed inset-0 z-50 flex h-screen bg-white bg-opacity-75 xl:static">
    <div @click.away="handleAway()" @keyup.escape.window="handleAway()" class="scrollbar-hidden w-72 overflow-y-auto bg-white shadow-xl">

        {{-- logo --}}
        <div class="mt-6 flex h-10 items-center justify-center">
            {{-- <x-logo class="h-16" /> --}}
        </div>

        {{-- menu --}}
        <div class="my-6 flex flex-col gap-4 px-3">

            <div x-data="{ open: false }" class="relative">
                <input x-show="open" x-ref="searchInput" wire:model.live.debounce.300ms="searchMenu" placeholder="Cari Menu"
                    class="h-8 w-full rounded-lg border-gray-200 px-3 transition-all duration-300" title="Cari Menu" x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-75" />

                <button x-on:click="open = !open; $nextTick(() => $refs.searchInput.focus())" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <x-tabler-search class="h-5 w-5" />
                </button>
            </div>

            <x-menus :menus="$menus" class="flex" />
        </div>

    </div>
</div>


@push('script')
    <script>
        function sidebar() {
            // const breakpoint = 1280
            const breakpoint = 120
            return {
                open: {
                    sidebar: false,
                    navbar: false,
                },

                isAboveBreakpoint: window.innerWidth > breakpoint,

                handleResize() {
                    this.isAboveBreakpoint = window.innerWidth > breakpoint
                },

                isOpen() {
                    if (this.isAboveBreakpoint) {
                        return this.open.sidebar
                    }
                    return this.open.navbar
                },

                handleOpen() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = true
                    }
                    this.open.navbar = true
                },


                handleClose() {
                    if (this.isAboveBreakpoint) {
                        this.open.sidebar = false
                    }
                    this.open.navbar = false
                },

                handleAway() {
                    if (!this.isAboveBreakpoint) {
                        this.open.navbar = false
                    }
                    this.open.sidebar = false
                },

            }
        }
    </script>
@endpush
