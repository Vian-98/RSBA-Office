<div class="flex flex-col gap-2 rounded-md border border-gray-200 p-2">
    <div class="grid grid-cols-2 gap-3">
        <div class="flex flex-col text-xl text-indigo-500">
            {{-- <span class="text-sm italic text-gray-400">Asset :</span> --}}
            {{ $assetBarang->kode }}
            <span class="text-lg font-bold text-indigo-500">{{ $assetBarang->barang->nama }}</span>
            <div class="flex flex-row gap-3 text-sm font-light text-gray-400">
                <span>Kategori : {{ $assetBarang->barang->kategori->nama }}, </span> Di Ruangan : {{ $assetBarang->ruangan->nama }}
            </div>
        </div>


        {{-- komponen assets list --}}
        <div class="flex flex-col">
            @if ($assetBarang->child->count() > 0)
                <div class="flex flex-col gap-1">
                    <span class="text-sm italic text-gray-400">Komponen : </span>
                    <ul class="list-disc pl-5 text-sm font-light text-gray-400">
                        @foreach ($assetBarang->child as $component)
                            <li>{{ $component->barang->nama }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

</div>
