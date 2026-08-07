<!-- TAB CONTENT: DASHBOARD -->
@if($activeTab === 'dashboard')
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
            <div>
                <span class="text-xs font-semibold text-gray-400 tracking-wider uppercase block">TOTAL MONITOR</span>
                <span class="text-3xl font-black text-gray-800 dark:text-white mt-1 block">{{ $totalMonitors }}</span>
            </div>
            <div class="p-3 bg-sky-50 text-sky-500 rounded-xl dark:bg-sky-950/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
            <div>
                <span class="text-xs font-semibold text-gray-400 tracking-wider uppercase block">MONITOR ONLINE</span>
                <span class="text-3xl font-black text-emerald-500 mt-1 block">{{ $onlineMonitors }}</span>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl dark:bg-emerald-950/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.828a5 5 0 010-7.07m7.07 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
            <div>
                <span class="text-xs font-semibold text-gray-400 tracking-wider uppercase block">MONITOR OFFLINE</span>
                <span class="text-3xl font-black text-rose-500 mt-1 block">{{ $offlineMonitors }}</span>
            </div>
            <div class="p-3 bg-rose-50 text-rose-500 rounded-xl dark:bg-rose-950/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 01-12.728 0m0 12.728a9 9 0 0112.728 0m-9.9-2.828a5 5 0 010-7.07m7.07 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Sync triggers -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/50 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Sinkronisasi Kamar BPJS</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Melakukan update data kapasitas kamar rawat inap lokal dari server Aplicares BPJS Kesehatan secara manual pada DMS middleware.</p>
            </div>
            <button wire:click="syncWards" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-semibold transition duration-150">
                Sync Kamar Rawat Inap
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/50 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Sinkronisasi Jadwal Operasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Melakukan update data jadwal tindakan kamar operasi lokal dari server BPJS secara manual pada DMS middleware.</p>
            </div>
            <button wire:click="syncSchedules" class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition duration-150">
                Sync Jadwal Kamar Operasi
            </button>
        </div>
    </div>
@endif
