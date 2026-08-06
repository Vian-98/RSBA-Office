<!-- Salaries Table -->
<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xs">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-400">
                    <th class="px-6 py-4 whitespace-nowrap">Nama & NIP</th>
                    <th class="px-6 py-4 whitespace-nowrap">Bagian / Jabatan</th>
                    @if(str_ends_with($periode, '-12'))
                        <th class="px-6 py-4 text-indigo-700 bg-indigo-50/50 whitespace-nowrap">Bruto YTD (Setahun)</th>
                        <th class="px-6 py-4 text-indigo-700 bg-indigo-50/50 whitespace-nowrap">PPh21 YTD (Setahun)</th>
                    @endif
                    <th class="px-6 py-4 whitespace-nowrap">Status Kerja</th>
                    <th class="px-6 py-4 whitespace-nowrap">Status Input</th>
                    <th class="px-6 py-4 whitespace-nowrap">Gaji Pokok</th>
                    <th class="px-6 py-4 whitespace-nowrap">Tunjangan</th>
                    <th class="px-6 py-4 whitespace-nowrap">Gaji Bersih</th>
                    <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($karyawans as $karyawan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $karyawan->full_nama }}</div>
                            <div class="text-xs text-slate-400">NIP: {{ $karyawan->nip }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700">{{ $karyawan->calculated_salary['bagian_nama'] }}</div>
                            <div class="text-xs text-slate-500">{{ $karyawan->calculated_salary['jabatan_nama'] }}</div>
                        </td>
                        @if(str_ends_with($periode, '-12'))
                            <td class="px-6 py-4 font-bold text-slate-800 bg-indigo-50/20 whitespace-nowrap">
                                Rp {{ number_format($karyawan->calculated_salary['bruto_ytd'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-amber-600 bg-indigo-50/20 whitespace-nowrap">
                                Rp {{ number_format($karyawan->calculated_salary['pph21_ytd'], 0, ',', '.') }}
                            </td>
                        @endif
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 whitespace-nowrap">
                                {{ $karyawan->status->nama() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($karyawan->payroll_status === 'generated')
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-100 whitespace-nowrap">
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200 whitespace-nowrap">
                                    Belum Input
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700 whitespace-nowrap">
                            Rp {{ number_format($karyawan->calculated_salary['gaji_pokok'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700 whitespace-nowrap">
                            Rp {{ number_format($karyawan->calculated_salary['tunjangan'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-indigo-600 whitespace-nowrap">
                            Rp {{ number_format($karyawan->calculated_salary['gaji_bersih'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @php
                                    $emailLog = $this->getEmailSendStatus($karyawan->id);
                                @endphp
                                @if($emailLog && $emailLog['status'] === 'sent')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full" title="Terkirim: {{ $emailLog['sent_at'] }}">
                                        <x-tabler-mail-check class="h-3.5 w-3.5 text-emerald-600" />
                                        Email OK
                                    </span>
                                @elseif($emailLog && $emailLog['status'] === 'failed')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full" title="Error: {{ $emailLog['error'] }}">
                                        <x-tabler-mail-x class="h-3.5 w-3.5 text-rose-600" />
                                        Gagal
                                    </span>
                                @else
                                    <x-ts:button flat color="sky" class="text-xs font-bold" wire:click="sendEmail({{ $karyawan->id }})" title="Kirim Slip Email Manual">
                                        <x-tabler-mail class="h-4 w-4" />
                                    </x-ts:button>
                                @endif

                                @if($isLocked)
                                    @if($karyawan->payroll_status === 'generated')
                                        <x-ts:button flat color="indigo" class="text-xs font-bold" wire:click="viewSlip({{ $karyawan->id }})" title="Lihat Slip Gaji Cetak">
                                            <x-tabler-file-text class="h-4 w-4" />
                                            Slip
                                        </x-ts:button>
                                        <x-ts:button flat color="slate" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})" title="Lihat Rincian Gaji">
                                            <x-tabler-eye class="h-4 w-4" />
                                            Detail
                                        </x-ts:button>
                                    @else
                                        <x-ts:button flat color="slate" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})" title="Lihat Rincian Gaji">
                                            <x-tabler-eye class="h-4 w-4" />
                                            Detail
                                        </x-ts:button>
                                    @endif
                                @else
                                    @if($karyawan->payroll_status === 'generated')
                                        <x-ts:button flat color="indigo" class="text-xs font-bold" wire:click="viewSlip({{ $karyawan->id }})">
                                            <x-tabler-file-text class="h-4 w-4" />
                                            Slip
                                        </x-ts:button>
                                        <x-ts:button flat color="amber" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})">
                                            <x-tabler-edit class="h-4 w-4" />
                                            Edit
                                        </x-ts:button>
                                    @else
                                        <x-ts:button flat color="emerald" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})">
                                            <x-tabler-plus class="h-4 w-4" />
                                            Input Gaji
                                        </x-ts:button>
                                    @endif
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center text-slate-400">
                            <x-tabler-database-x class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                            <div class="text-sm font-semibold">Tidak Ada Karyawan Ditemukan</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span>Tampilkan:</span>
            <select wire:model.live="perPage" class="rounded-lg border-gray-300 text-xs py-1.5 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="10">10 data</option>
                <option value="25">25 data</option>
                <option value="50">50 data</option>
                <option value="100">100 data</option>
                <option value="-1">Semua data</option>
            </select>
        </div>
        @if($karyawans->hasPages())
            <div>
                {{ $karyawans->onEachSide(1)->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>
