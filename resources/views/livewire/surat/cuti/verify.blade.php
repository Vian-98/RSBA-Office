<div class="flex flex-col gap-2">
    <x-ts:input wire:model.live.debounce.300='signature' placeholder="Scan Cuti Signature . . ." autocomplete="off" />

    @if ($dataSuratAsli)
        <div class="bg-{{ $bgColor }}-100 w-full rounded-md border p-4">
            <span class="font-bold text-slate-800">No Surat: {{ $surat->no_surat }}</span>
            <div class="flex flex-col gap-2 mt-2">
                <span class="text-sm font-semibold">
                    Status :
                    <span class="text-{{ $verify ? 'green' : 'red' }}-500 font-bold">{{ $verify ? 'Valid' : 'Invalid ! Data Telah Dirubah' }}</span>
                </span>

                {{-- tampilkan data asli --}}
                <div class="ms-2 flex flex-col text-sm text-gray-500 space-y-1">
                    <span class="text-indigo-500 font-medium">Data asli adalah : </span>

                    <span>Status : {{ $dataSuratAsli[0]['status'] }}</span>
                    <span>Oleh: {{ $dataSuratAsli[0]['disetujui'] }}</span>
                    <span>Keterangan : {{ $dataSuratAsli[0]['keterangan'] }}</span>
                    <span>Sign In Pada : {{ \Carbon\Carbon::parse($dataSuratAsli[0]['approved_at'])->translatedFormat('d F Y H:i:s') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
