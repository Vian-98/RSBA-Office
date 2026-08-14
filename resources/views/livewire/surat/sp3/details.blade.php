<div class="flex flex-col gap-2">
    {{-- Alert jika status ditolak --}}
    @if ($suratSp3->status === \App\Enums\StatusApproval::REJECTED)
        @php
            $rejectedAppr = $suratSp3->approvals()->where('status', 'rejected')->latest('id')->first();
        @endphp
        <div class="rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-xs">
            <div class="flex items-center gap-1 font-bold text-sm text-red-700">
                <x-tabler-alert-triangle class="size-4" />
                Surat SP3 Ini Ditolak
            </div>
            <div class="mt-1">
                Alasan Penolakan: <b>{{ $rejectedAppr?->keterangan ?: 'Tidak ada keterangan penolakan.' }}</b>
                @if($rejectedAppr?->disetujuiOleh)
                    (oleh {{ $rejectedAppr->disetujuiOleh->karyawan?->full_nama ?? $rejectedAppr->disetujuiOleh->name }})
                @endif
            </div>
            <div class="mt-1 text-[11px] text-red-600 italic">
                Silakan lakukan perbaikan melalui tombol "Edit & Ajukan Ulang" di bawah.
            </div>
        </div>
    @endif

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
                <div class="flex w-full flex-row flex-wrap justify-end gap-6">
                    @forelse ($this->approvals as $item)
                        @if ($item['status'] == 'Manual' || !empty($item['is_manual']))
                            <div class="flex flex-col items-center text-center">
                                <span class="text-xs font-semibold text-slate-600">{{ $item['tahap'] }}</span>
                                <span class="text-[10px] text-gray-500">Persetujuan Manual</span>
                                <span class="block h-12 w-auto"></span>
                                <span class="text-indigo-600 font-bold text-xs underline">{{ $item['nama'] }}</span>
                                <span class="text-[10px] font-light text-gray-400">Manual Cetak (TTD Basah)</span>
                            </div>
                        @elseif ($item['status'] == 'Ditolak' || $item['status'] == 'rejected')
                            <div class="flex flex-col items-center text-center rounded-md border border-red-200 bg-red-50 p-2 min-w-[120px]">
                                <span class="text-xs font-semibold text-red-700">{{ $item['tahap'] }}</span>
                                <span class="text-[10px] text-red-500 font-bold">DITOLAK</span>
                                <x-tabler-x class="size-10 text-red-500 my-1" />
                                <span class="text-red-700 font-bold text-xs underline">{{ $item['nama'] }}</span>
                                <span class="text-[10px] font-light text-red-500">{{ $item['approved_at'] }}</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center text-center">
                                <span class="text-xs font-semibold text-slate-600">{{ $item['tahap'] }}</span>
                                <span class="text-[10px] text-gray-500">{{ $item['status'] }} Oleh</span>
                                <span class="my-1">
                                    @if(!empty($item['barcode']))
                                        <img src="data:image/png;base64,{{ $item['barcode'] }}" alt="Barcode Tanda Tangan" class="h-20 w-20">
                                    @else
                                        <img src="data:image/png;base64,{{ $this->generateBarcode }}" alt="Barcode Tanda Tangan" class="h-20 w-20">
                                    @endif
                                </span>
                                <span class="text-indigo-600 font-bold text-xs underline">{{ $item['nama'] }}</span>
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
                @if ($suratSp3->status === \App\Enums\StatusApproval::REJECTED || $suratSp3->status === \App\Enums\StatusApproval::PENDING)
                    @if ($suratSp3->created_by === auth()->id() || auth()->user()->hasRole('Super-Admin'))
                        <x-ts:button sm color="info" icon="tabler.edit" x-on:click="$dispatch('open-modal', {id: 'modal-edit-sp3'}); $dispatch('close-modal', {id: 'modal-detail-sp3'})">
                            Edit & Ajukan Ulang
                        </x-ts:button>
                    @endif
                @endif

                @if ($suratSp3->status !== \App\Enums\StatusApproval::REJECTED)
                    <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-sp3')">Print</x-ts:button>
                @endif
            </div>
        @else
            <div class="flex flex-col py-2">
                <span class="text-xs italic text-orange-500">Menunggu Persetujuan </span>
                <span class="text-xs">{{ $suratSp3->jabatans->nama }}</span>
            </div>

            <div class="ml-auto mt-4 flex flex-row justify-end gap-2">
                @if ($suratSp3->created_by === auth()->id() || auth()->user()->hasRole('Super-Admin'))
                    <x-ts:button sm color="info" icon="tabler.edit" x-on:click="$dispatch('open-modal', {id: 'modal-edit-sp3'}); $dispatch('close-modal', {id: 'modal-detail-sp3'})">
                        Edit SP3
                    </x-ts:button>
                @endif
            </div>
        @endif
    </div>

</div>

