{{-- Toast Notification Component (Top-Right Toast & Auto-Close with Tabler Icons) --}}
@if (session()->has('success') || session()->has('warning') || session()->has('error'))
    <div class="fixed top-6 right-6 z-50 max-w-md w-full pointer-events-none space-y-3">
        @if (session()->has('success'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4500)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2 translate-x-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 translate-x-4 scale-95"
                class="pointer-events-auto flex items-center gap-3 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-emerald-200 text-slate-800 shadow-2xl shadow-emerald-500/10 ring-1 ring-emerald-500/20"
            >
                {{-- Tabler Success Icon --}}
                <div class="w-8 h-8 shrink-0 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-sm shadow-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                        <path d="M9 12l2 2l4 -4" />
                    </svg>
                </div>

                {{-- Message Content --}}
                <div class="flex-1 min-w-0">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Berhasil</h4>
                    <p class="text-xs font-semibold text-slate-700 leading-snug mt-0.5">
                        {{ session('success') }}
                    </p>
                </div>

                {{-- Tabler Close Button --}}
                <button
                    type="button"
                    @click="show = false"
                    class="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M18 6l-12 12" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session()->has('warning'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5500)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2 translate-x-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 translate-x-4 scale-95"
                class="pointer-events-auto flex items-center gap-3 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-amber-200 text-slate-800 shadow-2xl shadow-amber-500/10 ring-1 ring-amber-500/20"
            >
                {{-- Tabler Warning Icon --}}
                <div class="w-8 h-8 shrink-0 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-sm shadow-amber-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 9v4" />
                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                        <path d="M12 16h.01" />
                    </svg>
                </div>

                {{-- Message Content --}}
                <div class="flex-1 min-w-0">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Peringatan</h4>
                    <p class="text-xs font-semibold text-slate-700 leading-snug mt-0.5">
                        {{ session('warning') }}
                    </p>
                </div>

                {{-- Tabler Close Button --}}
                <button
                    type="button"
                    @click="show = false"
                    class="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M18 6l-12 12" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 6000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2 translate-x-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 translate-x-4 scale-95"
                class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-rose-200 text-slate-800 shadow-2xl shadow-rose-500/10 ring-1 ring-rose-500/20"
            >
                {{-- Tabler Error Icon --}}
                <div class="w-8 h-8 shrink-0 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-sm shadow-rose-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                    </svg>
                </div>

                {{-- Message Content --}}
                <div class="flex-1 min-w-0">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Terjadi Kesalahan</h4>
                    <p class="text-xs font-semibold text-slate-700 leading-snug mt-0.5">
                        {{ session('error') }}
                    </p>
                </div>

                {{-- Tabler Close Button --}}
                <button
                    type="button"
                    @click="show = false"
                    class="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M18 6l-12 12" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endif
