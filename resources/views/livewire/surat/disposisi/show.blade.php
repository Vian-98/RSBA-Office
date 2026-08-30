<div class="space-y-6">
    <!-- Header Navigation & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="flex items-center gap-3">
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.index') }}" outline color="indigo" icon="chevron-left" size="sm">
                Kembali ke Daftar Disposisi
            </x-ts:button>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                Disposisi #{{ $disposisi->no_agenda }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.print', $disposisi->id) }}" target="_blank" color="indigo" icon="printer" size="sm">
                Cetak / Download PDF Resmi
            </x-ts:button>
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-700 p-6 shadow-sm space-y-6">
        
        <!-- Header Info & Digital Signature QR Badge -->
        <div class="flex flex-col md:flex-row items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-6 gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">
                    LEMBAR PENERUS / DISPOSISI
                </h2>
                <h3 class="text-md font-bold text-indigo-700 dark:text-indigo-400">
                    RS BINTANG AMIN - DIREKTUR
                </h3>
                <div class="mt-2 text-xs font-mono text-slate-500">
                    ID Transaksi: {{ $disposisi->signature_hash ?? 'N/A' }}
                </div>
            </div>

            <!-- Verification QR Box -->
            @if($disposisi->signature_hash)
                <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl p-3">
                    <div class="p-2 bg-white rounded-lg border shadow-xs">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode(url('/verify-document/' . $disposisi->signature_hash)) }}" alt="QR Keabsahan" class="w-14 h-14" />
                    </div>
                    <div>
                        <x-ts:badge color="emerald" icon="check-badge" text="TTD DIGITAL DIREKTUR" />
                        <p class="text-[11px] text-emerald-800 dark:text-emerald-300 mt-1 font-medium">
                            Dokumen Sah & Terverifikasi
                        </p>
                        <a href="{{ url('/verify-document/' . $disposisi->signature_hash) }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline">
                            Verifikasi Publik &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Meta Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 text-sm">
            <div>
                <span class="text-xs font-bold uppercase text-slate-500 block">No. Agenda</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-base">{{ $disposisi->no_agenda }}</span>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-500 block">Tgl Surat</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $disposisi->tgl_surat->format('d F Y') }}</span>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-500 block">No. Surat</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $disposisi->no_surat }}</span>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-500 block">Asal Surat</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $disposisi->asal_surat }}</span>
            </div>
            <div class="md:col-span-2">
                <span class="text-xs font-bold uppercase text-slate-500 block">Perihal</span>
                <span class="font-bold text-slate-900 dark:text-white text-base">{{ $disposisi->perihal }}</span>
            </div>
        </div>

        <!-- Kepada YTH Table -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-2">
                Kepada YTH (Penerima & Status Tindak Lanjut)
            </h3>
            
            <div class="border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
                    <thead class="bg-slate-100 dark:bg-slate-900 font-bold border-b border-slate-300 dark:border-slate-700 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4">Kepada YTH</th>
                            <th class="py-3 px-3 text-center">Info</th>
                            <th class="py-3 px-3 text-center">Action</th>
                            <th class="py-3 px-3 text-center">Arsip</th>
                            <th class="py-3 px-4 text-center">Tanda Terima & Paraf</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($disposisi->details as $idx => $det)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50">
                                <td class="py-3 px-3 text-center font-bold text-xs">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-medium">
                                    {{ $det->nama_tujuan }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($det->is_info) <x-ts:icon name="check" class="w-5 h-5 text-indigo-600 inline" /> @else - @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($det->is_action) <x-ts:icon name="check" class="w-5 h-5 text-emerald-600 inline" /> @else - @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($det->is_arsip) <x-ts:icon name="check" class="w-5 h-5 text-amber-600 inline" /> @else - @endif
                                </td>
                                <td class="py-3 px-4 text-center text-xs">
                                    @if($det->status_tindak_lanjut === 'done')
                                        <x-ts:badge color="emerald" text="Diterima / Paraf" />
                                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $det->tgl_paraf ? $det->tgl_paraf->format('d/m/Y H:i') : '' }}</div>
                                    @else
                                        <x-ts:badge color="amber" text="Pending" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Catatan Direktur -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Catatan / Instruksi Direktur:</h3>
            <div class="p-4 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-slate-800 dark:text-slate-200 whitespace-pre-line font-medium text-sm">
                {{ $disposisi->catatan ?: 'Tidak ada catatan khusus.' }}
            </div>
        </div>

        <!-- Footer Info -->
        <div class="flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 border-t border-slate-200 dark:border-slate-700 pt-4">
            <div>Diterima oleh: <strong class="text-slate-700 dark:text-slate-300">{{ $disposisi->diterima_oleh ?: '-' }}</strong></div>
            <div>Tgl/Jam: <strong class="text-slate-700 dark:text-slate-300">{{ $disposisi->tgl_diterima ? $disposisi->tgl_diterima->format('d/m/Y') : '-' }} {{ $disposisi->jam_diterima }}</strong></div>
        </div>
    </div>
</div>
