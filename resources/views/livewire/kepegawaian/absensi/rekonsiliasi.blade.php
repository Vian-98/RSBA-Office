<div>
    <x-ts:card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold">Rekonsiliasi Data Absensi</h2>
                    <p class="text-sm text-gray-500 break-words">File: {{ $log->nama_file }} | Periode: {{ \Carbon\Carbon::parse($log->periode_awal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($log->periode_akhir)->format('d M Y') }}</p>
                </div>
                <div class="flex-shrink-0">
                    <x-ts:button color="primary" wire:click="commitKeJadwal" loading="commitKeJadwal">Commit ke Jadwal</x-ts:button>
                </div>
            </div>
        </x-slot:header>

        <!-- Loading Alert Banner -->
        <div wire:loading wire:target="commitKeJadwal" class="mb-6">
            <x-ts:alert color="primary" light>
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-indigo-900">Sedang Memproses Rekonsiliasi Absensi...</p>
                        <p class="text-xs text-indigo-700 mt-0.5">Sistem sedang menghitung status kehadiran, keterlambatan, dan mencatat jam absen finger karyawan ke Jadwal Kerja.</p>
                    </div>
                </div>
            </x-ts:alert>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Total Baris</p>
                <p class="text-xl font-bold">{{ $log->total_baris }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Matched</p>
                <p class="text-xl font-bold text-green-600">{{ $log->baris_matched }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Unmatched / Ambiguous</p>
                <p class="text-xl font-bold text-red-600">{{ $log->baris_unmatched }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Anomali</p>
                <p class="text-xl font-bold text-yellow-600">{{ $log->baris_anomali }}</p>
            </div>
        </div>

        <div class="flex gap-4 mb-4">
            <div class="w-1/3">
                <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari ID/Nama mentah..." icon="magnifying-glass" />
            </div>
            <div class="w-1/4">
                <x-ts:select.styled wire:model.live="filterStatus" :options="[
                    ['label' => 'Semua Status', 'value' => 'all'],
                    ['label' => 'Matched', 'value' => 'matched'],
                    ['label' => 'Unmatched', 'value' => 'unmatched'],
                    ['label' => 'Ambiguous', 'value' => 'ambiguous'],
                    ['label' => 'Diabaikan', 'value' => 'diabaikan'],
                ]" select="label:label|value:value" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">ID Mesin</th>
                        <th class="px-4 py-3">Nama Mesin</th>
                        <th class="px-4 py-3">Jam (In - Out)</th>
                        <th class="px-4 py-3">Status Matching</th>
                        <th class="px-4 py-3">Karyawan Ditautkan</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stagings as $staging)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($staging->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-mono">{{ $staging->employee_id_mentah }}</td>
                        <td class="px-4 py-3">{{ $staging->nama_mentah }}</td>
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ $staging->clock_in_aktual ?? '--:--' }}</span> - 
                            <span class="font-semibold">{{ $staging->clock_out_aktual ?? '--:--' }}</span>
                            @if($staging->catatan_mesin)
                            <div class="text-xs text-red-500 mt-1">{{ $staging->catatan_mesin }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($staging->status_matching === 'matched')
                                <x-ts:badge color="success" text="Matched" />
                            @elseif($staging->status_matching === 'unmatched')
                                <x-ts:badge color="danger" text="Unmatched" />
                            @elseif($staging->status_matching === 'ambiguous')
                                <x-ts:badge color="warning" text="Ambiguous" />
                            @else
                                <x-ts:badge color="gray" text="Diabaikan" />
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($staging->status_matching === 'matched' && $staging->karyawan)
                                <div class="font-medium text-gray-900">{{ $staging->karyawan->nama }}</div>
                                <div class="text-xs">{{ $staging->karyawan->nip }}</div>
                            @else
                                <span class="text-gray-400 italic">Belum ditautkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(in_array($staging->status_matching, ['unmatched', 'ambiguous']))
                                <div x-data="{ open: false }" class="relative">
                                    <x-ts:button color="primary" variant="light" size="sm" x-on:click="open = !open">Tautkan</x-ts:button>
                                    <div x-show="open" x-on:click.away="open = false" class="absolute z-10 right-0 mt-2 w-64 bg-white border rounded shadow-lg p-2">
                                        <div class="mb-2">
                                            <p class="text-xs font-semibold mb-1">Pilih Karyawan:</p>
                                            <select class="w-full text-sm border-gray-300 rounded" x-ref="selectKaryawan">
                                                <option value="">-- Pilih --</option>
                                                @foreach($karyawans as $k)
                                                    <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->nip }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex justify-between">
                                            <x-ts:button color="gray" size="sm" wire:click="abaikanBaris({{ $staging->id }})" x-on:click="open = false">Abaikan</x-ts:button>
                                            <x-ts:button color="primary" size="sm" x-on:click="$wire.tautkanManual({{ $staging->id }}, $refs.selectKaryawan.value); open = false">Simpan</x-ts:button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $stagings->links() }}
        </div>
    </x-ts:card>
</div>
