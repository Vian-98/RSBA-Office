<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="flex justify-center items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/30">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">RSBA System Verification</h2>
                <p class="text-xs text-slate-500 font-medium">Portal Resmi Verifikasi Keabsahan Dokumen Digital</p>
            </div>
        </div>

        <!-- SEARCH / SCAN INPUT BOX -->
        <div class="mb-4">
            <form wire:submit.prevent="submitSearch" class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" wire:model.live.debounce.300ms="inputHash" placeholder="Scan atau Masukkan Kode Hash QR Dokumen di sini..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-mono tracking-tight bg-white placeholder:font-sans placeholder:text-slate-400">
                </div>
                <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/20 transition-all shrink-0">
                    Cek Hash
                </button>
            </form>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 sm:rounded-2xl sm:px-10 border border-slate-100">
            @if ($isValid)
                <!-- STATUS VALID / SAH -->
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 mb-6 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-1">
                            TERVERIFIKASI SISTEM RSBA
                        </span>
                        <h3 class="text-base font-bold text-emerald-950">DOKUMEN RESMI & SAH</h3>
                        <p class="text-xs text-emerald-700 mt-0.5">Dokumen ini diterbitkan secara sah dan terdaftar resmi pada database sistem informasi RSBA.</p>
                    </div>
                </div>

                <!-- RINCIAN DOKUMEN -->
                <div class="space-y-4 text-sm">
                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Jenis Surat</span>
                        <span class="col-span-2 font-semibold text-slate-800">{{ $documentTypeLabel }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Nomor Surat</span>
                        <span class="col-span-2 font-mono font-semibold text-emerald-700">{{ $nomorSurat }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Pegawai / Pemohon</span>
                        <span class="col-span-2 font-semibold text-slate-800">{{ $namaPegawai }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Unit Kerja / Ruangan</span>
                        <span class="col-span-2 text-slate-700">{{ $unitKerja }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Perihal / Deskripsi</span>
                        <span class="col-span-2 text-slate-700">{{ $perihal }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Tanggal Diterbitkan</span>
                        <span class="col-span-2 text-slate-700">{{ \Carbon\Carbon::parse($tanggalSurat)->format('d F Y') }}</span>
                    </div>

                    @if ($signedAt)
                        <div class="grid grid-cols-3 gap-2 py-2 border-b border-slate-100">
                            <span class="text-slate-500 font-medium">Waktu Disahkan</span>
                            <span class="col-span-2 text-slate-700">{{ \Carbon\Carbon::parse($signedAt)->format('d F Y, H:i:s T') }}</span>
                        </div>
                    @endif
                </div>

                <!-- HISTORI PERSERUJUAN PEJABAT (ACC CHAIN) -->
                @if (!empty($approvals))
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Histori Persetujuan Pejabat (ACC)</h4>
                        <div class="space-y-3">
                            @foreach ($approvals as $app)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                                            ✓
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $app['nama'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $app['jabatan'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                            {{ $app['status'] }}
                                        </span>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($app['approved_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- SYSTEM SIGNATURE FOOTER HASH -->
                <div class="mt-6 p-3 rounded-lg bg-slate-900 text-slate-300 text-xs font-mono break-all leading-relaxed">
                    <span class="text-slate-500 block text-[10px] uppercase font-sans font-semibold mb-1">System Hash Signature (PKCS#12 Verified)</span>
                    {{ $hash }}
                </div>
            @else
                <!-- STATUS INVALID / TIDAK DITEMUKAN / BELUM MENGISI -->
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    @if (!empty($hash))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 mb-2">
                            KODE HASH TIDAK DITEMUKAN
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Dokumen Tidak Terdaftar</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto break-all font-mono">Kode Hash "{{ $hash }}" tidak cocok dengan rekaman dokumen mana pun di sistem.</p>
                    @else
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Masukkan / Scan Kode Hash Dokumen</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">Gunakan alat pemindai QR Code atau ketikkan kode Hash QR dokumen pada kolom pencarian di atas untuk memverifikasi keabsahan surat.</p>
                    @endif
                </div>
            @endif

            <div class="mt-8 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Rumah Sakit Bina Amartha (RSBA). All rights reserved.
            </div>
        </div>
    </div>
</div>
