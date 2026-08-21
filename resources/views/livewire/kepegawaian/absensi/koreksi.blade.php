<div class="space-y-4">
    {{-- Header & Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100">
                    <x-ts:icon name="tabler.pencil-check" class="w-5 h-5 text-indigo-500" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800">Koreksi Absensi</h3>
                    <p class="text-xs text-gray-500">Perbaiki data absensi per karyawan yang membutuhkan verifikasi</p>
                </div>
            </div>
            @if($totalPerluVerifikasi > 0)
                <span class="flex items-center gap-1.5 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-400 animate-pulse"></span>
                    {{ $totalPerluVerifikasi }} rekaman perlu verifikasi
                </span>
            @else
                <span class="flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    <x-ts:icon name="tabler.circle-check" class="w-3.5 h-3.5" />
                    Semua absensi sudah terverifikasi
                </span>
            @endif
        </div>

        {{-- Filter Bar --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
            <div>
                <x-ts:select.styled 
                    label="Mode" 
                    wire:model.live="mode" 
                    :options="[
                        ['label' => 'Bulanan', 'value' => 'bulanan'],
                        ['label' => 'Harian', 'value' => 'harian'],
                    ]"
                    select="label:label|value:value"
                />
            </div>

            @if($mode === 'bulanan')
                <div>
                    <x-ts:select.styled 
                        label="Bulan" 
                        wire:model.live="bulan" 
                        :options="[
                            ['label' => 'Januari', 'value' => 1],
                            ['label' => 'Februari', 'value' => 2],
                            ['label' => 'Maret', 'value' => 3],
                            ['label' => 'April', 'value' => 4],
                            ['label' => 'Mei', 'value' => 5],
                            ['label' => 'Juni', 'value' => 6],
                            ['label' => 'Juli', 'value' => 7],
                            ['label' => 'Agustus', 'value' => 8],
                            ['label' => 'September', 'value' => 9],
                            ['label' => 'Oktober', 'value' => 10],
                            ['label' => 'November', 'value' => 11],
                            ['label' => 'Desember', 'value' => 12],
                        ]"
                        select="label:label|value:value"
                    />
                </div>
                <div>
                    <x-ts:select.styled 
                        label="Tahun" 
                        wire:model.live="tahun" 
                        :options="[
                            ['label' => '2025', 'value' => 2025],
                            ['label' => '2026', 'value' => 2026],
                            ['label' => '2027', 'value' => 2027],
                        ]"
                        select="label:label|value:value"
                    />
                </div>
            @else
                <div class="col-span-2">
                    <x-ts:input type="date" label="Tanggal" wire:model.live="tanggal_spesifik" />
                </div>
            @endif

            <div>
                <x-ts:select.styled 
                    label="Ruangan" 
                    wire:model.live="ruangan_id" 
                    :options="$ruangans->map(fn($r) => ['label' => $r->nama, 'value' => $r->id])->toArray()"
                    select="label:label|value:value"
                    placeholder="Semua"
                    searchable
                />
            </div>
            <div>
                <x-ts:select.styled 
                    label="Karyawan" 
                    wire:model.live="karyawan_id" 
                    :options="$karyawans->map(fn($k) => ['label' => $k->full_nama, 'value' => $k->id])->toArray()"
                    select="label:label|value:value"
                    placeholder="Semua"
                    searchable
                />
            </div>
            <div>
                <x-ts:select.styled 
                    label="Filter Status" 
                    wire:model.live="filter_status" 
                    :options="[
                        ['label' => 'Semua Status', 'value' => ''],
                        ['label' => 'Perlu Verifikasi', 'value' => 'perlu_verifikasi'],
                        ['label' => 'Hadir', 'value' => 'hadir'],
                        ['label' => 'Terlambat', 'value' => 'terlambat'],
                        ['label' => 'Pulang Cepat', 'value' => 'pulang_cepat'],
                        ['label' => 'Tidak Hadir', 'value' => 'tidak_hadir'],
                        ['label' => 'Cuti', 'value' => 'cuti'],
                        ['label' => 'Izin', 'value' => 'izin'],
                    ]"
                    select="label:label|value:value"
                />
            </div>
        </div>
    </div>

    {{-- Karyawan Cards Grid --}}
    @if(count($rekapKaryawan) === 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <x-ts:icon name="tabler.circle-check" class="w-14 h-14 mx-auto mb-3 text-green-300" />
            <h4 class="text-base font-semibold text-gray-700 mb-1">Tidak ada data untuk dikoreksi</h4>
            <p class="text-sm text-gray-400">Ubah filter atau pilih bulan/tahun yang lain</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($rekapKaryawan as $kId => $rk)
                @php
                    $karyawan = $rk['karyawan'];
                    $isExpanded = isset($expandedKaryawan[$kId]);
                    $detailRows = $detailRecords[$kId] ?? collect();
                    $needsAttention = $rk['perlu_verifikasi'] > 0 || $rk['tidak_hadir'] > 0;
                @endphp

                <div 
                    class="bg-white rounded-xl shadow-sm border transition-all duration-200 {{ $needsAttention ? 'border-red-200' : 'border-gray-100' }}"
                    wire:key="karyawan-card-{{ $kId }}"
                >
                    {{-- Card Header --}}
                    <button 
                        type="button"
                        wire:click="toggleKaryawan({{ $kId }})"
                        class="w-full text-left p-4 rounded-t-xl {{ $isExpanded ? 'border-b border-gray-100' : '' }}"
                    >
                        <div class="flex items-center gap-3">
                            {{-- Avatar --}}
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm 
                                {{ $needsAttention ? 'bg-red-100 text-red-700' : 'bg-indigo-50 text-indigo-600' }}">
                                {{ strtoupper(substr($karyawan->nama ?? '-', 0, 1)) }}
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900 text-sm truncate">{{ $karyawan->nama ?? '-' }}</span>
                                    @if($needsAttention)
                                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-red-100 text-red-700">
                                            Butuh perhatian
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $karyawan->ruangan?->nama ?? 'Tanpa Ruangan' }}
                                    <span class="mx-1">·</span>PIN: {{ $karyawan->pin_absen ?? '-' }}
                                </div>
                            </div>

                            {{-- Chevron --}}
                            <div class="flex-shrink-0 transition-transform duration-300 {{ $isExpanded ? 'rotate-180' : '' }}">
                                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Mini Stats Row --}}
                        <div class="flex gap-2 mt-3 flex-wrap">
                            @if($rk['hadir'] > 0)
                                <span class="text-[11px] bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded-full font-medium">Hadir: {{ $rk['hadir'] }}</span>
                            @endif
                            @if($rk['terlambat'] > 0)
                                <span class="text-[11px] bg-yellow-50 text-yellow-800 border border-yellow-200 px-2 py-0.5 rounded-full font-medium">Terlambat: {{ $rk['terlambat'] }}</span>
                            @endif
                            @if($rk['pulang_cepat'] > 0)
                                <span class="text-[11px] bg-yellow-50 text-yellow-800 border border-yellow-200 px-2 py-0.5 rounded-full font-medium">Pulang Cepat: {{ $rk['pulang_cepat'] }}</span>
                            @endif
                            @if($rk['tidak_hadir'] > 0)
                                <span class="text-[11px] bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded-full font-medium">Tidak Hadir: {{ $rk['tidak_hadir'] }}</span>
                            @endif
                            @if($rk['cuti'] > 0)
                                <span class="text-[11px] bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full font-medium">Cuti: {{ $rk['cuti'] }}</span>
                            @endif
                            @if($rk['izin'] > 0)
                                <span class="text-[11px] bg-cyan-50 text-cyan-800 border border-cyan-200 px-2 py-0.5 rounded-full font-medium">Izin: {{ $rk['izin'] }}</span>
                            @endif
                            @if($rk['perlu_verifikasi'] > 0)
                                <span class="text-[11px] bg-red-100 text-red-700 border border-red-300 px-2 py-0.5 rounded-full font-bold">Verifikasi: {{ $rk['perlu_verifikasi'] }}</span>
                            @endif
                        </div>
                    </button>

                    {{-- Expanded Detail: daily records --}}
                    @if($isExpanded)
                        <div class="px-4 pb-4 space-y-2">
                            @if($detailRows->isEmpty())
                                <div class="text-center py-6 text-sm text-gray-400">
                                    <x-ts:icon name="tabler.calendar-off" class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                                    Tidak ada data absensi
                                </div>
                            @else
                                @foreach($detailRows as $rec)
                                    @php
                                        $statusColor = match($rec->status_kehadiran?->value ?? '') {
                                            'hadir'                      => 'bg-green-50 border-green-200',
                                            'terlambat', 'pulang_cepat' => 'bg-yellow-50 border-yellow-200',
                                            'tidak_hadir'                => 'bg-red-50 border-red-200',
                                            'cuti', 'izin'              => 'bg-blue-50 border-blue-200',
                                            'perlu_verifikasi'           => 'bg-slate-50 border-slate-300',
                                            default                      => 'bg-gray-50 border-gray-200',
                                        };
                                    @endphp
                                    <div class="rounded-lg border {{ $statusColor }} p-3 flex items-center gap-3" wire:key="rec-{{ $rec->id }}">
                                        {{-- Date --}}
                                        <div class="flex-shrink-0 text-center w-10">
                                            <div class="text-[10px] text-gray-400 uppercase font-semibold leading-none">
                                                {{ \Carbon\Carbon::parse($rec->tanggal)->format('M') }}
                                            </div>
                                            <div class="text-lg font-bold text-gray-800 leading-tight">
                                                {{ \Carbon\Carbon::parse($rec->tanggal)->format('d') }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 leading-none">
                                                {{ \Carbon\Carbon::parse($rec->tanggal)->format('D') }}
                                            </div>
                                        </div>

                                        {{-- Divider --}}
                                        <div class="w-px h-10 bg-gray-200 flex-shrink-0"></div>

                                        {{-- Shift & Times --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($rec->shift)
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded text-slate-800" 
                                                          style="background-color: {{ $rec->shift->warna ?? '#e2e8f0' }}">
                                                        {{ $rec->shift->kode }}
                                                    </span>
                                                    <span class="text-[11px] text-gray-500">
                                                        {{ substr($rec->shift->jam_masuk, 0, 5) }}–{{ substr($rec->shift->jam_keluar, 0, 5) }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">LIBUR</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-[11px]">
                                                @if($rec->absen_masuk_at || $rec->absen_keluar_at)
                                                    <span class="text-indigo-600 font-semibold">
                                                        {{ $rec->absen_masuk_at ? \Carbon\Carbon::parse($rec->absen_masuk_at)->format('H:i') : '--:--' }}
                                                        &rarr;
                                                        {{ $rec->absen_keluar_at ? \Carbon\Carbon::parse($rec->absen_keluar_at)->format('H:i') : '--:--' }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 italic">Tidak ada rekaman mesin</span>
                                                @endif
                                                @if($rec->catatan)
                                                    <span class="ml-2 text-gray-500 truncate max-w-[120px] inline-block align-middle" title="{{ $rec->catatan }}">· {{ $rec->catatan }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Status Badge --}}
                                        <div class="flex-shrink-0">
                                            @if($rec->status_kehadiran)
                                                <x-ts:badge :color="$rec->status_kehadiran->color()" text="{{ $rec->status_kehadiran->nama() }}" xs />
                                            @else
                                                <x-ts:badge color="gray" text="Belum Dicek" xs />
                                            @endif
                                        </div>

                                        {{-- Koreksi Button --}}
                                        <div class="flex-shrink-0">
                                            <button 
                                                type="button"
                                                wire:click="editRecord({{ $rec->id }})"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border transition-colors
                                                    {{ ($rec->status_kehadiran?->value === 'perlu_verifikasi') 
                                                        ? 'bg-red-600 border-red-700 text-white hover:bg-red-700' 
                                                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}"
                                            >
                                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Koreksi
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Koreksi --}}
    <x-ts:modal wire="showEditModal" title="Koreksi Absensi Manual" size="md">
        @if($editingRecordId)
            @php $rec = App\Models\Sdm\JadwalKerjaDetail::with(['karyawan','shift'])->find($editingRecordId); @endphp
            @if($rec)
                {{-- Karyawan Info --}}
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 mb-2">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-sm">
                        {{ strtoupper(substr($rec->karyawan?->nama ?? '-', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">{{ $rec->karyawan?->nama ?? '-' }}</div>
                        <div class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('l, d F Y') }}
                            @if($rec->shift)
                                &middot; Shift {{ $rec->shift->kode }}: {{ substr($rec->shift->jam_masuk,0,5) }}–{{ substr($rec->shift->jam_keluar,0,5) }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 py-2">
                    <x-ts:select.styled 
                        label="Status Kehadiran" 
                        wire:model="editStatus" 
                        :options="[
                            ['label' => 'Belum Dicek', 'value' => 'belum_dicek'],
                            ['label' => 'Hadir (Tepat Waktu)', 'value' => 'hadir'],
                            ['label' => 'Terlambat', 'value' => 'terlambat'],
                            ['label' => 'Pulang Cepat', 'value' => 'pulang_cepat'],
                            ['label' => 'Tidak Hadir (Alfa)', 'value' => 'tidak_hadir'],
                            ['label' => 'Cuti', 'value' => 'cuti'],
                            ['label' => 'Izin', 'value' => 'izin'],
                            ['label' => 'Perlu Verifikasi', 'value' => 'perlu_verifikasi'],
                        ]"
                        select="label:label|value:value"
                    />
                    
                    <div class="grid grid-cols-2 gap-3">
                        <x-ts:input type="datetime-local" label="Jam Masuk Aktual" wire:model="editAbsenMasuk" />
                        <x-ts:input type="datetime-local" label="Jam Keluar Aktual" wire:model="editAbsenKeluar" />
                    </div>
                    
                    <x-ts:input label="Catatan / Alasan Koreksi" wire:model="editCatatan" placeholder="Contoh: Lupa scan mesin absensi, Hadir tugas luar" />
                </div>
                
                <x-slot:footer>
                    <div class="flex justify-end gap-2">
                        <x-ts:button color="gray" variant="flat" wire:click="$set('showEditModal', false)">Batal</x-ts:button>
                        <x-ts:button color="primary" wire:click="saveCorrection">
                            <x-ts:icon name="tabler.device-floppy" class="w-4 h-4 mr-1" />
                            Simpan Koreksi
                        </x-ts:button>
                    </div>
                </x-slot:footer>
            @endif
        @endif
    </x-ts:modal>
</div>
