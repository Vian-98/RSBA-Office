@php
    $tglDistribusi = Carbon\Carbon::parse($distribusi->tanggal)->translatedFormat('d M Y');

    $keluarAs = $distribusi->dist_as;
    $colorKeluarAs = fn($keluarAs) => match ($keluarAs) {
        'keluar' => 'red',
        'asset' => 'green',
        null => 'base',
    };
    $colorKeluarAs = $colorKeluarAs($keluarAs);

    // label
    $labelKeluar = fn($keluarAs) => match ($keluarAs) {
        'keluar' => 'Pengeluaran',
        'asset' => 'Sebagai Aset',
        null => '-',
    };
    $labelKeluar = $labelKeluar($keluarAs);
@endphp

<div class="flex flex-col gap-2">
    <div class="flex flex-col gap-2 rounded border border-gray-200 p-3 text-sm lg:grid lg:grid-cols-3">
        <div class="flex flex-col">
            {{-- <span class="text-xs font-light font-gray-500">ID Transaksi</span> --}}
            <h1 class="text-2xl font-bold uppercase text-gray-500">{{ $distribusi->id }}</h1>

            {{-- footer --}}
            <span class="mt-2 flex flex-row items-center gap-2">
                <span>{{ $tglDistribusi }}</span>
                <x-ts:badge :text="$labelKeluar" :color="$colorKeluarAs" outline xs />
            </span>
        </div>
        <div class="col-span-2 flex w-full flex-col">
            <div class="flex items-center">
                <span class="w-[150px]">Ke</span> : {{ $distribusi->ruangan->nama }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Diterima Oleh</span> : {{ $distribusi->pengirim_nama }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Keterangan</span> : {{ $distribusi->keterangan }}
            </div>
        </div>
    </div>

    <div>
        <x-table-static :$headers :$rows striped :$paginator />
    </div>

</div>
