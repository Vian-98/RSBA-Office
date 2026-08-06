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
                            <td class="py-2.5 text-xs text-gray-400">{{ !empty($w['synced_at']) ? \Carbon\Carbon::parse($w['synced_at'])->format('d M Y H:i:s') : '-' }}</td>
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
                            @forelse($room['schedules'] ?? [] as $sch)
                                <tr class="border-b border-gray-50/50 dark:border-gray-700/10">
                                    <td class="py-2 font-mono"><code>{{ $sch['bpjs_schedule_id'] }}</code></td>
                                    <td class="py-2 font-bold">{{ $sch['patient_name'] }}</td>
                                    <td class="py-2">{{ \Carbon\Carbon::parse($sch['scheduled_start_at'])->format('d M Y H:i') }}</td>
                                    <td class="py-2">{{ !empty($sch['actual_start_at']) ? \Carbon\Carbon::parse($sch['actual_start_at'])->format('d M Y H:i') : '-' }}</td>
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
