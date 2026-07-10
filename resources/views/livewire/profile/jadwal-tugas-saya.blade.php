<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Jadwal Kerja Saya
            </h2>
            <p class="text-sm text-gray-500">
                Lihat jadwal tugas dan shift Anda pada periode yang dipilih.
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <x-ts:select.styled wire:model.live="bulan" :options="$bulanOptions" select="label:label|value:value" class="w-32" />
            <x-ts:select.styled wire:model.live="tahun" :options="$tahunOptions" select="label:label|value:value" class="w-24" />
        </div>
    </div>

    @if(count($details) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($details as $detail)
                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100 flex flex-col gap-2 {{ \Carbon\Carbon::parse($detail->tanggal)->isWeekend() ? 'bg-red-50' : '' }}">
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-bold text-gray-700">
                            {{ \Carbon\Carbon::parse($detail->tanggal)->translatedFormat('l, d F Y') }}
                        </span>
                        @if($detail->shift_id)
                            <span class="px-2 py-1 text-xs font-semibold rounded" style="background-color: {{ $detail->shift->warna ?? '#e2e8f0' }}">
                                {{ $detail->shift->kode }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700">
                                LIBUR
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex flex-col gap-1 text-sm text-gray-600">
                        @if($detail->shift_id)
                            <div class="flex justify-between">
                                <span>Jam Masuk:</span>
                                <span>{{ \Carbon\Carbon::parse($detail->shift->jam_masuk)->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Jam Keluar:</span>
                                <span>{{ \Carbon\Carbon::parse($detail->shift->jam_keluar)->format('H:i') }}</span>
                            </div>
                        @else
                            <div class="text-center py-2 text-gray-500 italic">
                                Hari Libur Terjadwal
                            </div>
                        @endif
                        <div class="flex justify-between border-t mt-1 pt-1">
                            <span>Status Kehadiran:</span>
                            <x-ts:badge :color="$detail->status_kehadiran->color()" text="{{ $detail->status_kehadiran->nama() }}" xs />
                        </div>
                        @if($detail->shift_id)
                            <div class="flex justify-between text-gray-500">
                                <span>Jam Masuk Aktual:</span>
                                <span>{{ $detail->absen_masuk_at ? \Carbon\Carbon::parse($detail->absen_masuk_at)->format('H:i') : '--:--' }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Jam Keluar Aktual:</span>
                                <span>{{ $detail->absen_keluar_at ? \Carbon\Carbon::parse($detail->absen_keluar_at)->format('H:i') : '--:--' }}</span>
                            </div>
                            @if($detail->catatan)
                            <div class="text-xs text-red-500 mt-1 italic">
                                Catatan: {{ $detail->catatan }}
                            </div>
                            @endif
                            @if($detail->status_kehadiran === \App\Enums\StatusKehadiran::PERLU_VERIFIKASI)
                            <div class="text-xs text-yellow-600 mt-1">
                                Menunggu konfirmasi SDM
                            </div>
                            @endif
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
