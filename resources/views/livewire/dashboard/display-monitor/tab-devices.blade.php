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
                                <td class="py-3 font-mono">
                                    <code title="{{ !empty($dev['ip_address']) ? $dev['ip_address'] : 'ip belum dimasukkan' }}" class="cursor-help underline decoration-dotted decoration-gray-400 hover:decoration-sky-500">
                                        {{ $dev['display_id'] }}
                                    </code>
                                </td>
                                <td class="py-3 font-semibold text-gray-800 dark:text-white">
                                    @if($editDisplayId === $dev['display_id'])
                                        <div class="flex flex-col gap-1.5">
                                            <input wire:model="editDeviceName" wire:keydown.enter="updateDevice" type="text" placeholder="Nama Lokasi" class="px-2 py-1 w-full min-w-[150px] bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                                            <input wire:model="editIpAddress" wire:keydown.enter="updateDevice" type="text" placeholder="IP Armbian (opsional)" class="px-2 py-1 w-full min-w-[150px] bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded text-xs text-gray-800 dark:text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                                            <div class="flex items-center gap-2 mt-1">
                                                <button wire:click="updateDevice" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded" title="Simpan">Simpan</button>
                                                <button wire:click="cancelEdit" class="px-2.5 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded" title="Batal">Batal</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 group">
                                            <span>{{ $dev['name'] }}</span>
                                            <button wire:click="editDevice('{{ $dev['display_id'] }}', '{{ $dev['name'] }}', '{{ $dev['ip_address'] ?? '' }}')" class="opacity-0 group-hover:opacity-100 text-sky-500 hover:text-sky-600 transition-opacity" title="Ubah Perangkat">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
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
                                            @if($dev['mappings'][0]['target_type'] === 'ward_class')
                                                Rawat Inap
                                            @elseif($dev['mappings'][0]['target_type'] === 'ward_summary')
                                                Summary Inap
                                            @elseif($dev['mappings'][0]['target_type'] === 'inpatient_room')
                                                Ruang Inap
                                            @elseif($dev['mappings'][0]['target_type'] === 'polyclinic')
                                                Poliklinik
                                            @else
                                                Kamar Operasi
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Belum Dimap</span>
                                    @endif
                                </td>
                                <td class="py-3 font-medium text-gray-800 dark:text-white">
                                    @if(!empty($dev['mappings']))
                                        @if($dev['mappings'][0]['target_type'] === 'ward_summary')
                                            Semua Kamar
                                        @else
                                            {{ $dev['mappings'][0]['target']['name'] ?? 'Unknown' }}
                                        @endif
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

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1" for="ipAddress">IP Armbian (Opsional)</label>
                        <input wire:model="ipAddress" type="text" id="ipAddress" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="MISAL: 192.168.1.100">
                        @error('ipAddress') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
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
                            <option value="ward_class">Ketersediaan Kamar Rawat Inap (Per Kelas)</option>
                            <option value="ward_summary">Summary Rawat Inap (Semua Kamar)</option>
                            <option value="operating_room">Jadwal Kamar Operasi</option>
                            <option value="inpatient_room">Ruangan Custom (Lantai & Gedung)</option>
                            <option value="polyclinic">Poliklinik</option>
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
                            @elseif($targetType === 'inpatient_room')
                                @foreach($inpatientRooms as $ir)
                                    <option value="{{ $ir['id'] }}">{{ $ir['room_code'] }} - {{ $ir['name'] }} (Fl. {{ $ir['floor'] }} / {{ $ir['building'] }})</option>
                                @endforeach
                            @elseif($targetType === 'polyclinic')
                                @foreach($polyclinics as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['code'] }} - {{ $p['name'] }}</option>
                                @endforeach
                            @elseif($targetType === 'ward_summary')
                                <option value="all">Semua Ruangan</option>
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
