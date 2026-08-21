<div class="flex flex-col gap-4">
    <!-- Header & Period Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl bg-white p-5 shadow-2xs border border-slate-100">
        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Jadwal Tugas Saya
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Daftar jadwal tugas dan shift Anda pada periode yang dipilih.
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <x-ts:select.styled wire:model.live="bulan" :options="$bulanOptions" select="label:label|value:value" class="w-36" />
            <x-ts:select.styled wire:model.live="tahun" :options="$tahunOptions" select="label:label|value:value" class="w-28" />
        </div>
    </div>

    @if(count($details) > 0)
        @php
            $totalKerja = $details->whereNotNull('shift_id')->count();
            $totalLibur = $details->whereNull('shift_id')->count();
        @endphp

        <!-- Ringkasan Statistik Periode -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-100 bg-white p-3.5 shadow-2xs flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 shrink-0">
                    <x-tabler-calendar-stats class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Terjadwal</span>
                    <div class="text-sm font-extrabold text-slate-800 mt-0.5">{{ count($details) }} Hari</div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-white p-3.5 shadow-2xs flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 shrink-0">
                    <x-tabler-briefcase class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hari Kerja</span>
                    <div class="text-sm font-extrabold text-emerald-600 mt-0.5">{{ $totalKerja }} Hari</div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-white p-3.5 shadow-2xs flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 shrink-0">
                    <x-tabler-coffee class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hari Libur</span>
                    <div class="text-sm font-extrabold text-rose-500 mt-0.5">{{ $totalLibur }} Hari</div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-white p-3.5 shadow-2xs flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600 shrink-0">
                    <x-tabler-building-hospital class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ruangan / Unit</span>
                    <div class="text-xs font-bold text-slate-700 mt-0.5 line-clamp-2 leading-tight">
                        {{ $details->first()?->jadwalKerja?->ruangan?->nama ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- List Vertikal Jadwal Tugas -->
        <div class="rounded-xl bg-white shadow-2xs border border-slate-100 overflow-hidden divide-y divide-slate-100">
            @foreach($details as $detail)
                @php
                    $dt = \Carbon\Carbon::parse($detail->tanggal);
                    $isToday = $dt->isToday();
                    $isWeekend = $dt->isWeekend();
                @endphp
                <div class="flex flex-col md:flex-row md:items-center gap-4 py-3 px-4 transition-colors hover:bg-slate-50/70 {{ $isToday ? 'border-l-4 border-l-indigo-600 bg-indigo-50/30' : ($isWeekend ? 'bg-slate-50/40' : '') }}">
                    <!-- Tanggal (Kiri) -->
                    <div class="flex items-center gap-3 md:w-52 shrink-0">
                        <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg shrink-0 {{ $isToday ? 'bg-indigo-600 text-white font-bold' : ($isWeekend ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                            <span class="text-[10px] font-medium leading-none uppercase">{{ $dt->translatedFormat('M') }}</span>
                            <span class="text-base font-bold leading-tight">{{ $dt->format('d') }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-sm text-slate-800">
                                    {{ $dt->translatedFormat('l') }}
                                </span>
                                @if($isToday)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-700">Hari Ini</span>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400">
                                {{ $dt->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Shift + Jam Kerja (Rata Kiri) -->
                    <div class="flex-1 flex flex-wrap items-center gap-2.5">
                        @if($detail->shift_id)
                            <span class="px-2.5 py-1 text-xs font-bold rounded-md shadow-2xs border border-black/5 shrink-0" style="background-color: {{ $detail->shift->warna ?? '#e2e8f0' }}; color: #1e293b;">
                                {{ $detail->shift->kode }}
                            </span>
                            <span class="text-xs font-semibold text-slate-700">
                                {{ $detail->shift->nama }}
                            </span>
                            <span class="text-slate-300 hidden sm:inline">•</span>
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100/80 px-2.5 py-1 rounded-md shrink-0">
                                <x-tabler-clock class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                                <span>{{ \Carbon\Carbon::parse($detail->shift->jam_masuk)->format('H:i') }}</span>
                                <span class="text-slate-400">—</span>
                                <span>{{ \Carbon\Carbon::parse($detail->shift->jam_keluar)->format('H:i') }}</span>
                            </div>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-slate-200 text-slate-600 shrink-0">
                                LIBUR
                            </span>
                            <span class="text-xs italic text-slate-400">
                                Libur Terjadwal
                            </span>
                        @endif
                    </div>

                    <!-- Status Kehadiran (Kanan) -->
                    <div class="ml-auto shrink-0">
                        <x-ts:badge :color="$detail->status_kehadiran->color()" text="{{ $detail->status_kehadiran->nama() }}" sm />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl bg-white p-10 text-center shadow-2xs border border-slate-100 space-y-4">
            @if($isSuperAdmin)
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <x-tabler-shield-check class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Mode Super-Admin</h3>
                    <p class="mt-1.5 max-w-md mx-auto text-xs text-slate-500 leading-relaxed">
                        Akun ini memiliki wewenang penuh untuk memantau, menyusun, dan menyetujui jadwal seluruh unit di RSBA. Jadwal shift personal Anda saat ini kosong.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('kepegawaian.jadwal-kerja.index') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-all">
                        <x-tabler-calendar-event class="h-4 w-4" />
                        <span>Buka Manajemen Jadwal Kerja</span>
                    </a>
                </div>
            @elseif(!$hasKaryawan)
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
                    <x-tabler-user-off class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Profil Pegawai Belum Ditautkan</h3>
                    <p class="mt-1.5 max-w-md mx-auto text-xs text-slate-500 leading-relaxed">
                        Akun login Anda belum terhubung ke data Master Pegawai/Karyawan RSBA. Silakan hubungi bagian SDM/Kepegawaian untuk penautan profil akun.
                    </p>
                </div>
            @elseif(!$hasRuangan)
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 border border-sky-100">
                    <x-tabler-building-hospital class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Penugasan Ruangan Belum Ditetapkan</h3>
                    <p class="mt-1.5 max-w-md mx-auto text-xs text-slate-500 leading-relaxed">
                        Data pegawai Anda belum ditempatkan pada Ruangan / Unit Kerja aktif. Penjadwalan shift memerlukan penugasan ruangan oleh Koordinator atau Bagian SDM.
                    </p>
                </div>
            @else
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 border border-slate-200/60">
                    <x-tabler-calendar-x class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Jadwal Terbit</h3>
                    <p class="mt-1.5 max-w-md mx-auto text-xs text-slate-500 leading-relaxed">
                        Belum ada jadwal kerja yang dipublikasikan untuk Anda pada bulan {{ date('F', mktime(0, 0, 0, $bulan, 1)) }} {{ $tahun }}. Silakan hubungi Koordinator Ruangan Anda.
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>

