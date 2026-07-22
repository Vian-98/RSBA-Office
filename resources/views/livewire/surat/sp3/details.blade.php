<div class="flex flex-col gap-2">
    <div class="flex flex-col rounded-md border border-indigo-200 px-4 py-2">
        <span class="text-lg font-semibold text-indigo-500"># {{ $suratSp3->no }}</span>
        <div class="flex w-full flex-row gap-2">
            <div class="flex w-1/4 flex-col py-2">
                <span class="text-xs italic text-gray-500">Ke</span>
                <span class="text-sm">{{ $suratSp3->rekanan }}</span>
                <span class="text-sm">{{ date('d M Y', strtotime($suratSp3->tgl)) }}</span>
                <span class="text-sm">{{ $suratSp3->method_bayar }}</span>
            </div>
            <div class="flex w-3/4 flex-col py-2">
                <span class="text-xs italic text-gray-500">Subject / Berita</span>
                <span class="text-sm">{{ $suratSp3->keterangan ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="gap-2 rounded-md border border-indigo-200 px-4 py-2">
        <div>
            <span class="text-xs italic text-gray-500">Rincian Pembayaran</span>
            <x-table-static :$headers :rows="$this->rows()" headerless />
        </div>
        <div class="mt-2 flex w-full justify-end rounded-md bg-indigo-100/50 p-2">
            <div class="flex flex-col text-right">
                <span class="text-xs italic text-gray-500">Total Pembayaran</span>
                <span class="font-bold text-indigo-500">{{ formatRupiah($suratSp3->details->sum('nominal')) }}</span>
            </div>
        </div>

        @if ($this->approvals->count() > 0)
            <div class="ml-auto flex w-full justify-end px-4 py-2">
                <div class="flex w-full flex-col">
                    @forelse ($this->approvals as $item)
                        @if ($item['status'] == 'Manual' || !empty($item['is_manual']))
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500">Mengetahui</span>
                                <span class="block h-12 w-auto">
                                    {{-- Kolom Tanda Tangan Basah --}}
                                </span>
                                <span class="text-indigo-500 font-semibold">{{ $item['nama'] }}</span>
                                <span class="text-[10px] font-light text-gray-400">Manual Cetak (Tanda Tangan Basah)</span>
                            </div>
                        @else
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500">{{ $item['status'] }} Oleh</span>
                                <span>
                                    <img src="data:image/png;base64,{{ $this->generateBarcode }}" alt="Barcode Tanda Tangan" class="h-auto w-32">
                                </span>
                                <span class="text-indigo-500">{{ $item['nama'] }}</span>
                                <span class="text-[10px] font-light text-gray-400">{{ $item['approved_at'] }}</span>
                            </div>
                        @endif
                    @empty
                        <span class="text-xs italic text-orange-500">Menunggu Persetujuan </span>
                        <span class="text-xs">{{ $suratSp3->jabatans->nama }}</span>
                    @endforelse
                </div>
            </div>

            <div id="print-sp3" class="hidden">
                <livewire:Surat.Sp3.PrintSp3 :$suratSp3 />
            </div>

            <div class="ml-auto mt-4 flex flex-row justify-end gap-2">
                <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-sp3')">Print</x-ts:button>
            </div>
        @else
            <div class="flex flex-col py-2">
                <span class="text-xs italic text-orange-500">Menunggu Persetujuan </span>
                <span class="text-xs">{{ $suratSp3->jabatans->nama }}</span>
            </div>
        @endif
    </div>

</div>
