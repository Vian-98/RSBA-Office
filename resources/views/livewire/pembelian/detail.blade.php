<div id="modal-detail-pembelian" class="flex flex-col gap-2">
    <div class="flex w-full flex-col rounded-md border border-gray-200 p-3 lg:grid lg:grid-cols-2">
        <div class="flex flex-col">
            <span class="font-gray-500 text-xs font-light">No. Transaksi</span>
            <h1 class="text-2xl font-bold uppercase text-gray-500">{{ $pembelian?->no ?? 'Tidak Ditemukan' }}</h1>
            <span class="mt-2 flex flex-row gap-2 text-[0.45rem]">
                @php
                    $status = $pembelian->status;
                    $statusBeli = fn(string $status): string => match ($status) {
                        'waiting' => 'amber',
                        'selesai' => 'green',
                        'dibatalkan' => 'red',
                        default => 'secondary',
                    };
                    $badgeStatus = $statusBeli($status);
                @endphp
                <x-ts:badge :text="ucwords($pembelian->status)" :color="$badgeStatus" outline xs />


                {{-- pembayaran --}}
                @php
                    $pembayaran = $pembelian?->status_pembayaran ?? null;
                    $statusPembayaran = fn($pembayaran) => match ($pembayaran) {
                        'lunas' => 'green',
                        'tempo' => 'amber',
                        null => 'red',
                    };
                    $badgePembayaran = $statusPembayaran($pembayaran);
                @endphp
                <x-ts:badge :text="$pembelian?->status_pembayaran ? ucfirst($pembelian->status_pembayaran) : 'Belum Dibayar'" :color="$badgePembayaran" outline xs />
            </span>
        </div>
        <div class="flex flex-col text-sm">
            <div class="flex items-center">
                <span class="w-[150px]">Supplier</span> : {{ $pembelian?->supplier->nama }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Tanggal Beli</span> :
                {{ Carbon\Carbon::parse($pembelian?->tgl)->translatedFormat('d M Y') }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Jenis</span> :
                {{ $pembelian->jenis }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Lampiran</span> :
                @if (count($pembelian?->lampirans ?? []))
                    <span x-on:click="$dispatch('open-modal',{id:'modal-view-lampiran-pembelian'})" class="rounded-md px-1 hover:cursor-pointer hover:bg-indigo-100 hover:text-indigo-500">
                        {{ count($pembelian?->lampirans ?? []) }}
                        File
                    </span>
                @else
                    <span class="ml-2 italic text-gray-500">Tidak ada lampiran</span>
                @endif

            </div>
        </div>

        {{-- modal view lampiran --}}
        <x-filament::modal id="modal-view-lampiran-pembelian" width="w-full" :close-by-escaping="true" :close-button="true">
            <x-slot:heading></x-slot:heading>
            <livewire:Pembelian.ViewLampiran :lampirans="$pembelian->lampirans" />
        </x-filament::modal>
        {{-- end modal view lampiran --}}
    </div>

    <div class="scrollbar-hidden w-full overflow-x-auto rounded-md border border-gray-200 p-2">

        {{-- table --}}
        <table class="border-collapses w-full min-w-full table-auto">
            <colgroup>
                <col style="width:5%">
                <col style="width:15%">
                <col style="width:8%">
                <col style="width:8%">
                <col style="width:8%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:12%">
                <col style="width:13%">
            </colgroup>

            <thead class="border-b border-double border-gray-300 text-left text-xs font-thin capitalize text-gray-600">
                <tr>
                    <th class="p-2"></th>
                    <th class="p-2">Barang</th>
                    <th class="p-2">Satuan</th>
                    <th class="p-2">Jumlah Pesan</th>
                    <th class="p-2">Diterima</th>
                    <th class="p-2">Harga Satuan</th>
                    <th class="p-2">Diskon</th>
                    <th class="p-2">PPN</th>
                    <th class="p-2">Oleh</th>
                </tr>
            </thead>

            <div class="overflow-y-auto">
                <tbody>
                    @foreach ($pembelian->details as $itemBeli)
                        @php
                            $rowClass = 'bg-gray-200/25';
                            $iconStatus = 'checks';
                            $iconColor = 'green';
                            if ((int) $itemBeli->terimas->sum('jumlah') === 0) {
                                $rowClass = 'bg-red-200/25';
                                $iconStatus = 'hourglass-high'; //circle-dashed
                                $iconColor = 'red';
                            } elseif ((int) $itemBeli->jumlah > (int) $itemBeli->terimas->sum('jumlah')) {
                                $rowClass = $rowClass = 'bg-orange-200/25';
                                $iconStatus = 'progress-check';
                                $iconColor = 'orange';
                            }
                        @endphp
                        <tr class="{{ $rowClass }} text-sm">
                            <td class="px-2 py-1 opacity-50">
                                <x-ts:icon :name="'tabler.' . $iconStatus" :color="$iconColor" class="h-5 w-auto" />
                            </td>
                            <td class="px-2 py-1">{{ $itemBeli->barang->nama }}</td>
                            <td class="px-2 py-1">{{ $itemBeli->barang->satuan->nama }}</td>
                            <td class="px-2 py-1">{{ $itemBeli->jumlah }}</td>
                            <td class="px-2 py-1">{{ $itemBeli->terimas->sum('jumlah') }}</td>
                            <td class="px-2 py-1">{{ formatRupiah($itemBeli->harga_satuan, $withPrefix = false, $withDecimals = false) }}</td>
                            <td class="px-2 py-1">{{ formatRupiah($itemBeli->diskon, $withPrefix = false, $withDecimals = false) }}</td>
                            <td class="px-2 py-1"> ({{ $itemBeli->ppn }}%)
                                {{ formatRupiah(($itemBeli->harga_satuan * $itemBeli->jumlah - $itemBeli->diskon) * ($itemBeli->ppn / 100), $withPrefix = false, $withDecimals = false) }}
                            </td>
                            <td class="px-2 py-1">{{ Str::limit($itemBeli->terimas->last()?->penerimaan?->user?->karyawan?->nama, 10, '...') }} </td>
                        </tr>
                    @endforeach
                </tbody>
            </div>
        </table>
        {{-- end table --}}

    </div>
    {{-- summary cart --}}
    <div class="grid w-full rounded-md border border-gray-200 px-4 py-2">
        <div class="col-span-2 flex w-full flex-col lg:col-span-1">
            <div class="flex flex-col justify-end text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="text-gray-800">{{ formatRupiah($pembelian->subtotal) }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Diskon</span>
                    <span class="text-red-500">{{ formatRupiah($pembelian->total_diskon) }}</span>
                </div>

                <div class="flex justify-between border-t-2 border-dashed border-gray-200 font-semibold">
                    <span class="text-gray-600">Subtotal setelah diskon</span>
                    <span class="text-gray-800">{{ formatRupiah($pembelian->subtotal - $pembelian->total_diskon) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">PPN</span>
                    <span class="text-gray-800">{{ formatRupiah($pembelian->total_ppn) }}</span>
                </div>
                <div class="flex justify-between border-t-2 border-dashed border-indigo-200 pt-1">
                    <span class="text-base font-bold text-indigo-700">Total Pembayaran</span>
                    <span class="text-xl font-bold text-indigo-600">{{ formatRupiah($pembelian->total) }} </span>
                </div>
            </div>
        </div>
    </div>

    <div id="footer-detail-pembelian">

    </div>
    {{-- <div class="mt-4 flex justify-end">
        <x-ts:button sm color="red" x-on:click="$dispatch('tutup-detail-beli')">Tutup</x-ts:button>
    </div> --}}

</div>
