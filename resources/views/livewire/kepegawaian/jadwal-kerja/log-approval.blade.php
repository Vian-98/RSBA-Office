<div class="py-2">
    @if(!$jadwalKerja)
        <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
            <x-ts:icon name="tabler.timeline" class="w-12 h-12 mb-3 opacity-30" />
            <p class="text-sm">Pilih jadwal kerja untuk melihat log persetujuan.</p>
        </div>
    @else
        {{-- Header Info Jadwal --}}
        <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex-shrink-0 rounded-lg bg-blue-100 dark:bg-blue-900/40 p-2">
                    <x-ts:icon name="tabler.calendar-stats" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Jadwal #{{ $jadwalKerja->id }} — {{ $jadwalKerja->ruangan?->nama ?? 'Ruangan Tidak Diketahui' }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::create($jadwalKerja->tahun, $jadwalKerja->bulan)->translatedFormat('F Y') }}
                        &bull; Tipe: <span class="capitalize">{{ $jadwalKerja->tipe }}</span>
                        &bull; Dibuat oleh: <span class="font-medium">{{ $jadwalKerja->pembuat?->full_nama ?? 'Sistem' }}</span>
                    </p>
                </div>
                @php
                    $statusMap = [
                        'draft'          => ['color' => 'bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-200',   'label' => 'Draf'],
                        'menunggu_kabid' => ['color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',       'label' => 'Menunggu Kabid'],
                        'menunggu_wadir' => ['color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',   'label' => 'Menunggu Wadir'],
                        'published'      => ['color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'label' => 'Dipublikasikan'],
                        'ditolak'        => ['color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',       'label' => 'Revisi / Dikembalikan'],
                        'locked'         => ['color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300', 'label' => 'Terkunci'],
                    ];
                    $s = $statusMap[$jadwalKerja->status->value] ?? ['color' => 'bg-gray-100 text-gray-700', 'label' => $jadwalKerja->status->value];
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $s['color'] }}">
                    {{ $s['label'] }}
                </span>
            </div>
        </div>

        {{-- Timeline --}}
        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-500">
                <x-ts:icon name="tabler.history" class="w-10 h-10 mb-2 opacity-30" />
                <p class="text-sm">Belum ada riwayat persetujuan untuk jadwal ini.</p>
                <p class="text-xs mt-1 opacity-70">Log akan muncul setelah pertama kali diajukan.</p>
            </div>
        @else
            <ol class="relative border-l border-gray-200 dark:border-gray-700 ml-4">
                @foreach($logs as $log)
                    @php
                        $iconBg = $log->aksiIconBg();
                        $actor  = $log->karyawan?->full_nama ?? $log->user?->name ?? 'Sistem';
                        $role   = $log->user?->getRoleNames()->first() ?? '—';
                        $roleLabel = match($role) {
                            'Super-Admin'              => 'Super Admin',
                            'Staff-SDM'               => 'Staff SDM',
                            'Kepala-Bidang'           => 'Kepala Bidang',
                            'Kepala-Ruangan'          => 'Kepala Ruangan',
                            'Wakil-Direktur'          => 'Wakil Direktur',
                            'Wadir-Medis-Keperawatan' => 'Wadir Medis & Keperawatan',
                            'Wadir-SDM-Umum'          => 'Wadir SDM & Umum',
                            'Wadir-Keuangan'          => 'Wadir Keuangan',
                            'Direktur'                => 'Direktur',
                            default                   => $role,
                        };
                    @endphp
                    <li class="mb-6 ml-6 last:mb-0">
                        {{-- Dot Icon --}}
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full {{ $iconBg }} ring-4 ring-white dark:ring-gray-900">
                            <x-ts:icon name="{{ $log->aksiIcon() }}" class="w-3.5 h-3.5 text-white" />
                        </span>

                        {{-- Content Card --}}
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 shadow-sm transition-shadow hover:shadow-md">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $log->aksiLabel() }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $actor }}</span>
                                        @if($roleLabel && $roleLabel !== '—')
                                            &middot;
                                            <span>{{ $roleLabel }}</span>
                                        @endif
                                    </p>
                                </div>
                                <time class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap" title="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                                </time>
                            </div>

                            {{-- Status Transition --}}
                            @if($log->status_sebelumnya || $log->status_sesudah)
                                <div class="mt-2 flex items-center gap-1.5 text-xs">
                                    @if($log->status_sebelumnya)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ \App\Models\Sdm\JadwalApprovalLog::statusLabel($log->status_sebelumnya) }}
                                        </span>
                                    @endif
                                    @if($log->status_sebelumnya && $log->status_sesudah)
                                        <x-ts:icon name="tabler.arrow-right" class="w-3.5 h-3.5 text-gray-400" />
                                    @endif
                                    @if($log->status_sesudah)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 font-medium
                                            @if($log->status_sesudah === 'published') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                            @elseif($log->status_sesudah === 'ditolak') bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300
                                            @elseif(str_contains($log->status_sesudah, 'wadir')) bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                            @elseif(str_contains($log->status_sesudah, 'kabid')) bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                            @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300
                                            @endif
                                        ">
                                            {{ \App\Models\Sdm\JadwalApprovalLog::statusLabel($log->status_sesudah) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Catatan Revisi --}}
                            @if($log->catatan)
                                <div class="mt-2 flex gap-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/40 px-3 py-2">
                                    <x-ts:icon name="tabler.message-2" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-amber-500" />
                                    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                        {{ $log->catatan }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    @endif
</div>
