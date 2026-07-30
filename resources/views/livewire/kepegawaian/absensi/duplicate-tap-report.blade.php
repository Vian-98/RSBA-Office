<div>
    <x-ts:card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Laporan Tap Duplikat (Deduplikasi <= 10 Menit)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if ($importLog)
                            File: <span class="font-medium text-gray-700">{{ $importLog->nama_file }}</span>
                            ({{ \Carbon\Carbon::parse($importLog->periode_awal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($importLog->periode_akhir)->format('d M Y') }})
                        @else
                            Menampilkan seluruh log tap duplikat dari semua sesi import.
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <x-ts:button color="emerald" sm icon="tabler.file-spreadsheet" wire:click="exportCsv">
                        Unduh CSV
                    </x-ts:button>
                    <a href="{{ route('kepegawaian.absensi.import') }}">
                        <x-ts:button outline color="gray" sm icon="tabler.arrow-left">
                            Kembali ke Import
                        </x-ts:button>
                    </a>
                </div>
            </div>
        </x-slot:header>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div>
                <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari Employee ID / Nama..." icon="tabler.search" />
            </div>

            <div>
                <select wire:model.live="logId" class="w-full text-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Semua Log Import --</option>
                    @foreach ($allImportLogs as $log)
                        <option value="{{ $log->id }}">
                            {{ $log->nama_file }} ({{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Employee ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Mentah</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jam Anchor (Referensi)</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jam Dibuang</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Selisih Waktu</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($records as $row)
                        @php
                            $selisih = '-';
                            if ($row->anchor_datetime && $row->discarded_datetime) {
                                $diffSec = abs(strtotime($row->discarded_datetime) - strtotime($row->anchor_datetime));
                                $mins = floor($diffSec / 60);
                                $secs = $diffSec % 60;
                                $selisih = "{$mins} mnt {$secs} dtk";
                            }
                        @endphp
                        <tr class="hover:bg-amber-50/50 transition">
                            <td class="px-4 py-2.5 font-mono font-medium text-gray-900">{{ $row->employee_id }}</td>
                            <td class="px-4 py-2.5 text-gray-800">{{ $row->nama_mentah }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}</td>
                            <td class="px-4 py-2.5 font-mono font-semibold text-emerald-700 bg-emerald-50/50 rounded">{{ $row->jam_anchor ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-mono font-semibold text-rose-700 bg-rose-50/50 rounded">{{ $row->jam_dibuang ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-mono text-amber-700">{{ $selisih }}</td>
                            <td class="px-4 py-2.5 text-gray-500">
                                <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                    {{ $row->discard_reason ?? 'Duplicate tap (<=10 menit)' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                Tidak ada data tap duplikat yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </x-ts:card>
</div>
