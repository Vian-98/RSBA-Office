<div class="flex flex-col gap-3">
    {{-- title --}}


    {{-- asset identitas --}}

    {{-- request maintenance --}}
    <div class="flex flex-col gap-2 text-gray-600">
        <div class="text-lg font-semibold">Permintaan </div>
        <div class="ms-4 flex flex-col gap-2">
            <div class="font-semibold text-indigo-500">Request Number: #{{ $work->jadwal->request->id }}</div>
            <div class="space-y-1 text-sm">
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tanggal</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->jadwal->request->created_at)->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">User Pengaju</span>
                    <span class="ml-2">: {{ $work->jadwal->request->user_request }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $work->jadwal->request->note }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- pengerjaan maintenance --}}
    <div class="flex flex-col gap-2">
        <div class="text-lg font-semibold">Pengerjaan</div>
        @if ($work)
            <div class="ms-4 flex flex-col gap-2 text-sm text-gray-600">
                <div class="font-semibold text-indigo-500">Work Order Number: #{{ $work->id }} </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Teknisi</span>
                    <span class="ml-2">: @php
                        $work->jadwal->teknisi->each(function ($teknisi) {
                            echo "{$teknisi->user->karyawan->nama} [{$teknisi->role}], ";
                        });
                    @endphp </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tgl Mulai</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->mulai)->translatedFormat('d M Y H:i') }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tgl Selesai</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->selesai)->translatedFormat('d M Y H:i') }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Durasi </span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->mulai)->diff(\Carbon\Carbon::parse($work->selesai))->locale('id')->forHumans() }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Diselesaikan</span>
                    <span class="ml-2">: {{ $work->user_selesai }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $work->catatan }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Total Cost</span>
                    <span class="ml-2">: {{ formatRupiah($work->total_biaya, withDecimals: false) }} </span>
                </div>

            </div>

            {{-- daftar item parts / penggunaan BHP --}}
            <div class="ms-4 flex flex-col gap-2">
                <div class="font-semibold text-gray-500">Penggantian Parts / Komponen / Penggunaan BHP</div>
                @if ($work->parts->count() > 0)
                    <ul class="ms-2 list-disc pl-5 text-sm">
                        @foreach ($work->parts as $part)
                            <li class="text-gray-600">{{ $part->barang->nama }} ({{ number_format($part->qty) . $part->barang->satuan->nama }})</li>
                        @endforeach
                    </ul>
                @else
                    <div class="ms-4 text-sm italic text-gray-600">Tidak ada data</div>
                @endif

            </div>

            {{-- Lampiran Dokumentasi & Foto Permintaan --}}
            @php
                $requestLampirans = is_array($work->jadwal->request->lampiran ?? null) 
                    ? $work->jadwal->request->lampiran 
                    : (is_string($work->jadwal->request->lampiran ?? null) ? (json_decode($work->jadwal->request->lampiran, true) ?? []) : []);
                $workDokumentasi = is_array($work->dokumentasi ?? null) 
                    ? $work->dokumentasi 
                    : (is_string($work->dokumentasi ?? null) ? (json_decode($work->dokumentasi, true) ?? []) : []);
                $allLampirans = array_merge($requestLampirans, $workDokumentasi);
            @endphp
            @if (count($allLampirans) > 0)
                <div class="ms-4 flex flex-col gap-2 mt-3">
                    <div class="font-semibold text-gray-500">Lampiran Foto & Dokumentasi</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($allLampirans as $img)
                            @php
                                $imgUrl = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/storage/')) 
                                    ? $img 
                                    : Storage::url($img);
                            @endphp
                            <a href="{{ $imgUrl }}" target="_blank" class="block border rounded-lg overflow-hidden hover:opacity-80 transition">
                                <img src="{{ $imgUrl }}" class="h-28 w-28 object-cover" alt="Dokumentasi Maintenance" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="ms-4 italic text-gray-600">Belum ada pengerjaan.</div>
        @endif

    </div>
</div>
