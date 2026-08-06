<div class="space-y-6">
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
            Dokter
        </button>
        <button 
            wire:click="setTab('queue')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'queue' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Antrian Hari Ini
        </button>
    </div>

    <!-- Alert Banner -->
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2 text-sm dark:bg-emerald-950/20 dark:border-emerald-800/30 dark:text-emerald-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-2 text-sm dark:bg-rose-950/20 dark:border-rose-800/30 dark:text-rose-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    <!-- TAB CONTENTS -->
    @include('livewire.dashboard.poli-admin.tab-poli')
    @include('livewire.dashboard.poli-admin.tab-doctors')
    @include('livewire.dashboard.poli-admin.tab-queue')
</div>
