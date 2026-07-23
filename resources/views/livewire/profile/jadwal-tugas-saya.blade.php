@php
    // Calculate total overtime for this month
    $totalOvertimeBulan = 0;
    foreach($details as $detail) {
        $status = $detail->status_kehadiran;
        $isAbsent = in_array($status, [
            \App\Enums\StatusKehadiran::CUTI,
            \App\Enums\StatusKehadiran::IZIN,
            \App\Enums\StatusKehadiran::TIDAK_HADIR
        ]);
        if (!$isAbsent && $detail->absen_masuk_at && $detail->absen_keluar_at) {
            if ($detail->shift) {
                $shift = $detail->shift;
                $jamKeluar = \Carbon\Carbon::parse($shift->jam_keluar);
                $targetCheckout = \Carbon\Carbon::parse(\Carbon\Carbon::parse($detail->tanggal)->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                if ($shift->lintas_hari || $jamKeluar->lt(\Carbon\Carbon::parse($shift->jam_masuk))) {
                    $targetCheckout->addDay();
                }
                if (\Carbon\Carbon::parse($detail->absen_keluar_at)->gt($targetCheckout)) {
                    $totalOvertimeBulan += abs(\Carbon\Carbon::parse($detail->absen_keluar_at)->diffInMinutes($targetCheckout));
                }
            } else {
                $totalOvertimeBulan += abs(\Carbon\Carbon::parse($detail->absen_keluar_at)->diffInMinutes(\Carbon\Carbon::parse($detail->absen_masuk_at)));
            }
        }
    }
@endphp

<div class="flex flex-col gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl bg-white p-3.5 sm:p-4 shadow-sm border border-slate-100">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-800">
                Jadwal Kerja Saya
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5">
                <span>Lihat jadwal tugas dan shift Anda pada periode yang dipilih.</span>
                @if($totalOvertimeBulan > 0)
                    @php
                        $toh = floor($totalOvertimeBulan / 60);
                        $tom = $totalOvertimeBulan % 60;
                    @endphp
                    <span class="text-slate-300 font-bold hidden sm:inline">•</span>
                    <span class="text-indigo-700 font-bold bg-indigo-50 px-2 py-0.5 rounded text-xs select-none">Total Overtime: {{ $toh }}j {{ $tom }}m</span>
                @endif
            </p>
        </div>
        <div class="flex gap-2 items-center w-full sm:w-auto shrink-0">
            <x-ts:select.styled wire:model.live="bulan" :options="$bulanOptions" select="label:label|value:value" class="flex-1 sm:w-32" />
            <x-ts:select.styled wire:model.live="tahun" :options="$tahunOptions" select="label:label|value:value" class="flex-1 sm:w-24" />
        </div>
    </div>

    @if(count($details) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($details as $detail)
                @php
                    $status = $detail->status_kehadiran;
                    $isAbsentType = in_array($status, [
                        \App\Enums\StatusKehadiran::CUTI,
                        \App\Enums\StatusKehadiran::IZIN,
                        \App\Enums\StatusKehadiran::TIDAK_HADIR
                    ]);                    // Calculate Overtime for this date
                    $overtimeMenit = 0;
                    $overtimeKeterangan = '';
                    if (!$isAbsentType && $detail->absen_masuk_at && $detail->absen_keluar_at) {
                        $masuk = \Carbon\Carbon::parse($detail->absen_masuk_at);
                        $keluar = \Carbon\Carbon::parse($detail->absen_keluar_at);

                        if ($detail->shift) {
                            $shift = $detail->shift;
                            $jamKeluar = \Carbon\Carbon::parse($shift->jam_keluar);
                            $targetCheckout = \Carbon\Carbon::parse(\Carbon\Carbon::parse($detail->tanggal)->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                            
                            if ($shift->lintas_hari || $jamKeluar->lt(\Carbon\Carbon::parse($shift->jam_masuk))) {
                                    $targetCheckout->addDay();
                            }

                            if ($keluar->gt($targetCheckout)) {
                                $overtimeMenit = abs($keluar->diffInMinutes($targetCheckout));
                                $overtimeKeterangan = "Pulang terlambat dari shift " . $shift->nama;
                            }
                        } else {
                            $overtimeMenit = abs($keluar->diffInMinutes($masuk));
                            $overtimeKeterangan = "Tapping masuk pada hari Libur/OFF";
                        }
                    }

                    // Parse lateness and early checkout
                    $lateMins = 0;
                    $earlyMins = 0;
                    if ($detail->catatan) {
                        if (preg_match('/Terlambat (-?\d+) menit/i', $detail->catatan, $matches)) {
                            $lateMins = abs((int) $matches[1]);
                        }
                        if (preg_match('/Pulang cepat (-?\d+) menit/i', $detail->catatan, $matches)) {
                            $earlyMins = abs((int) $matches[1]);
                        }
                    }

                    // Clean general notes (remove lateness/early checkout text)
                    $cleanCatatan = $detail->catatan;
                    if ($cleanCatatan) {
                        $cleanCatatan = preg_replace('/Terlambat -?\d+ menit\.?/i', '', $cleanCatatan);
                        $cleanCatatan = preg_replace('/Pulang cepat -?\d+ menit\.?/i', '', $cleanCatatan);
                        $cleanCatatan = trim($cleanCatatan);
                    }

                    // Card Styling
                    $cardClass = 'bg-white border-slate-150';
                    if ($status === \App\Enums\StatusKehadiran::CUTI) {
                        $cardClass = 'bg-sky-50/50 border-sky-200/60 ring-1 ring-sky-100/50';
                    } elseif ($status === \App\Enums\StatusKehadiran::IZIN) {
                        $cardClass = 'bg-amber-50/40 border-amber-200/60 ring-1 ring-amber-100/50';
                    } elseif ($status === \App\Enums\StatusKehadiran::TIDAK_HADIR) {
                        $cardClass = 'bg-rose-50/50 border-rose-200/60 ring-1 ring-rose-100/50';
                    } elseif ($status === \App\Enums\StatusKehadiran::TERLAMBAT) {
                        $cardClass = 'bg-yellow-50/30 border-yellow-200/60';
                    } elseif ($overtimeMenit > 0) {
                        $cardClass = 'bg-indigo-50/20 border-indigo-200 border-l-4 border-l-indigo-500';
                    } elseif (\Carbon\Carbon::parse($detail->tanggal)->isWeekend()) {
                        $cardClass = 'bg-red-50/40 border-red-100';
                    }
                @endphp
                <div class="rounded-xl p-4 shadow-sm border flex flex-col gap-2 transition-all duration-300 hover:shadow-md {{ $cardClass }}">
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-bold text-gray-700 text-sm">
                            {{ \Carbon\Carbon::parse($detail->tanggal)->translatedFormat('l, d F Y') }}
                        </span>
                        @if($status === \App\Enums\StatusKehadiran::CUTI)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-sky-100 text-sky-800 border border-sky-200">
                                CUTI
                            </span>
                        @elseif($status === \App\Enums\StatusKehadiran::IZIN)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-amber-100 text-amber-800 border border-amber-200">
                                IZIN
                            </span>
                        @elseif($status === \App\Enums\StatusKehadiran::TIDAK_HADIR)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-red-100 text-red-800 border border-red-200">
                                TIDAK HADIR
                            </span>
                        @elseif($detail->shift_id)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded text-slate-800 border border-black/5" style="background-color: {{ $detail->shift->warna ?? '#e2e8f0' }}">
                                {{ $detail->shift->kode }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-gray-200 text-gray-700 border border-gray-300">
                                LIBUR
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex flex-col gap-1 text-sm text-gray-600">
                        @if($detail->shift_id)
                            <div class="flex justify-between {{ $isAbsentType ? 'line-through text-gray-400' : '' }}">
                                <span>Jam Kerja Shift:</span>
                                <span>{{ \Carbon\Carbon::parse($detail->shift->jam_masuk)->format('H:i') }} - {{ \Carbon\Carbon::parse($detail->shift->jam_keluar)->format('H:i') }}</span>
                            </div>
                        @else
                            <div class="text-center py-2 text-gray-500 italic text-xs">
                                Hari Libur Terjadwal
                            </div>
                        @endif
                        
                        <div class="flex justify-between border-t mt-1 pt-1">
                            <span>Status Kehadiran:</span>
                            <x-ts:badge :color="$detail->status_kehadiran->color()" text="{{ $detail->status_kehadiran->nama() }}" xs />
                        </div>
                        
                        @if(!$isAbsentType && ($detail->absen_masuk_at || $detail->absen_keluar_at))
                            <div class="flex justify-between text-gray-500 text-xs">
                                <span>Jam Masuk Aktual:</span>
                                <span>{{ $detail->absen_masuk_at ? \Carbon\Carbon::parse($detail->absen_masuk_at)->format('H:i') : '--:--' }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500 text-xs">
                                <span>Jam Keluar Aktual:</span>
                                <span>{{ $detail->absen_keluar_at ? \Carbon\Carbon::parse($detail->absen_keluar_at)->format('H:i') : '--:--' }}</span>
                            </div>
                        @endif

                        @if($lateMins > 0)
                            <div class="flex justify-between text-rose-600 font-semibold text-xs border-t border-rose-100/30 pt-1 mt-1">
                                <span>Keterlambatan:</span>
                                <span class="bg-rose-50 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $lateMins }} menit</span>
                            </div>
                        @endif

                        @if($earlyMins > 0)
                            <div class="flex justify-between text-amber-600 font-semibold text-xs border-t border-amber-100/30 pt-1 mt-1">
                                <span>Pulang Lebih Awal:</span>
                                <span class="bg-amber-50 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $earlyMins }} menit</span>
                            </div>
                        @endif

                        @if($overtimeMenit > 0)
                            @php
                                $oh = floor($overtimeMenit / 60);
                                $om = $overtimeMenit % 60;
                                $overtimeFormatted = $oh > 0 ? "{$oh}j {$om}m" : "{$om}m";
                            @endphp
                            <div class="flex justify-between text-indigo-600 font-semibold text-xs border-t border-indigo-100/50 pt-1 mt-1">
                                <span>Kelebihan Jam (Overtime):</span>
                                <span class="bg-indigo-50 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $overtimeFormatted }}</span>
                            </div>
                        @endif
                        
                        @if($cleanCatatan)
                            <div class="text-xs text-slate-500 mt-1 font-medium bg-slate-50 p-1.5 rounded border border-slate-200">
                                Catatan: {{ $cleanCatatan }}
                            </div>
                        @endif
                        
                        @if($detail->status_kehadiran === \App\Enums\StatusKehadiran::PERLU_VERIFIKASI)
                            <div class="text-xs text-yellow-600 mt-1 font-semibold flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                                Menunggu konfirmasi SDM
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-white p-8 text-center shadow-sm">
            <x-ts:icon name="tabler.calendar-x" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Tidak Ada Jadwal</h3>
            <p class="mt-1 text-sm text-gray-500">
                Belum ada jadwal kerja yang dipublikasikan untuk Anda pada periode ini.
            </p>
        </div>
    @endif
</div>
