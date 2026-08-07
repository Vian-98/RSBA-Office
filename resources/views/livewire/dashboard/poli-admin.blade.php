<div class="space-y-6">
    <!-- Header Controls & SIMRS Integration Status -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
        <div class="flex items-center gap-3">
        </div>
        <button wire:click="syncMasterData"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-xl transition dark:bg-violet-950/40 dark:text-violet-300 dark:hover:bg-violet-900/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Sync Master Data
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button 
            wire:click="setTab('poli')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'poli' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Daftar Poli
        </button>
        <button 
            wire:click="setTab('doctors')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'doctors' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Dokter Bertugas
        </button>
        <button 
            wire:click="setTab('queue')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'queue' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Antrian Pasien SIMRS
        </button>
    </div>

    <!-- Alert Banner -->
    @if($successMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between gap-3 text-sm dark:bg-emerald-950/20 dark:border-emerald-800/30 dark:text-emerald-400">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" @click="show = false" wire:click="$set('successMessage', '')" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 p-1 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors focus:outline-none shrink-0" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between gap-3 text-sm dark:bg-rose-950/20 dark:border-rose-800/30 dark:text-rose-400">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" @click="show = false" wire:click="$set('errorMessage', '')" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors focus:outline-none shrink-0" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- TAB CONTENT: On-Demand Sub-components --}}
    @if($activeTab === 'poli')
        <livewire:dashboard.poli.poli-tab-daftar wire:key="tab-poli" />
    @elseif($activeTab === 'doctors')
        <livewire:dashboard.poli.poli-tab-dokter wire:key="tab-doctors" />
    @elseif($activeTab === 'queue')
        <livewire:dashboard.poli.poli-tab-antrian wire:key="tab-queue" />
    @endif
</div>
