<!-- Printable Area -->
<div id="salary-slip-print" class="p-6 bg-white text-slate-800 text-sm select-none">
    <!-- Header -->
    <div class="flex flex-col items-center justify-center pb-4 mb-4 border-b-2 border-slate-900">
        <x-logo class="h-12 w-auto mb-1" style="height: 48px; width: auto;" />
        <h2 class="text-base font-black tracking-widest text-slate-800 uppercase leading-none">RS BINTANG AMIN</h2>
    </div>

    <!-- Info Block -->
    <table class="w-full text-xs font-semibold mb-4 border-collapse">
        <tbody>
            <tr class="border-t border-b border-slate-800">
                <td class="w-20 py-1.5 font-bold">Nama</td>
                <td class="w-4 py-1.5">:</td>
                <td class="py-1.5 font-bold">{{ $selectedSlip['nama'] }}</td>
            </tr>
            <tr class="border-b border-slate-800">
                <td class="py-1.5 font-bold">NIP</td>
                <td class="py-1.5">:</td>
                <td class="py-1.5 font-bold">{{ $selectedSlip['nip'] }}</td>
            </tr>
            <tr class="border-b border-slate-800">
                <td class="py-1.5 font-bold">Jabatan</td>
                <td class="py-1.5">:</td>
                <td class="py-1.5 font-bold">{{ $selectedSlip['jabatan'] }}</td>
            </tr>
            <tr class="border-b-4 border-double border-slate-800">
                <td class="py-1.5 font-bold">Bulan</td>
                <td class="py-1.5">:</td>
                <td class="py-1.5 font-bold">{{ $selectedSlip['periode'] }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Main Grid Table -->
    <table class="w-full text-xs border-collapse border border-slate-800">
        <tbody>
            <!-- Gaji Pokok -->
            <tr class="border-b border-slate-800">
                <td class="w-1/2 border-r border-slate-800 px-3 py-1.5 font-medium">Gaji Pokok</td>
                <td class="w-[15%] border-r border-slate-800 px-3 py-1.5"></td>
                <td class="w-[10%] border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="w-[25%] px-3 py-1.5 text-right font-medium">{{ number_format($selectedSlip['gaji_pokok'], 0, ',', '.') }}</td>
            </tr>
            <!-- Tj. Tetap -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                    Tj. Tetap
                    @if(!empty($selectedSlip['allocations_list']))
                        <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                            @foreach($selectedSlip['allocations_list'] as $alloc)
                                @if(!$alloc->is_absensi)
                                    • {{ $alloc->nama }}: Rp {{ number_format($alloc->nominal, 0, ',', '.') }}<br>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_tetap'] > 0 ? number_format($selectedSlip['tunjangan_tetap'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Tj. Kehadiran -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                    Tj. Kehadiran
                    @if(!empty($selectedSlip['allocations_list']))
                        <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                            @foreach($selectedSlip['allocations_list'] as $alloc)
                                @if($alloc->is_absensi)
                                    • {{ $alloc->nama }}: Rp {{ number_format($alloc->nominal, 0, ',', '.') }}<br>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_absensi'] > 0 ? number_format($selectedSlip['tunjangan_absensi'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Tj. Lain - Lain -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                    Tj. Lain – Lain
                    @if(!empty($selectedSlip['tunjangan_lain_items']))
                        <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                            @foreach($selectedSlip['tunjangan_lain_items'] as $item)
                                • {{ $item['nama'] }}: Rp {{ number_format($item['nominal'], 0, ',', '.') }}<br>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_lain'] > 0 ? number_format($selectedSlip['tunjangan_lain'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Tj. Jabatan -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Jabatan</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_jabatan'] > 0 ? number_format($selectedSlip['tunjangan_jabatan'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Tj. Shift -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Shift</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_shift'] > 0 ? number_format($selectedSlip['tunjangan_shift'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Tj. Radiologi -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Radiologi</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_radiologi'] > 0 ? number_format($selectedSlip['tunjangan_radiologi'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Uang Lembur -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Uang Lembur</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['uang_lembur'] > 0 ? number_format($selectedSlip['uang_lembur'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- THR -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tunjangan Hari Raya (THR)</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['tunjangan_hari_raya'] > 0 ? number_format($selectedSlip['tunjangan_hari_raya'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- JUMLAH PENDAPATAN (BRUTO) -->
            <tr class="border-b-2 border-slate-800 font-bold bg-slate-50/50">
                <td class="border-r border-slate-800 px-3 py-2 uppercase">JUMLAH PENDAPATAN</td>
                <td class="border-r border-slate-800 px-3 py-2"></td>
                <td class="border-r border-slate-800 px-3 py-2 text-center">Rp.</td>
                <td class="px-3 py-2 text-right text-slate-900 font-black">{{ number_format($selectedSlip['gaji_pokok'] + $selectedSlip['tunjangan_tetap'] + $selectedSlip['tunjangan_absensi'] + $selectedSlip['tunjangan_jabatan'] + $selectedSlip['tunjangan_shift'] + $selectedSlip['tunjangan_radiologi'] + $selectedSlip['tunjangan_lain'] + $selectedSlip['uang_lembur'] + $selectedSlip['tunjangan_hari_raya'], 0, ',', '.') }}</td>
            </tr>

            <!-- POTONGAN HEADER -->
            <tr class="border-b border-slate-800 bg-slate-50/20">
                <td colspan="4" class="px-3 py-1 font-bold italic text-slate-700 uppercase tracking-wider text-[10px]">POTONGAN - POTONGAN :</td>
            </tr>
            <!-- Pot. Absensi -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Absensi / Terlambat</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['potongan_absensi'] > 0 ? number_format($selectedSlip['potongan_absensi'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Cash Bon -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Cash Bon</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['potongan_cash_bon'] > 0 ? number_format($selectedSlip['potongan_cash_bon'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Obat / Rawat Inap -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Obat / Rawat Inap</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['potongan_obat'] > 0 ? number_format($selectedSlip['potongan_obat'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Pot. Lain - Lain -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Lain – Lain</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['potongan_lain'] > 0 ? number_format($selectedSlip['potongan_lain'], 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- BPJS Kesehatan -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">BPJS Kesehatan</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ ($selectedSlip['potongan_bpjs_kes'] ?? $selectedSlip['bpjs_kes'] ?? 0) > 0 ? number_format($selectedSlip['potongan_bpjs_kes'] ?? $selectedSlip['bpjs_kes'] ?? 0, 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- BPJS Ketenagakerjaan -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">BPJS Ketenagakerjaan</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ ($selectedSlip['potongan_bpjs_tk'] ?? $selectedSlip['bpjs_ket'] ?? 0) > 0 ? number_format($selectedSlip['potongan_bpjs_tk'] ?? $selectedSlip['bpjs_ket'] ?? 0, 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- PPh Pasal 21 -->
            <tr class="border-b border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">PPh Pasal 21</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ ($selectedSlip['potongan_pph21'] ?? $selectedSlip['pajak'] ?? 0) > 0 ? number_format($selectedSlip['potongan_pph21'] ?? $selectedSlip['pajak'] ?? 0, 0, ',', '.') : '-' }}
                </td>
            </tr>
            <!-- Potongan Bank -->
            <tr class="border-b-2 border-slate-800">
                <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Potongan Bank</td>
                <td class="border-r border-slate-800 px-3 py-1.5"></td>
                <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                <td class="px-3 py-1.5 text-right font-medium">
                    {{ $selectedSlip['potongan_bank'] > 0 ? number_format($selectedSlip['potongan_bank'], 0, ',', '.') : '-' }}
                </td>
            </tr>

            <!-- PENGHASILAN NETTO -->
            <tr class="font-bold bg-indigo-50/60">
                <td class="border-l border-r border-slate-800 px-3 py-2 uppercase text-indigo-800">PENGHASILAN NETTO</td>
                <td class="border-r border-slate-800 px-3 py-2"></td>
                <td class="border-r border-slate-800 px-3 py-2 text-center text-indigo-800">Rp.</td>
                <td class="border-r border-slate-800 px-3 py-2 text-right text-indigo-850 text-sm">{{ number_format($selectedSlip['gaji_bersih'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Bottom Signatures -->
    <div class="mt-6 flex flex-col text-xs text-slate-600 pl-6">
        <p class="font-medium">Bandar Lampung, {{ now()->translatedFormat('d F Y') }}</p>
        <p class="font-medium">Wadir SDM & Umum</p>
        <div class="h-16"></div>
        <p class="font-bold text-slate-800 leading-none">Riyanti, SP., M.Kes</p>
    </div>
</div>
