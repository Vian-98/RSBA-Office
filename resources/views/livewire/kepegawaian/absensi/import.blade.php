<div>
    <x-ts:card header="Import Data Absensi">
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Format File Absensi</label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none {{ $formatFile === 'punch_csv' ? 'border-indigo-600 ring-2 ring-indigo-600 bg-indigo-50/30' : 'border-gray-300 bg-white' }}">
                    <input type="radio" wire:model.live="formatFile" value="punch_csv" class="sr-only" />
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-semibold text-gray-900">CSV Raw Punch Log (Mesin Absensi)</span>
                            <span class="mt-1 flex items-center text-xs text-gray-500">Format CSV dari mesin absensi (1 tap = 1 baris). Otomatis deduplikasi & pairing.</span>
                        </span>
                    </span>
                    <x-ts:icon name="tabler.clock-play" class="h-6 w-6 text-indigo-600 shrink-0" />
                </label>

                <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none {{ $formatFile === 'excel' ? 'border-indigo-600 ring-2 ring-indigo-600 bg-indigo-50/30' : 'border-gray-300 bg-white' }}">
                    <input type="radio" wire:model.live="formatFile" value="excel" class="sr-only" />
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-semibold text-gray-900">Excel Rekapitulasi (Format Lama)</span>
                            <span class="mt-1 flex items-center text-xs text-gray-500">Format Excel (.xls/.xlsx) yang sudah memiliki kolom check-in & check-out terpisah.</span>
                        </span>
                    </span>
                    <x-ts:icon name="tabler.file-spreadsheet" class="h-6 w-6 text-emerald-600 shrink-0" />
                </label>
            </div>
        </div>

        <div class="mb-4">
            @if ($formatFile === 'punch_csv')
                <x-ts:input type="file" wire:model="file" label="Pilih File CSV Raw Punch Log" hint="Format .csv atau .txt dari mesin absensi. Maksimal 10MB." />
            @else
                <x-ts:input type="file" wire:model="file" label="Pilih File Excel Absensi" hint="Format .xls atau .xlsx. Maksimal 10MB." />
            @endif
        </div>

        <div wire:loading wire:target="file" class="mt-4 w-full">
            <x-ts:alert text="Sedang membaca dan memproses clearing file absensi..." color="info" />
        </div>

        @if ($previewData)
            <div class="mt-6 border rounded-xl p-5 bg-gray-50 shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-base font-semibold text-gray-800">
                        Preview & Clearing Engine Result
                        <span class="ml-2 inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                            {{ $previewData['format'] === 'punch_csv' ? 'Format CSV Fingerprint' : 'Format Excel' }}
                        </span>
                    </h3>

                    @if ($previewData['format'] === 'punch_csv' && !empty($previewData['import_log_id']))
                        <a href="{{ route('kepegawaian.absensi.duplicate-report', ['logId' => $previewData['import_log_id']]) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg px-3 py-1.5 transition">
                            <x-ts:icon name="tabler.copy-x" class="w-4 h-4 text-amber-600" />
                            Lihat Laporan Tap Duplikat ({{ $previewData['total_duplikat'] }})
                        </a>
                    @endif
                </div>

                @if ($previewData['format'] === 'punch_csv')
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Periode Absensi</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">
                                {{ \Carbon\Carbon::parse($previewData['periode_awal'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($previewData['periode_akhir'])->format('d M Y') }}
                            </p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Total Tap (Raw Log)</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">{{ number_format($previewData['total_tap']) }} tap</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Hasil Pairing Hari</p>
                            <p class="text-sm font-semibold text-indigo-600 mt-1">{{ number_format($previewData['total_hari']) }} hari kerja</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Tap Duplikat (≤10 mnt)</p>
                            <p class="text-sm font-semibold text-amber-600 mt-1">{{ number_format($previewData['total_duplikat']) }} tap dibuang</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Anomali Terdeteksi</p>
                            <p class="text-sm font-semibold {{ $previewData['anomali'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                                {{ number_format($previewData['anomali']) }} rekaman
                            </p>
                        </div>
                    </div>

                    @if (!empty($previewData['anomalies']))
                        <div class="mt-5">
                            <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Sampel Baris Anomali (Memerlukan Rekonsiliasi SDM)</h4>
                            <div class="overflow-x-auto rounded-lg border bg-white">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Employee ID</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Nama Mentah</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Tanggal</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Jam Masuk</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Jam Keluar</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500">Anomali</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($previewData['anomalies'] as $item)
                                            <tr class="hover:bg-amber-50/50">
                                                <td class="px-3 py-2 font-mono font-medium text-gray-800">{{ $item['employee_id'] }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $item['nama_mentah'] }}</td>
                                                <td class="px-3 py-2 text-gray-600">{{ $item['tanggal'] }}</td>
                                                <td class="px-3 py-2 font-mono text-emerald-700">{{ $item['clock_in'] ?? '-' }}</td>
                                                <td class="px-3 py-2 font-mono text-indigo-700">{{ $item['clock_out'] ?? '-' }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                        {{ $item['anomali'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Periode Awal</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($previewData['periode_awal'])->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Periode Akhir</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($previewData['periode_akhir'])->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Baris</p>
                            <p class="font-semibold">{{ $previewData['total_baris'] }} baris</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Anomali Terdeteksi</p>
                            <p class="font-semibold text-{{ $previewData['anomali'] > 0 ? 'red-600' : 'green-600' }}">
                                {{ $previewData['anomali'] }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <x-ts:button color="gray" wire:click="$set('previewData', null)">Batal</x-ts:button>
                    <x-ts:button color="primary" wire:click="prosesImport" wire:loading.attr="disabled">
                        Proses ke Rekonsiliasi SDM
                    </x-ts:button>
                </div>
            </div>
        @endif
    </x-ts:card>
</div>
