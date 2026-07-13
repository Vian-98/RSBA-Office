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

    <!-- TAB CONTENT: DEVICES & MAPPING -->
    @if($activeTab === 'devices')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Devices Table -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Daftar Monitor Terdaftar</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-400 uppercase">
                                <th class="pb-3">Display ID</th>
                                <th class="pb-3">Nama/Lokasi</th>
                                <th class="pb-3">Koneksi</th>
                                <th class="pb-3">Mapped To</th>
                                <th class="pb-3">Rincian Target</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600 dark:text-gray-300">
                            @forelse($devices as $dev)
                                <tr class="border-b border-gray-50 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-900/10">
                                    <td class="py-3 font-mono"><code>{{ $dev['display_id'] }}</code></td>
                                    <td class="py-3 font-semibold text-gray-800 dark:text-white">{{ $dev['name'] }}</td>
                                    <td class="py-3">
                                        @if(($dev['status'] ?? 'offline') === 'online')
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-xs font-medium dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">Online</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-full text-xs font-medium dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30">Offline</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if(!empty($dev['mappings']))
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-full text-xs font-medium dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30">
                                                {{ $dev['mappings'][0]['target_type'] === 'ward_class' ? 'Rawat Inap' : 'Kamar Operasi' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">Belum Dimap</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-medium text-gray-800 dark:text-white">
                                        @if(!empty($dev['mappings']))
                                            {{ $dev['mappings'][0]['target']['name'] ?? 'Unknown' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada monitor terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Forms -->
            <div class="space-y-6">
                <!-- Add Monitor Form -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                    <h3 class="text-md font-bold text-gray-800 dark:text-white mb-4">Daftar Monitor Baru</h3>
                    
                    <form wire:submit.prevent="registerDevice" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1" for="displayId">Display ID</label>
                            <input wire:model="displayId" type="text" id="displayId" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="MISAL: DSP001" required>
                            @error('displayId') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1" for="deviceName">Nama Lokasi/Monitor</label>
                            <input wire:model="deviceName" type="text" id="deviceName" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="MISAL: Loket Rawat Inap" required>
                            @error('deviceName') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold transition duration-150">
                            Daftarkan Perangkat
                        </button>
                    </form>
                </div>

                <!-- Update Mapping Form -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                    <h3 class="text-md font-bold text-gray-800 dark:text-white mb-4">Set Map Konten Monitor</h3>
                    
                    <form wire:submit.prevent="updateMapping" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1" for="selectedDeviceId">Pilih Monitor</label>
                            <select wire:model.live="selectedDeviceId" id="selectedDeviceId" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" required>
                                <option value="">-- Pilih Monitor --</option>
                                @foreach($devices as $dev)
                                    <option value="{{ $dev['id'] }}">{{ $dev['display_id'] }} - {{ $dev['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1" for="targetType">Jenis Konten</label>
                            <select wire:model.live="targetType" id="targetType" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" required>
                                <option value="ward_class">Ketersediaan Kamar Rawat Inap</option>
                                <option value="operating_room">Jadwal Kamar Operasi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1" for="targetId">Pilih Target Tujuan</label>
                            <select wire:model="targetId" id="targetId" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" required>
                                <option value="">-- Pilih Target --</option>
                                @if($targetType === 'ward_class')
                                    @foreach($wards as $w)
                                        <option value="{{ $w['id'] }}">{{ $w['bpjs_class_code'] }} - {{ $w['name'] }}</option>
                                    @endforeach
                                @elseif($targetType === 'operating_room')
                                    @foreach($rooms as $r)
                                        <option value="{{ $r['id'] }}">{{ $r['bpjs_or_code'] }} - {{ $r['name'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition duration-150">
                            Simpan Map Konten
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB CONTENT: DATA CACHE BPJS -->
    @if($activeTab === 'data')
        <div class="space-y-6">
            <!-- Ward Classes availability -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Cache Kamar Rawat Inap</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-400 uppercase">
                                <th class="pb-3">Kode Kelas</th>
                                <th class="pb-3">Nama Kelas</th>
                                <th class="pb-3">Total Bed</th>
                                <th class="pb-3">Terisi</th>
                                <th class="pb-3">Tersedia</th>
                                <th class="pb-3">Waktu Sync</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600 dark:text-gray-300">
                            @forelse($wards as $w)
                                <tr class="border-b border-gray-50 dark:border-gray-700/30">
                                    <td class="py-2.5 font-bold">{{ $w['bpjs_class_code'] }}</td>
                                    <td class="py-2.5">{{ $w['name'] }}</td>
                                    <td class="py-2.5">{{ $w['current_availability']['bed_total'] ?? 0 }}</td>
                                    <td class="py-2.5">{{ $w['current_availability']['bed_occupied'] ?? 0 }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-xs font-medium dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">
                                            {{ $w['current_availability']['bed_available'] ?? 0 }} Bed Sisa
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-xs text-gray-400">{{ !empty($w['synced_at']) ? Carbon\Carbon::parse($w['synced_at'])->format('d M Y H:i:s') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400 italic">Belum ada data kamar rawat inap. Silakan sync manual di tab Dashboard.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Operating room schedules -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Cache Jadwal Operasi</h3>

                @forelse($rooms as $room)
                    <div class="mb-6 last:mb-0">
                        <h4 class="text-sm font-extrabold text-gray-700 dark:text-gray-300 border-l-4 border-purple-500 pl-3 mb-3">{{ $room['bpjs_or_code'] }} - {{ $room['name'] }}</h4>
                        <div class="overflow-x-auto pl-4">
                            <table class="min-w-full text-left">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-400 uppercase">
                                        <th class="pb-2">ID JADWAL</th>
                                        <th class="pb-2">PASIEN</th>
                                        <th class="pb-2">RENCANA MULAI</th>
                                        <th class="pb-2">MULAI AKTUAL</th>
                                        <th class="pb-2">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs text-gray-600 dark:text-gray-300">
                                    @forelse($room['schedules'] as $sch)
                                        <tr class="border-b border-gray-50/50 dark:border-gray-700/10">
                                            <td class="py-2 font-mono"><code>{{ $sch['bpjs_schedule_id'] }}</code></td>
                                            <td class="py-2 font-bold">{{ $sch['patient_name'] }}</td>
                                            <td class="py-2">{{ Carbon\Carbon::parse($sch['scheduled_start_at'])->format('d M Y H:i') }}</td>
                                            <td class="py-2">{{ !empty($sch['actual_start_at']) ? Carbon\Carbon::parse($sch['actual_start_at'])->format('d M Y H:i') : '-' }}</td>
                                            <td class="py-2">
                                                @if($sch['status'] === 'selesai')
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-xs font-medium dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">Selesai</span>
                                                @elseif($sch['status'] === 'sedang_dilaksanakan')
                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-full text-xs font-medium dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30 animate-pulse">Sedang Jalan</span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-full text-xs font-medium dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700/50">Menunggu</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-2 text-gray-400 italic">Tidak ada jadwal operasi terdaftar untuk kamar ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Belum ada kamar operasi terdaftar. Silakan sync manual di tab Dashboard.</p>
                @endforelse
            </div>
        </div>
    @endif

    <!-- TAB CONTENT: AUDIT LOGS -->
    @if($activeTab === 'logs')
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Log Aktivitas & Audit Mapping</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-400 uppercase">
                            <th class="pb-3">WAKTU</th>
                            <th class="pb-3">OPERATOR</th>
                            <th class="pb-3">MODUL</th>
                            <th class="pb-3">OPERASI</th>
                            <th class="pb-3">ENTITY ID</th>
                            <th class="pb-3">IP ADDRESS</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-gray-600 dark:text-gray-300">
                        @forelse($auditLogs as $log)
                            <tr class="border-b border-gray-50 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-900/10">
                                <td class="py-3">{{ Carbon\Carbon::parse($log['created_at'])->format('d M Y H:i:s') }}</td>
                                <td class="py-3 font-semibold text-gray-800 dark:text-white">{{ $log['user']['email'] ?? 'System' }}</td>
                                <td class="py-3 uppercase tracking-wider font-bold text-sky-500">{{ $log['module'] }}</td>
                                <td class="py-3 uppercase">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $log['operation'] === 'post' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20' : ($log['operation'] === 'put' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/20' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/20') }}">
                                        {{ $log['operation'] }}
                                    </span>
                                </td>
                                <td class="py-3 font-mono"><code>{{ $log['entity_id'] }}</code></td>
                                <td class="py-3 text-gray-400">{{ $log['ip_address'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-400 italic">Belum ada catatan aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
