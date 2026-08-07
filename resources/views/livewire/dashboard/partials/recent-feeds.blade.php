<!-- Main Dynamic Feed / Lists -->
@php
    $activeCardsList = [];
    if (!empty($recentCuti)) $activeCardsList[] = 'cuti';
    if (!empty($recentPurchases)) $activeCardsList[] = 'purchases';
    if (!empty($recentMaintenance)) $activeCardsList[] = 'maintenance';
    if (!empty($recentSp3)) $activeCardsList[] = 'sp3';
    
    $totalActive = count($activeCardsList);
@endphp

@if($totalActive > 0)
    <div class="grid grid-cols-1 gap-6 {{ $totalActive > 1 ? 'lg:grid-cols-2' : '' }}">
        @foreach($activeCardsList as $index => $cardType)
            @php
                $isSingleOrLastOdd = ($totalActive === 1) || (($totalActive % 2 !== 0) && ($index === $totalActive - 1));
                $colSpanClass = $isSingleOrLastOdd ? 'lg:col-span-2' : '';
            @endphp

            @if($cardType === 'cuti')
                <!-- Cuti Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-slate-800">Pengajuan Cuti Terbaru</h2>
                        <a href="{{ route('kepegawaian.surat.cuti') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100">
                        @foreach($recentCuti as $cuti)
                            <div class="flex items-center justify-between py-3.5">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700">{{ $cuti['karyawan']['nama'] ?? 'Karyawan' }}</h4>
                                    <p class="text-xs text-slate-400">Periode: {{ \Carbon\Carbon::parse($cuti['tgl_mulai'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($cuti['tgl_akhir'])->translatedFormat('d M Y') }}</p>
                                </div>
                                <div>
                                    @php
                                        $statusName = $cuti['status'] instanceof \App\Enums\StatusApproval ? $cuti['status']->nama() : $cuti['status'];
                                        $statusColor = $cuti['status'] instanceof \App\Enums\StatusApproval ? $cuti['status']->color() : 'gray';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-100">
                                        {{ $statusName }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($cardType === 'purchases')
                <!-- Purchases Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-slate-800">Transaksi Pembelian Terbaru</h2>
                        <a href="{{ route('umum.pembelian.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100">
                        @foreach($recentPurchases as $purchase)
                            <div class="flex items-center justify-between py-3.5">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700">{{ $purchase['no_po'] ?? 'Pembelian PO' }}</h4>
                                    <p class="text-xs text-slate-400">Supplier: {{ $purchase['supplier']['nama'] ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-800">{{ formatRupiah($purchase['total'], true, false) }}</p>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase {{ $purchase['status_pembayaran'] === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                        {{ $purchase['status_pembayaran'] ?? 'tempo' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($cardType === 'maintenance')
                <!-- Maintenance Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-slate-800">Jadwal Maintenance Asset</h2>
                        <a href="{{ route('umum.maintenance.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100">
                        @foreach($recentMaintenance as $maint)
                            <div class="flex items-center justify-between py-3.5">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700">{{ $maint['asset']['nama'] ?? 'Asset' }}</h4>
                                    <p class="text-xs text-slate-400">Teknisi ditugaskan: {{ count($maint['teknisi'] ?? []) }} Orang</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-slate-500">Tgl Jadwal:</p>
                                    <p class="text-xs font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($maint['tanggal'])->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($cardType === 'sp3')
                <!-- SP3 Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-slate-800">SP3 Terbaru</h2>
                        <a href="{{ route('kepegawaian.surat.sp3') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100">
                        @foreach($recentSp3 as $sp3)
                            <div class="flex items-center justify-between py-3.5">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700">SP3 No. {{ $sp3['no'] }} ({{ $sp3['tahun'] }})</h4>
                                    <p class="text-xs text-slate-400">Rekanan: {{ $sp3['rekanan'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-slate-500">Persetujuan:</p>
                                    <p class="text-xs font-semibold text-emerald-600">{{ $sp3['penyetuju']['nama'] ?? 'Disetujui' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
