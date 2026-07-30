<div class="space-y-5">
    {{-- Top Header Banner --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-indigo-50/60 blur-xl"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">
                    <x-ts:icon name="tabler.stethoscope" class="h-3.5 w-3.5 text-indigo-600" />
                    Modul Remunerasi & Jasa Medis
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Jasa Medis (Jasmed)</h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                    Sistem pengelolaan klaim, verifikasi, dan distribusi remunerasi dokter & tenaga kesehatan RS Bintang Amin.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Tab Navigation Bar --}}
    <div class="overflow-x-auto scrollbar-hidden pb-1">
        <nav class="inline-flex gap-2.5 sm:gap-3 min-w-max p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60" aria-label="Jasmed Tabs">
            <button wire:click="navigateTo('dashboard')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ empty($content) || $content === 'dashboard' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.dashboard" class="h-4 w-4 shrink-0" />
                Dashboard
            </button>

            @can('jasmed-bpjs')
                <button wire:click="navigateTo('bpjs')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'bpjs' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.shield-check" class="h-4 w-4 shrink-0" />
                    BPJS Kesehatan
                </button>
            @endcan

            @can('jasmed-tunai')
                <button wire:click="navigateTo('tunai')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'tunai' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.cash" class="h-4 w-4 shrink-0" />
                    Pasien Tunai
                </button>
            @endcan

            @can('jasmed-jkmd')
                <button wire:click="navigateTo('jkmd')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'jkmd' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.building-hospital" class="h-4 w-4 shrink-0" />
                    JKMD
                </button>
            @endcan

            @can('verify-jasa')
                <button wire:click="navigateTo('verifikasi')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'verifikasi' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.file-check" class="h-4 w-4 shrink-0" />
                    Verifikasi
                </button>
            @endcan
        </nav>
    </div>

    {{-- Content Area --}}
    <div class="w-full">
        @switch($content)
            @case('bpjs')
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="w-full rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                        <livewire:jasmed.bpjs-import :$content :key="Str::random()" />
                    </div>

                    <div class="flex w-full flex-col gap-5">
                        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                            <livewire:Jasmed.BpjsRajal :key="Str::random()" />
                        </div>
                        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                            <livewire:Jasmed.BpjsRanap :key="Str::random()" />
                        </div>
                    </div>
                </div>
            @break

            @case('tunai')
                <livewire:Jasmed.Tunai :key="Str::random()" />
            @break

            @case('jkmd')
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="w-full rounded-2xl bg-white p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex flex-col rounded-xl border border-indigo-200 bg-indigo-50/60 p-4 text-xs text-indigo-900">
                            <span class="font-bold text-indigo-700 flex items-center gap-1.5">
                                <x-ts:icon name="tabler.info-circle" class="h-4 w-4 text-indigo-600" />
                                Ketentuan Impor Data JKMD
                            </span>
                            <ul class="mt-2 ms-5 list-disc space-y-1 text-slate-700 font-medium">
                                <li>Generate SEP dengan format: <b>{layanan}MRN-TGLCHECKOUT</b></li>
                                <li>Rumus Excel: <b><i>{RI/RJ}{MRN}&"-"&{TGLCHECKOUT}</i></b></li>
                                <li>Formula Remunerasi: <b>(Total RS - Non-Coverage) x (1 - 20%)</b></li>
                            </ul>
                        </div>
                        <livewire:jasmed.bpjs-import :$content :key="Str::random()" />
                    </div>

                    <div class="flex w-full flex-col gap-5">
                        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                            <livewire:Jasmed.Jkmd.Rajal :key="Str::random()" />
                        </div>
                        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                            <livewire:Jasmed.Jkmd.Ranap :key="Str::random()" />
                        </div>
                    </div>
                </div>
            @break

            @case('verifikasi')
                <div class="w-full rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                    <livewire:Jasmed.Verify.Index key="verify-jasmed" />
                </div>
            @break

            @default
                <div class="w-full rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                    <livewire:Jasmed.Dashboard :key="Str::random()" />
                </div>
        @endswitch
    </div>
</div>
