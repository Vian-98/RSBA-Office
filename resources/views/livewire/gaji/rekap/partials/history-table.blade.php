<!-- Trend Table -->
<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xs h-full flex flex-col justify-between">
    <div class="px-6 py-4 border-b border-slate-50">
        <h2 class="text-md font-bold text-slate-800">Tabel Riwayat Penggajian</h2>
    </div>
    <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse text-left text-sm text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-400">
                    <th class="px-4 py-4 whitespace-nowrap">Periode</th>
                    <th class="px-4 py-4 text-center whitespace-nowrap">Karyawan</th>
                    <th class="px-4 py-4 whitespace-nowrap">Total Potongan</th>
                    <th class="px-4 py-4 whitespace-nowrap">Total Gaji Bersih</th>
                    <th class="px-4 py-4 text-center whitespace-nowrap">Status</th>
                    <th class="px-4 py-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($trendMonths as $item)
                    @php
                        $isActive = $item['periode'] === $this->periode;
                    @endphp
                    <tr 
                        wire:click="setPeriode('{{ $item['periode'] }}')"
                        class="transition-colors cursor-pointer hover:bg-indigo-50/30 {{ $isActive ? 'bg-indigo-50/40 font-semibold' : '' }}">
                        <td class="px-4 py-4 text-slate-800 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if($isActive)
                                    <span class="h-2 w-2 rounded-full bg-indigo-600 animate-pulse flex-shrink-0"></span>
                                @endif
                                {{ $item['label'] }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 whitespace-nowrap">
                            {{ $item['karyawan_count'] }} Karyawan
                        </td>
                        <td class="px-4 py-4 font-medium text-slate-600 whitespace-nowrap">
                            Rp {{ number_format($item['total_potongan'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 font-bold whitespace-nowrap {{ $isActive ? 'text-indigo-600' : 'text-slate-800' }}">
                            Rp {{ number_format($item['total_gaji_bersih'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($item['status'] === 'approved')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                    <x-tabler-lock class="h-3 w-3" />
                                    Disetujui
                                </span>
                            @elseif($item['status'] === 'review_pajak')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 border border-amber-100">
                                    <x-tabler-eye-check class="h-3 w-3" />
                                    Review Pajak
                                </span>
                            @elseif($item['status'] === 'review_sdm')
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 border border-blue-100">
                                    <x-tabler-clipboard-check class="h-3 w-3" />
                                    Review SDM
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                    <x-tabler-lock-open class="h-3 w-3" />
                                    Draf
                                </span>
                            @endif
                        <td class="px-4 py-4 text-center whitespace-nowrap" @click.stop>
                            <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                @can('view-kepegawaian-gaji-detail')
                                    <a href="{{ route('kepegawaian.gaji.detail', ['periode' => $item['periode']]) }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-800 transition-all whitespace-nowrap">
                                        <x-tabler-calculator class="h-3.5 w-3.5" />
                                        {{ $item['status'] === 'approved' ? 'Lihat Detail' : 'Kelola Gaji' }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Buka Detail</span>
                                @endcan

                                {{-- SDM Actions --}}
                                @if($isSDM)
                                    @if($item['status'] === 'draft' && $item['karyawan_count'] > 0)
                                        {{-- SDM: Kirim ke Pajak --}}
                                        <button type="button" wire:click="submitToReviewPajak('{{ $item['periode'] }}')" wire:confirm="Kirim data gaji periode ini ke Tim Pajak untuk direview?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 hover:text-amber-800 transition-all whitespace-nowrap" title="Kirim ke Pajak">
                                            <x-tabler-send class="h-4 w-4" />
                                        </button>
                                    @elseif($item['status'] === 'review_pajak')
                                        {{-- Waiting for Pajak --}}
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-amber-500 bg-amber-50/50 border border-amber-100 cursor-default whitespace-nowrap" title="Menunggu review dari Tim Pajak">
                                            <x-tabler-clock class="h-4 w-4" />
                                        </span>
                                    @elseif($item['status'] === 'review_sdm')
                                        {{-- SDM: Finalisasi & SP3 --}}
                                        <button type="button" wire:click="openFinalisasiModal('{{ $item['periode'] }}', {{ $item['karyawan_count'] }}, {{ $item['total_potongan'] }}, {{ $item['total_gaji_bersih'] }})" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all whitespace-nowrap" title="Setujui & Kunci">
                                            <x-tabler-lock class="h-4 w-4" />
                                        </button>
                                    @elseif($item['status'] === 'approved')
                                        @if($item['sp3_status'] === 'rejected')
                                            {{-- SP3 Ditolak Direksi -> SDM boleh buka kunci untuk revisi --}}
                                            <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-800 transition-all whitespace-nowrap" title="SP3 ditolak Direksi. Buka kunci untuk merevisi.">
                                                <x-tabler-lock-open class="h-4 w-4" />
                                            </button>
                                        @else
                                            {{-- SP3 Pending / Approved -> Hanya Super Admin yang boleh buka kunci --}}
                                            @role('Super-Admin')
                                                <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-orange-600 bg-orange-50 hover:bg-orange-100 hover:text-orange-800 transition-all whitespace-nowrap" title="Force Unlock (Super Admin)">
                                                    <x-tabler-shield-lock class="h-4 w-4" />
                                                </button>
                                            @else
                                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed whitespace-nowrap" title="Payroll dikunci & dikirim ke SP3. Hanya Super Admin yang dapat membuka kunci.">
                                                    <x-tabler-lock class="h-4 w-4" />
                                                </span>
                                            @endrole
                                        @endif
                                    @endif
                                @endif

                                {{-- Pajak Actions --}}
                                @if($isOnlyPajak && $item['status'] === 'review_pajak')
                                    <button type="button" wire:click="approveByPajak('{{ $item['periode'] }}')" wire:confirm="Setujui data pajak untuk periode ini dan kembalikan ke SDM?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all whitespace-nowrap" title="Setujui Pajak">
                                        <x-tabler-check class="h-4 w-4" />
                                    </button>
                                    <button type="button" wire:click="rejectByPajak('{{ $item['periode'] }}')" wire:confirm="Tolak dan kembalikan ke SDM untuk diperbaiki?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-800 transition-all whitespace-nowrap" title="Tolak Pajak (Kembalikan ke SDM)">
                                        <x-tabler-x class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
