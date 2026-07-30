<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-4 rounded-md border border-gray-200 px-4 py-2">
        <span class="italic">
            Permintaan
            <span class="text-lg font-semibold text-indigo-500">#{{ $pembelianRequest->id }}</span>
        </span>

        <div class="space-y-1 text-sm">
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">User Pengaju</span>
                <span class="ml-2">: {{ $pembelianRequest->user_request }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                <span class="ml-2">: {{ $pembelianRequest->note }}</span>
            </div>
        </div>

    </div>

    <div class="border-gay-200 flex flex-col gap-2 rounded-md border px-4 py-2">
        <div class="space-y-1 text-sm">
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">Status</span>
                <span class="ml-2">: {{ Str::ucfirst($pembelianRequest->status) }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">User Verify</span>
                <span class="ml-2">: {{ $pembelianRequest->user_verify }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-24 flex-shrink-0 text-nowrap">Verify Pada</span>
                <span class="ml-2">: {{ $pembelianRequest->updated_at }}</span>
            </div>
            @if ($pembelianRequest->ket_reject)
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $pembelianRequest->ket_reject }}</span>
                </div>
            @endif
        </div>

        <span class="text-sm italic text-gray-400">Items Diajukan</span>
        <table class="w-full">
            <thead>
                <tr class="bg-gray-100 text-sm font-semibold text-gray-700">
                    <td class="px-4 py-2 text-left">Barang</td>
                    <td class="px-4 py-2 text-left">Kategori</td>
                    <td class="px-4 py-2 text-left">Consumable</td>
                    <td class="px-4 py-2 text-left">Satuan</td>
                    <td class="px-4 py-2 text-left">Jumlah Permintaan</td>
                    <td class="px-4 py-2 text-right">Disetujui</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($pembelianRequestDetails as $item)
                    @php
                        $redtext = $item->jml_disetujui == 0 ? 'text-red-500' : '';
                    @endphp

                    <tr class="{{ $redtext }} text-sm even:bg-gray-50 hover:bg-indigo-50">
                        <td class="px-4 py-2 text-left">{{ $item->barang->nama }}</td>
                        <td class="px-4 py-2 text-left">{{ $item->barang->kategori->nama }}</td>
                        <td class="px-4 py-2 text-left">{{ $item->barang->bhp ? 'Ya' : 'Bukan' }}</td>
                        <td class="px-4 py-2 text-left">{{ $item->barang->satuan->nama }}</td>
                        <td class="px-4 py-2 text-left">{{ $item->jml_req }}</td>
                        <td class="px-4 py-2 text-right">{{ $item->jml_disetujui == 0 ? 'Tidak Disetujui' : $item->jml_disetujui }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <div id="print-permintaan-approval" class="absolute -left-[9999px]">
        <livewire:Pembelian.Permintaan.PrintPermintaan :$pembelianRequest :$pembelianRequestDetails />
    </div>

    <div class="flex justify-end">
        <x-ts:button outline sm icon="tabler.printer" x-on:click="printArea('print-permintaan-approval')">Print</x-ts:button>
    </div>

    {{-- <div class="flex justify-end">
        <x-ts:button wire:click='generatePdf'>Print</x-ts:button>
    </div> --}}

</div>
