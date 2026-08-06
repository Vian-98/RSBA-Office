<div wire:poll.10s="loadData" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Log Aktivitas & Audit Mapping</h3>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Auto-Sync Background (10s)
        </span>
    </div>
    
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
                        <td class="py-3">{{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y H:i:s') }}</td>
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
