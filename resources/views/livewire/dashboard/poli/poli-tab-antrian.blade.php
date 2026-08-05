<div class="space-y-6">
    <!-- Filter: Poli & Dokter -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Pilih Poliklinik</label>
                <select wire:model.live="queuePoliId" wire:change="loadQueue"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <option value="">— Pilih Poli —</option>
                    @foreach($polyclinics as $poli)
                        <option value="{{ $poli['id'] }}">{{ $poli['code'] }} — {{ $poli['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Filter Dokter</label>
                <select wire:model.live="queueDoctorId" wire:change="loadQueue"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Dokter</option>
                    @foreach($queueAvailableDoctors as $doc)
                        <option value="{{ $doc['id'] }}">{{ $doc['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($queuePoliId)
        <!-- Form Tambah Pasien Wadah SIMRS -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Wadah Entri Antrian Pasien</h3>
            <form wire:submit="addPatient" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Dokter Bertugas</label>
                    <select wire:model="queueDoctorId" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        <option value="">— Pilih Dokter —</option>
                        @foreach($queueAvailableDoctors as $doc)
                            <option value="{{ $doc['id'] }}">{{ $doc['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Pasien</label>
                    <input type="text" wire:model="patientName" placeholder="Nama pasien dari SIMRS" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                </div>
                <button type="submit"
                    class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 px-6 rounded-xl transition">
                    + Tambah Antrian
                </button>
            </form>
        </div>

        <!-- Tabel Antrian Pasien -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Antrian Pasien SIMRS — {{ now()->translatedFormat('l, d F Y') }}</h3>
                <span class="text-xs text-gray-400">{{ count($queueItems) }} pasien</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase w-16">No.</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Nama Pasien</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Dokter Bertugas</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status Antrean</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Dipanggil</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($queueItems as $item)
                            <tr class="transition {{ $item['status'] === 'dilayani' ? 'bg-amber-50 dark:bg-amber-900/10' : ($item['status'] === 'terlewat' ? 'opacity-60' : 'hover:bg-gray-50 dark:hover:bg-gray-700/20') }}">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm
                                        {{ $item['status'] === 'dilayani' ? 'bg-amber-200 text-amber-800 dark:bg-amber-800/30 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        {{ $item['queue_number'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold {{ $item['status'] === 'terlewat' ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $item['patient_name'] }}
                                    @if($item['is_fallback'] ?? false)
                                        <span class="ml-2 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Mode Local</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item['doctor_name'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <select 
                                        wire:change="changeQueueStatus('{{ $item['id'] }}', $event.target.value)"
                                        class="text-xs font-bold rounded-full px-2.5 py-1.5 border-0 ring-1 ring-inset focus:ring-2 focus:ring-violet-500 bg-transparent cursor-pointer transition
                                        {{ $item['status'] === 'menunggu' ? 'text-gray-600 bg-gray-50 ring-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:ring-gray-700' : '' }}
                                        {{ $item['status'] === 'dilayani' ? 'text-amber-700 bg-amber-50 ring-amber-200 dark:text-amber-400 dark:bg-amber-950/20 dark:ring-amber-900/30' : '' }}
                                        {{ $item['status'] === 'selesai' ? 'text-emerald-700 bg-emerald-50 ring-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/20 dark:ring-emerald-900/30' : '' }}
                                        {{ $item['status'] === 'terlewat' ? 'text-rose-700 bg-rose-50 ring-rose-200 dark:text-rose-400 dark:bg-rose-950/20 dark:ring-rose-900/30' : '' }}"
                                    >
                                        <option value="menunggu" class="text-gray-800 bg-white dark:text-white dark:bg-gray-900" {{ $item['status'] === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="dilayani" class="text-gray-800 bg-white dark:text-white dark:bg-gray-900" {{ $item['status'] === 'dilayani' ? 'selected' : '' }}>Dilayani</option>
                                        <option value="selesai" class="text-gray-800 bg-white dark:text-white dark:bg-gray-900" {{ $item['status'] === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="terlewat" class="text-gray-800 bg-white dark:text-white dark:bg-gray-900" {{ $item['status'] === 'terlewat' ? 'selected' : '' }}>Terlewat</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-400">
                                    {{ $item['called_at'] ? \Carbon\Carbon::parse($item['called_at'])->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($item['status'] !== 'selesai')
                                        <button wire:click="deleteQueueItem('{{ $item['id'] }}')" wire:confirm="Hapus antrian ini?" class="px-2.5 py-1 text-xs font-semibold text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition dark:hover:text-rose-400 dark:hover:bg-rose-900/20" title="Hapus">
                                            Hapus ✕
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada antrian pasien.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
