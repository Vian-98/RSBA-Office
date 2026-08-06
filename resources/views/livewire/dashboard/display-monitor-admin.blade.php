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
        <button 
            wire:click="setTab('logs')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'logs' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Log Audit
        </button>
        <button 
            wire:click="setTab('inpatient_rooms')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'inpatient_rooms' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Ruangan Custom
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
    @include('livewire.dashboard.display-monitor.tab-dashboard')
    @include('livewire.dashboard.display-monitor.tab-devices')
    @include('livewire.dashboard.display-monitor.tab-data')
    @include('livewire.dashboard.display-monitor.tab-logs')
    @include('livewire.dashboard.display-monitor.tab-inpatient-rooms')
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
