<div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button 
            wire:click="setTab('dashboard')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'dashboard' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Dashboard
        </button>
        <button 
            wire:click="setTab('devices')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'devices' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Display & Mapping
        </button>
        <button 
            wire:click="setTab('data')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'data' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Data Cache BPJS
        </button>
        @if($isSuperAdmin)
            <button 
                wire:click="setTab('logs')" 
                class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'logs' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                Log Audit
            </button>
        @endif
        <button 
            wire:click="setTab('inpatient_rooms')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'inpatient_rooms' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Ruangan Custom
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

    {{-- TAB CONTENT: On-Demand Lazy Sub-components --}}
    @if($activeTab === 'dashboard')
        <livewire:dashboard.display-monitor.display-tab-dashboard wire:key="tab-dashboard" />
    @elseif($activeTab === 'devices')
        <livewire:dashboard.display-monitor.display-tab-devices wire:key="tab-devices" />
    @elseif($activeTab === 'data')
        <livewire:dashboard.display-monitor.display-tab-bpjs-data wire:key="tab-data" />
    @elseif($activeTab === 'logs' && $isSuperAdmin)
        <livewire:dashboard.display-monitor.display-tab-audit-logs wire:key="tab-logs" />
    @elseif($activeTab === 'inpatient_rooms')
        <livewire:dashboard.display-monitor.display-tab-inpatient-rooms wire:key="tab-inpatient-rooms" />
    @endif
</div>

@push('script')
<script>
document.addEventListener('livewire:initialized', function () {
    const refreshInterval = setInterval(function () {
        if (document.visibilityState === 'visible') {
            Livewire.dispatch('display-status-changed');
        }
    }, 20000);

    document.addEventListener('livewire:navigating', function () {
        clearInterval(refreshInterval);
    }, { once: true });
});
</script>
@endpush
