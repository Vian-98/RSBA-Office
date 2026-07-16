<div class="flex flex-col gap-3">
    <div>
        <label class="block text-2xs font-bold uppercase tracking-wider text-slate-400 mb-1">Scan QR Code Dokumen Cuti</label>
        <x-ts:input wire:model.live.debounce.300='signature' placeholder="Scan atau tempel kode tanda tangan di sini..." autocomplete="off" icon="tabler.qrcode" />
    </div>

    @if ($dataSuratAsli)
        @if ($verify)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/30 p-5 space-y-4">
                <div class="flex items-center gap-2.5">
                    <x-tabler-circle-check-filled class="h-6 w-6 text-emerald-600 shrink-0" />
                    <div>
                        <span class="text-sm font-bold text-emerald-900 block">Tanda Tangan Digital VALID</span>
                        <p class="text-xs text-emerald-700">Pengajuan Cuti asli cocok dengan tanda tangan digital terdaftar</p>
                    </div>
                </div>
                
                <div class="border-t border-emerald-100 pt-3 space-y-2 text-xs text-slate-700">
                    <div class="grid grid-cols-3">
                        <span class="text-slate-400">No. Surat:</span>
                        <span class="col-span-2 font-bold text-slate-800">{{ $surat->no_surat }}</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-400">Status Dokumen:</span>
                        <span class="col-span-2 font-bold text-slate-800 uppercase">{{ $dataSuratAsli[0]['status'] }}</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-400">Penandatangan:</span>
                        <span class="col-span-2 font-bold text-slate-800">{{ $dataSuratAsli[0]['disetujui'] }}</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-400">Tanggal TTD:</span>
                        <span class="col-span-2 font-bold text-slate-800">{{ \Carbon\Carbon::parse($dataSuratAsli[0]['approved_at'])->translatedFormat('d F Y H:i:s') }}</span>
                    </div>
                    @if($dataSuratAsli[0]['keterangan'])
                        <div class="grid grid-cols-3">
                            <span class="text-slate-400">Keterangan:</span>
                            <span class="col-span-2 text-slate-600">{{ $dataSuratAsli[0]['keterangan'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-xl border border-rose-200 bg-rose-50/30 p-5 space-y-4">
                <div class="flex items-center gap-2.5">
                    <x-tabler-circle-x-filled class="h-6 w-6 text-rose-600 shrink-0" />
                    <div>
                        <span class="text-sm font-bold text-rose-900 block">Tanda Tangan Digital TIDAK VALID</span>
                        <p class="text-xs text-rose-700">Peringatan: Isi dokumen ini telah dimodifikasi atau tanda tangan tidak cocok!</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
