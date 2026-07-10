<div class="space-y-4 p-1">

    {{-- Welcome banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-700 p-6 text-white shadow-lg">
        <div class="relative z-10">
            <p class="text-sm font-medium text-indigo-100">Selamat datang kembali 👋</p>
            <h2 class="mt-1 text-2xl font-bold">{{ $karyawan->full_nama }}</h2>
            <p class="mt-0.5 text-sm text-indigo-200">{{ $karyawan->jabatan?->first()?->nama ?? 'Belum ada jabatan' }} &bull; {{ $karyawan->status->nama() }}</p>
        </div>
        {{-- Decorative circles --}}
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 -right-4 h-24 w-24 rounded-full bg-white/10"></div>
        <div class="absolute bottom-4 right-24 h-10 w-10 rounded-full bg-white/10"></div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Masa kerja --}}
        <div class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <x-tabler-briefcase class="h-4 w-4" />
            </div>
            <span class="mt-2 text-xl font-bold text-slate-800">{{ $diff->y }}<span class="text-sm font-medium text-slate-500"> Th {{ $diff->m }} Bln</span></span>
            <span class="text-xs text-slate-400">Masa Kerja</span>
        </div>

        {{-- Sisa Cuti --}}
        <div class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <x-tabler-calendar-check class="h-4 w-4" />
            </div>
            @if($sisaCuti < 0)
                <span class="mt-2 text-xl font-bold text-rose-500">-</span>
                <span class="text-xs text-slate-400">Sisa Cuti <span class="text-rose-400">(< 1 Th)</span></span>
            @else
                <span class="mt-2 text-xl font-bold text-slate-800">{{ $sisaCuti }}<span class="text-sm font-medium text-slate-500"> Hari</span></span>
                <span class="text-xs text-slate-400">Sisa Cuti Tahunan</span>
            @endif
        </div>

        {{-- Cuti Pending --}}
        <div class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                <x-tabler-clock-hour-4 class="h-4 w-4" />
            </div>
            <span class="mt-2 text-xl font-bold text-slate-800">{{ $pendingCuti }}</span>
            <span class="text-xs text-slate-400">Cuti Menunggu</span>
        </div>

        {{-- Anniversary countdown --}}
        <div class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                <x-tabler-confetti class="h-4 w-4" />
            </div>
            <span class="mt-2 text-xl font-bold text-slate-800">{{ $daysToAnniversary }}<span class="text-sm font-medium text-slate-500"> Hari</span></span>
            <span class="text-xs text-slate-400">Reset Cuti Berikutnya</span>
        </div>
    </div>

    {{-- Info karyawan & riwayat cuti --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Info pribadi --}}
        <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center gap-2">
                <x-tabler-user class="h-4 w-4 text-indigo-500" />
                <h3 class="text-sm font-semibold text-slate-700">Informasi Kepegawaian</h3>
            </div>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">NIP</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->nip }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">NIK</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->nik ?? '-' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">NPWP</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->npwp ?? '-' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">BPJS Kesehatan</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->bpjs_kesehatan ?? '-' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">BPJS Ketenagakerjaan</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->bpjs_tk ?? '-' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">Tanggal Masuk</dt>
                    <dd class="font-medium text-slate-700">
                        {{ $karyawan->tgl_masuk ? \Carbon\Carbon::parse($karyawan->tgl_masuk)->translatedFormat('d F Y') : '-' }}
                    </dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">Jabatan</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->jabatan?->first()?->nama ?? '-' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <dt class="text-slate-400">Agama</dt>
                    <dd class="font-medium text-slate-700">{{ ucfirst($karyawan->agama ?? '-') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Usia</dt>
                    <dd class="font-medium text-slate-700">{{ $karyawan->usia }}</dd>
                </div>
            </dl>
        </div>

        {{-- Riwayat cuti terbaru --}}
        <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center gap-2">
                <x-tabler-calendar-event class="h-4 w-4 text-indigo-500" />
                <h3 class="text-sm font-semibold text-slate-700">Riwayat Cuti Terbaru</h3>
            </div>

            @if($recentCuti->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-center text-slate-300">
                    <x-tabler-calendar-off class="h-10 w-10 mb-2" />
                    <span class="text-sm">Belum ada riwayat pengajuan cuti</span>
                </div>
            @else
                <ul class="space-y-2">
                    @foreach($recentCuti as $cuti)
                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                            <div>
                                <p class="text-xs font-semibold text-slate-700">{{ $cuti->no_surat ?? 'No. Surat: -' }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $cuti->tgl_mulai ? \Carbon\Carbon::parse($cuti->tgl_mulai)->translatedFormat('d M Y') : '-' }}
                                    &mdash; {{ $cuti->lama_cuti }} hari
                                </p>
                            </div>
                            <x-filament::badge :color="$cuti->status->color()">
                                {{ $cuti->status->nama() }}
                            </x-filament::badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
