<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ isset($title) ? config('app.name') . " | $title" : config('app.name') }}</title>
        
        <!-- Premium Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        
        <style>
            [x-cloak] {
                display: none !important;
            }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif !important;
            }
        </style>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.Alpine && !window.Alpine.store('theme')) {
                    window.Alpine.store('theme', localStorage.getItem('theme') || 'light');
                }
            });

            // sidebar() must be defined before Alpine evaluates x-data="sidebar()" on <body>
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

                    toggleCollapse() {
                        this.isCollapsed = !this.isCollapsed
                        localStorage.setItem('sidebar-collapsed', this.isCollapsed)
                    },

                    handleClose() {
                        this.open.sidebar = false
                        this.open.navbar = false
                    },

                    handleAway() {
                        if (!this.isAboveBreakpoint) {
                            this.open.navbar = false
                        }
                    },

                    scrollToActiveMenu() {
                        setTimeout(() => {
                            const container = document.getElementById('sidebar-scroll-container')
                            if (!container) return
                            const activeItem = container.querySelector('.bg-primary-500, .active, [class*="bg-primary"]')
                            if (activeItem) {
                                activeItem.scrollIntoView({ block: 'center', behavior: 'smooth' })
                            }
                        }, 120)
                    },

                    initForceListeners() {
                        window.addEventListener('force-sidebar-collapse', () => {
                            this.isCollapsed = true
                        })
                        window.addEventListener('force-sidebar-expand', () => {
                            this.isCollapsed = false
                        })
                        document.addEventListener('livewire:navigated', () => {
                            this.handleClose()
                            this.scrollToActiveMenu()
                        })
                    },

                    init() {
                        this.initForceListeners()
                        this.scrollToActiveMenu()
                    },
                }
            }
        </script>
        <tallstackui:script />
        @filamentStyles

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- inject style laravel --}}
        @stack('style')
    </head>

    <body x-data="sidebar()" class="flex h-screen bg-gray-100 antialiased" @resize.window="handleResize()">
        {{-- toast , dialog --}}
        <x-ts:toast />
        <x-ts:dialog />

        {{-- loading pages --}}
        <x-loading-page />


        @auth
            <div class="flex h-full w-full flex-row overflow-hidden">
                {{-- SIDEBAR --}}
                <livewire:Partials.Sidebar key="sidebar" />

                <!-- Main content -->
                <div id="main-content" class="max-h-screen flex-1 flex flex-col overflow-hidden transition-all duration-300">

                    {{-- NAVBAR --}}
                    <div class="h-16 rounded-md bg-white shadow-md shrink-0 mx-6 mt-6">
                        <livewire:Partials.Navbar :title="isset($title) ? $title : config('app.name')" key="navbar" />
                    </div>

                    {{-- CONTENT --}}
                    <main id="main" class="flex-1 overflow-y-auto scrollbar-hidden px-6 py-4">
                        {{ $slot }}
                    </main>

                </div>
            </div>
        @endauth

        @filamentScripts

        {{-- Inject Script Laravel --}}
        @stack('script')
    </body>

</html>
