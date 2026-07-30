<div class="flex flex-col gap-2">
    <div class="flex flex-col lg:grid lg:grid-cols-2 gap-2 border border-gray-200 border-s-4 border-s-indigo-500 px-4 py-2">
        {{-- @dd($stoks) --}}
        <div class="flex flex-col text-left">
            <label>
                Stok Id :
                <span class="text-indigo-500 font-semibold">
                    &nbsp; {{ $stoks?->id }}
                </span>
            </label>
            <label>
                No. Transaksi Beli :
                <span class="text-indigo-500 font-semibold">
                    &nbsp; {{ $stoks?->penerimaanDet?->pembelianDet?->pembelian?->no }}
                </span>
            </label>
        </div>
        <div class="flex flex-col text-left">
            <label>
                Tgl Masuk :
                <span class="text-indigo-500 font-semibold">
                    &nbsp; {{ $stoks?->penerimaanDet?->penerimaan?->tanggal }}
                </span>
            </label>
            <label>
                Jumlah Masuk :
                <span class="text-indigo-500 font-semibold">
                    &nbsp; {{ $stoks?->penerimaanDet?->jumlah }}
                </span>
            </label>
        </div>
    </div>

    <table class="min-w-full table-fixed border-collapses overflow-y-auto overflow-x-auto">
        <thead>
            <tr class="text-left text-sm text-gray-600 border-b">
                <th class="py-2 px-4">Tanggal</th>
                <th class="py-2 px-4">Ke</th>
                <th class="py-2 px-4">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distribusiDetail as $index => $item)
                <tr class="text-left text-sm text-gray-600 border-b even:bg-gray-200/25" :key="{{ $index }}">
                    <td class="py-2 px-4">{{ $item->distribusi->tanggal }}</td>
                    <td class="py-2 px-4">{{ $item->distribusi->ruangan->nama }}</td>
                    <td class="py-2 px-4">{{ $item->jml }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-2 px-4 text-center italic text-gray-400">
                        Belum ada distribusi untuk stok ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-4 py-3 text-sm">
        {{ $distribusiDetail->links() }}
    </div>
</div>
