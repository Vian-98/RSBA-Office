<div class="flex flex-col gap-2 rounded-md border border-gray-200 p-2">
    @if ($assetBarang)
        <div class="grid grid-cols-2 gap-3">
            <div class="flex flex-col text-xl text-indigo-500">
                {{ $assetBarang->kode }}
                <span class="text-lg font-bold text-indigo-500">{{ $assetBarang->barang?->nama ?? '-' }}</span>
                <div class="flex flex-row gap-3 text-sm font-light text-gray-400">
                    <span>Kategori : {{ $assetBarang->barang?->kategori?->nama ?? '-' }}, </span> Di Ruangan : {{ $assetBarang->ruangan?->nama ?? '-' }}
                </div>
            </div>

            {{-- komponen assets list --}}
            <div class="flex flex-col">
                @if ($assetBarang->child && $assetBarang->child->count() > 0)
                    <div class="flex flex-col gap-1">
                        <span class="text-sm italic text-gray-400">Komponen : </span>
                        <ul class="list-disc pl-5 text-sm font-light text-gray-400">
                            @foreach ($assetBarang->child as $component)
                                <li>{{ $component->barang?->nama ?? '-' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="flex flex-col gap-1 p-1">
            <span class="px-2 py-0.5 w-fit rounded text-xs font-bold bg-amber-100 text-amber-800">NON-ASET / FASILITAS UMUM</span>
            <span class="text-sm font-medium text-slate-700">Pemeliharaan / Perbaikan Fasilitas Umum</span>
        </div>
    @endif
</div>
