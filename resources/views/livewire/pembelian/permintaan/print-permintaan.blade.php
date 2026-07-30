<div class="w-full">
    <div align="center" class="mb-2 flex flex-col items-center text-center">
        <img src="{{ ($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png') }}" class="h-[60px] w-auto" alt="Logo">
        <span class="text-lg font-bold uppercase">{{ $rs->nama }}</span>
        <span class="text-sm">SURAT PERMINTAAN PENGADAAN<br>(Barang, Jasa, dll)</span>
    </div>
    <div class="flex flex-col text-sm">
        <span class="text-lg italic text-info-500">#{{ $pembelianRequest->id }}</span>
        <div class="grid grid-cols-2">
            <div class="flex flex-col">
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">User Pengaju</span>
                    <span class="ml-2">: {{ $pembelianRequest->userRequest }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $pembelianRequest->note }}</span>
                </div>
            </div>
            <div class="flex flex-col">
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Diverifikasi Oleh</span>
                    <span class="ml-2">: {{ $pembelianRequest->userVerify }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Status</span>
                    <span class="ml-2">: {{ Str::ucfirst($pembelianRequest->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex w-full flex-col text-sm">
        <span class="text-sm text-gray-400">Detail Pengajuan</span>

        <span class="flex flex-col">
            <div class="grid grid-cols-4 bg-gray-500 font-semibold">
                <span>Nama Barang</span>
                <span>Disetujui</span>
                <span>Harga</span>
                <span class="text-right">Ket</span>
            </div>
            @foreach ($pembelianRequestDetails as $item)
                <div class="{{ $item->jml_disetujui == 0 ? 'text-red-500' : '' }} grid grid-cols-4 even:bg-gray-50">
                    <span>{{ $item->barang->nama }}</span>
                    <span>{{ $item->jml_disetujui == 0 ? 'Tidak Disetujui' : $item->jml_disetujui . $item->barang->satuan->nama }} </span>
                    <span>{{ $item->harga }}</span>
                    <span class="text-right">{{ $item->keterangan }}</span>
                </div>
            @endforeach
        </span>
    </div>

    <div class="mt-4 flex justify-end text-sm">
        <div class="flex flex-col">
            <span>Bandar Lampung</span>
            <span>
                <img src="data:image/png;base64,{{ $this->getSignature['qrcode'] }}" alt="Barcode Tanda Tangan" style="height: auto; width:72px; ">
                ({{ $this->getSignature['sign_by'] }})
            </span>
        </div>
    </div>
</div>
