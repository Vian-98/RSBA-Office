<div class="flex flex-col gap-3">
    <livewire:Asset.Title :assetBarang="$assetBarang" :key="'title' . $assetBarang->id" />

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
        <div class="rounded-md border border-gray-200 p-2">
            <span class="text-sm italic text-indigo-400">Detail Asset</span>
            <div class="mt-2 flex flex-col gap-1 border-t border-gray-200 pt-2">
                <span class="font-semibold">{{ $assetBarang->barang->nama }}</span>
                <span class="text-sm text-gray-500">Kode : {{ $assetBarang->kode }}</span>
                <span class="text-sm text-gray-500">Kategori : {{ $assetBarang->barang->kategori->nama ?? '-' }}</span>
                <span class="text-sm text-gray-500">Lokasi : {{ $assetBarang->ruangan->nama ?? '-' }}</span>
                <span class="text-sm text-gray-500">Nilai Saat Diterima : {{ $assetBarang->nilai ?? '-' }}</span>
                <span class="text-sm text-gray-500">Status : {{ Str::ucfirst($assetBarang->status) }}</span>
                <span class="text-sm text-gray-500">Diterima Pada : {{ Carbon\Carbon::parse($assetBarang->tanggal_catat)->locale('ID')->translatedFormat('d M Y') }}</span>
            </div>
        </div>
        <div class="rounded-md border border-gray-200 p-2">
            <span class="text-sm italic text-indigo-400">Spesifikasi Assets</span>
            <div class="mt-2 flex flex-col gap-1 border-t border-gray-200 pt-2">
                @foreach ($assetBarang->specs as $spec)
                    <span class="text-sm text-gray-500">{{ $spec->label }} : {{ $spec->value }}</span>
                @endforeach
                @if ($assetBarang->specs->isEmpty())
                    <span class="text-sm text-gray-500">Tidak ada spesifikasi.</span>
                @endif
            </div>


        </div>

        <div class="rounded-md border border-gray-200 p-2">
            @php
                $activeComponents = $assetBarang->components->filter(fn($c) => in_array(strtolower($c->status), ['baik', 'diperbaiki']));
                $inactiveComponents = $assetBarang->components->filter(fn($c) => !in_array(strtolower($c->status), ['baik', 'diperbaiki']));
            @endphp

            <span class="text-sm font-semibold italic text-indigo-500">Komponen-komponen Aktif</span>
            <div class="mt-2 flex flex-col gap-2 border-t border-gray-200 pt-2">
                @if ($activeComponents->isNotEmpty())
                    @foreach ($activeComponents as $komponen)
                        <div class="rounded bg-gray-50 p-2 border border-gray-100">
                            <span class="text-sm font-semibold text-gray-700">{{ $komponen->barang->nama }}</span>
                            <div class="ms-2 flex flex-col gap-1 text-xs mt-1">
                                <span class="text-gray-500">Kode: {{ $komponen->kode ?? '-' }}</span>
                                <span class="text-gray-500">Status: <span class="font-medium text-emerald-600">{{ Str::ucfirst($komponen->status) }}</span></span>
                                <span class="text-gray-500">Nilai: Rp {{ number_format($komponen->nilai ?? 0, 0, ',', '.') }}</span>

                                @if ($komponen->specs->isNotEmpty())
                                    <span class="text-gray-500 font-medium mt-1">Spesifikasi :</span>
                                    <span class="ms-2 flex flex-col gap-0.5 text-xs text-gray-500">
                                        @foreach ($komponen->specs as $spec)
                                            <span>• {{ $spec->label }} : {{ $spec->value }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <span class="text-sm text-gray-400">Tidak ada komponen aktif.</span>
                @endif
            </div>

            {{-- Bagian Komponen Rusak / Tidak Digunakan --}}
            <div class="mt-4 border-t border-gray-200 pt-3">
                <span class="text-sm font-semibold italic text-rose-500 flex items-center gap-1">
                    <x-tabler-alert-triangle class="size-4 text-rose-500 inline" />
                    Komponen Rusak / Tidak Digunakan
                </span>
                <div class="mt-2 flex flex-col gap-2">
                    @if ($inactiveComponents->isNotEmpty())
                        @foreach ($inactiveComponents as $komponen)
                            <div class="rounded bg-rose-50/60 p-2 border border-rose-100">
                                <span class="text-sm font-semibold text-gray-800">{{ $komponen->barang->nama }}</span>
                                <div class="ms-2 flex flex-col gap-1 text-xs mt-1">
                                    <span class="text-gray-600">Kode: {{ $komponen->kode ?? '-' }}</span>
                                    <span class="text-gray-600">Status: <span class="font-semibold text-rose-600">{{ Str::ucfirst($komponen->status) }}</span></span>
                                    @if ($komponen->keterangan)
                                        <span class="text-gray-600 italic">Keterangan: {{ $komponen->keterangan }}</span>
                                    @endif
                                    @if ($komponen->specs->isNotEmpty())
                                        <span class="text-gray-600 font-medium mt-0.5">Spesifikasi :</span>
                                        <span class="ms-2 flex flex-col gap-0.5 text-xs text-gray-500">
                                            @foreach ($komponen->specs as $spec)
                                                <span>• {{ $spec->label }} : {{ $spec->value }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <span class="text-sm text-gray-400">Tidak ada riwayat komponen rusak / bekas.</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
