<div>
    <x-ts:card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-gray-800">Rekonsiliasi Data Absensi</h2>
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
                        <p class="text-xs text-indigo-700 mt-0.5">Sistem sedang menghitung status kehadiran, keterlambatan, dan mencatat jam absen mesin karyawan ke Jadwal Kerja.</p>
                    </div>
                </div>
            </x-ts:alert>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-xs text-gray-500">Total Baris</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($log->total_baris) }}</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                <p class="text-xs text-emerald-600 font-medium">Matched</p>
                <p class="text-xl font-bold text-emerald-700">{{ number_format($log->baris_matched) }}</p>
            </div>
            <div class="bg-rose-50 p-4 rounded-lg border border-rose-100">
                <p class="text-xs text-rose-600 font-medium">Unmatched / Ambiguous</p>
                <p class="text-xl font-bold text-rose-700">{{ number_format($log->baris_unmatched) }}</p>
            </div>
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                <p class="text-xs text-amber-600 font-medium">Anomali Terdeteksi</p>
                <p class="text-xl font-bold text-amber-700">{{ number_format($log->baris_anomali) }}</p>
            </div>
        </div>

        <!-- Filter Banner Notification -->
        @if ($filterStatus === 'problematic')
            <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-amber-800">
                    <x-ts:icon name="tabler.filter" class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>Mode Otomatis Filter: Menampilkan data <strong>Perlu Rekonsiliasi / Bermasalah</strong> (Unmatched / Anomali).</span>
                </div>
                <button wire:click="$set('filterStatus', 'all')" class="text-xs text-amber-900 underline font-semibold hover:text-indigo-600 transition">
                    Tampilkan Semua Data (Tutup Filter)
                </button>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <div class="w-full sm:w-1/3">
                <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari ID/Nama mentah..." icon="tabler.search" />
            </div>
            <div class="w-full sm:w-1/3">
                <select wire:model.live="filterStatus" class="w-full text-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="problematic">Perlu Rekonsiliasi (Unmatched / Anomali)</option>
                    <option value="all">Semua Status Data</option>
                    <option value="matched">Matched (Berhasil Ditautkan)</option>
                    <option value="unmatched">Unmatched (Belum Ditautkan)</option>
                    <option value="ambiguous">Ambiguous</option>
                    <option value="anomali">Anomali (Single Punch / Extra Punch)</option>
                    <option value="diabaikan">Diabaikan</option>
                </select>

            </div>
            @if ($filterStatus !== 'all')
                <div>
                    <x-ts:button outline color="gray" sm wire:click="$set('filterStatus', 'all')">
                        Tutup Filter
                    </x-ts:button>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
            <table class="w-full text-xs text-left text-gray-600">
                <thead class="text-xs uppercase bg-gray-50 border-b text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">ID Mesin</th>
                        <th class="px-4 py-3">Nama Mesin</th>
                        <th class="px-4 py-3">Jam (In - Out)</th>
                        <th class="px-4 py-3">Status Matching</th>
                        <th class="px-4 py-3">Karyawan Ditautkan</th>
                        <th class="px-4 py-3 text-right">Aksi SDM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($stagings as $staging)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($staging->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-mono font-medium text-gray-900">{{ $staging->employee_id_mentah }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $staging->nama_mentah }}</td>
                        <td class="px-4 py-3">
                            <div class="font-mono">
                                <span class="{{ $staging->clock_in_aktual ? 'text-emerald-700 font-semibold' : 'text-gray-400' }}">{{ $staging->clock_in_aktual ?? '--:--' }}</span>
                                <span class="text-gray-400 mx-1">-</span>
                                <span class="{{ $staging->clock_out_aktual ? 'text-indigo-700 font-semibold' : 'text-gray-400' }}">{{ $staging->clock_out_aktual ?? '--:--' }}</span>
                            </div>
                            @if($staging->catatan_mesin)
                            <div class="text-xs text-amber-700 font-medium mt-1 inline-flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                <x-ts:icon name="tabler.alert-circle" class="w-3.5 h-3.5 text-amber-600" />
                                {{ $staging->catatan_mesin }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($staging->status_matching === 'matched')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <x-ts:icon name="tabler.check" class="w-3 h-3 text-emerald-600" /> Matched
                                </span>
                            @elseif($staging->status_matching === 'unmatched')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                    <x-ts:icon name="tabler.x" class="w-3 h-3 text-rose-600" /> Unmatched
                                </span>
                            @elseif($staging->status_matching === 'ambiguous')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <x-ts:icon name="tabler.help" class="w-3 h-3 text-amber-600" /> Ambiguous
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    Diabaikan
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($staging->status_matching === 'matched' && $staging->karyawan)
                                <div class="font-medium text-gray-900">{{ $staging->karyawan->nama }}</div>
                                <div class="text-xs text-gray-500 font-mono">NIP: {{ $staging->karyawan->nip }}</div>
                            @else
                                <span class="text-gray-400 italic">Belum ditautkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <x-ts:button color="indigo" outline size="xs" icon="tabler.pencil" wire:click="openEditModal({{ $staging->id }})">
                                    Revisi Jam
                                </x-ts:button>

                                @if(in_array($staging->status_matching, ['unmatched', 'ambiguous']))
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <x-ts:button color="primary" variant="light" size="xs" x-on:click="open = !open">
                                            Tautkan
                                        </x-ts:button>
                                        <div x-show="open" x-on:click.away="open = false" class="absolute z-10 right-0 mt-2 w-64 bg-white border rounded-lg shadow-xl p-3 text-left">
                                            <div class="mb-2">
                                                <p class="text-xs font-semibold text-gray-700 mb-1">Pilih Karyawan:</p>
                                                <select class="w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500" x-ref="selectKaryawan">
                                                    <option value="">-- Pilih Karyawan --</option>
                                                    @foreach($karyawans as $k)
                                                        <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->nip }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex justify-between mt-3">
                                                <x-ts:button color="gray" size="xs" wire:click="abaikanBaris({{ $staging->id }})" x-on:click="open = false">Abaikan</x-ts:button>
                                                <x-ts:button color="primary" size="xs" x-on:click="$wire.tautkanManual({{ $staging->id }}, $refs.selectKaryawan.value); open = false">Simpan</x-ts:button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada data absensi yang sesuai dengan filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $stagings->links() }}
        </div>
    </x-ts:card>

    <!-- Modal Revisi Jam / Tautan SDM -->
    <x-ts:modal id="modal-revisi-absensi" title="Revisi Data Absensi Karyawan" size="md">
        <form wire:submit.prevent="simpanRevisi">
            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg border text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-gray-500">Nama Mesin:</span>
                            <p class="font-semibold text-gray-800">{{ $editNamaMentah }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Tanggal:</span>
                            <p class="font-semibold text-gray-800">{{ $editTanggal }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tautkan ke Karyawan System</label>
                    <select wire:model="editKaryawanId" class="w-full text-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Belum Ditautkan --</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-ts:input wire:model="editClockIn" label="Jam Masuk (Format HH:mm)" placeholder="07:00" hint="Contoh: 07:15" />
                    </div>
                    <div>
                        <x-ts:input wire:model="editClockOut" label="Jam Keluar (Format HH:mm)" placeholder="15:00" hint="Contoh: 15:30" />
                    </div>
                </div>

                <div>
                    <x-ts:input wire:model="editCatatan" label="Catatan / Alasan Revisi" placeholder="Direvisi manual SDM" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                <x-ts:button outline color="gray" sm type="button" x-on:click="$dispatch('close-modal', {id: 'modal-revisi-absensi'})">Batal</x-ts:button>
                <x-ts:button color="primary" sm type="submit" loading="simpanRevisi">Simpan Revisi</x-ts:button>
            </div>
        </form>
    </x-ts:modal>
</div>
