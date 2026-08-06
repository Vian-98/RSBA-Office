<!-- Salary Slip Modal (Detailed Print Layout) -->
<x-ts:modal wire="isOpenModal" size="3xl" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-file-invoice class="h-5 w-5 text-indigo-500" />
            Slip Gaji Karyawan
        </span>
    </x-slot:title>

    @if($selectedSlip)
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
                                        • {{ $item->nama }}: Rp {{ number_format($item->nominal, 0, ',', '.') }}<br>
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
                    <!-- Tj. Radiasi -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Radiasi</td>
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
                    <!-- Tj. Hari Raya -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Hari Raya</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['tunjangan_hari_raya'] > 0 ? number_format($selectedSlip['tunjangan_hari_raya'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    
                    <!-- TOTAL GAJI -->
                    <tr class="border-b-4 border-double border-slate-800 font-bold bg-slate-50/50">
                        <td class="border-r border-slate-800 px-3 py-1.5 uppercase text-slate-800">TOTAL GAJI</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp.</td>
                        <td class="px-3 py-1.5 text-right text-slate-800">{{ number_format($selectedSlip['total_gaji'], 0, ',', '.') }}</td>
                    </tr>

                    <!-- Spacer row -->
                    <tr class="border-b border-slate-800 h-4 bg-slate-50/20">
                        <td class="border-r border-slate-800 px-3 py-1"></td>
                        <td class="border-r border-slate-800 px-3 py-1"></td>
                        <td class="border-r border-slate-800 px-3 py-1 text-center">Rp</td>
                        <td class="px-3 py-1 text-right"></td>
                    </tr>

                    <!-- Pot. Absensi -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Absensi</td>
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
                    <!-- Pot. Obat / Perawatan -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Obat / Perawatan</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['potongan_obat'] > 0 ? number_format($selectedSlip['potongan_obat'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- Pot. BPJS Kesehatan -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. BPJS Kesehatan</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['bpjs_kes'] > 0 ? number_format($selectedSlip['bpjs_kes'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- Pot. BPJS TK -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. BPJS TK</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['bpjs_ket'] > 0 ? number_format($selectedSlip['bpjs_ket'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- Potongan Lain-lain -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Potongan Lain-lain</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['potongan_lain'] > 0 ? number_format($selectedSlip['potongan_lain'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    <!-- TOTAL POTONGAN -->
                    <tr class="border-b-4 border-double border-slate-800 font-bold bg-slate-50/50">
                        <td class="border-r border-slate-800 px-3 py-1.5 uppercase text-slate-800">TOTAL POTONGAN</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp.</td>
                        <td class="px-3 py-1.5 text-right text-slate-800">{{ number_format($selectedSlip['total_potongan'], 0, ',', '.') }}</td>
                    </tr>

                    <!-- Spacer row -->
                    <tr class="border-b border-slate-800 h-4 bg-slate-50/20">
                        <td class="border-r border-slate-800 px-3 py-1"></td>
                        <td class="border-r border-slate-800 px-3 py-1"></td>
                        <td class="border-r border-slate-800 px-3 py-1 text-center">Rp</td>
                        <td class="px-3 py-1 text-right"></td>
                    </tr>

                    <!-- PPh Pasal 21 -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">PPh Pasal 21</td>
                        <td class="border-r border-slate-800 px-3 py-1.5"></td>
                        <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                        <td class="px-3 py-1.5 text-right font-medium">
                            {{ $selectedSlip['pajak'] > 0 ? number_format($selectedSlip['pajak'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- Pot. Bank -->
                    <tr class="border-b border-slate-800">
                        <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Bank</td>
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

        <!-- Edit History Section (Non-printable) -->
        @if(!empty($selectedSlip['edit_logs']) && count($selectedSlip['edit_logs']) > 0)
            <div class="mt-6 border-t border-slate-200 pt-6 px-6 pb-4">
                <div class="flex items-center gap-2 mb-4">
                    <x-tabler-history class="h-5 w-5 text-slate-500" />
                    <h3 class="font-bold text-slate-800 text-sm">Riwayat Perubahan Data Gaji</h3>
                </div>
                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                    @foreach($selectedSlip['edit_logs'] as $log)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs text-slate-600">
                            <div class="flex justify-between items-start gap-4 mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="rounded-full bg-slate-200 text-slate-700 w-6 h-6 flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($log->editor_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $log->editor_name }}</span>
                                        <span class="text-slate-400 text-[10px] ml-1.5">• Mengubah data</span>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold bg-white border border-slate-200/60 rounded-md px-2 py-0.5 shadow-sm">
                                    {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 mt-2.5 pl-8">
                                @foreach($log->perubahan as $col => $change)
                                    <div class="flex items-center justify-between py-1 border-b border-dashed border-slate-200 last:border-0">
                                        <span class="text-slate-500 font-medium">{{ $change['label'] }}</span>
                                        <div class="flex items-center gap-2 font-semibold">
                                            @if($col === 'bpjs_keluarga_tambahan')
                                                <span class="text-slate-400 font-normal line-through">{{ $change['old'] }}</span>
                                                <x-tabler-arrow-narrow-right class="h-3 w-3 text-slate-400" />
                                                <span class="text-indigo-600">{{ $change['new'] }}</span>
                                            @else
                                                <span class="text-slate-400 font-normal line-through">Rp {{ number_format($change['old'], 0, ',', '.') }}</span>
                                                <x-tabler-arrow-narrow-right class="h-3 w-3 text-slate-400" />
                                                <span class="text-indigo-600">Rp {{ number_format($change['new'], 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-end gap-2.5">
                <x-ts:button size="sm" flat color="slate" wire:click="closeModal">Tutup</x-ts:button>
                <x-ts:button size="sm" color="sky" class="font-bold text-white bg-sky-600 hover:bg-sky-700" wire:click="sendEmail({{ $selectedSlip['id'] }})" loading="sendEmail">
                    <x-tabler-mail class="mr-1.5 h-4 w-4" />
                    Kirim ke Email
                </x-ts:button>
                <x-ts:button size="sm" color="indigo" class="font-bold" onclick="printSalarySlip()">
                    <x-tabler-printer class="mr-1.5 h-4 w-4" />
                    Cetak Slip Gaji
                </x-ts:button>
            </div>
        </x-slot:footer>
    @endif
</x-ts:modal>

<!-- Custom Print Script -->
<script>
    function printSalarySlip() {
        var printContents = document.getElementById('salary-slip-print').innerHTML;

        var printWindow = window.open('', '', 'height=700,width=850');
        printWindow.document.write('<html><head><title>Cetak Slip Gaji</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: sans-serif; font-size: 13px; color: #1e293b; line-height: 1.4; padding: 20px; margin: 0; }');
        printWindow.document.write('.flex { display: flex; }');
        printWindow.document.write('.flex-col { flex-direction: column; }');
        printWindow.document.write('.items-center { align-items: center; }');
        printWindow.document.write('.justify-center { justify-content: center; }');
        printWindow.document.write('.pb-4 { padding-bottom: 12px; }');
        printWindow.document.write('.mb-4 { margin-bottom: 16px; }');
        printWindow.document.write('.border-b-2 { border-bottom: 2px solid #0f172a; }');
        printWindow.document.write('.border-slate-900 { border-color: #0f172a; }');
        printWindow.document.write('.text-base { font-size: 14px; }');
        printWindow.document.write('.font-black { font-weight: 800; }');
        printWindow.document.write('.tracking-widest { letter-spacing: 0.1em; }');
        printWindow.document.write('.uppercase { text-transform: uppercase; }');
        printWindow.document.write('.leading-none { line-height: 1; }');
        printWindow.document.write('.w-full { width: 100%; }');
        printWindow.document.write('.text-xs { font-size: 11px; }');
        printWindow.document.write('.font-semibold { font-weight: 600; }');
        printWindow.document.write('.border-collapse { border-collapse: collapse; }');
        printWindow.document.write('.border-t { border-top: 1px solid #cbd5e1; }');
        printWindow.document.write('.border-b { border-bottom: 1px solid #cbd5e1; }');
        printWindow.document.write('.border-slate-300 { border-color: #cbd5e1; }');
        printWindow.document.write('.py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }');
        printWindow.document.write('.font-bold { font-weight: bold; }');
        printWindow.document.write('.border { border: 1px solid #1e293b; }');
        printWindow.document.write('.border-slate-800 { border-color: #1e293b; }');
        printWindow.document.write('.border-b-4 { border-bottom-width: 4px; }');
        printWindow.document.write('.border-double { border-bottom-style: double !important; border-color: #1e293b !important; }');
        printWindow.document.write('.border-r { border-right: 1px solid #cbd5e1; }');
        printWindow.document.write('.px-3 { padding-left: 12px; padding-right: 12px; }');
        printWindow.document.write('.font-medium { font-weight: 500; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-right { text-align: right; }');
        printWindow.document.write('.bg-slate-50\\/50 { background-color: #f8fafc; }');
        printWindow.document.write('.h-4 { height: 16px; }');
        printWindow.document.write('.bg-slate-50\\/20 { background-color: #f8fafc; }');
        printWindow.document.write('.py-2 { padding-top: 8px; padding-bottom: 8px; }');
        printWindow.document.write('.text-indigo-800 { color: #3730a3; }');
        printWindow.document.write('.text-sm { font-size: 13px; }');
        printWindow.document.write('.mt-6 { margin-top: 24px; }');
        printWindow.document.write('.pl-6 { padding-left: 24px; }');
        printWindow.document.write('.text-slate-600 { color: #475569; }');
        
        printWindow.document.write('@media print {');
        printWindow.document.write('  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 10px; margin: 0; }');
        printWindow.document.write('  table { border-collapse: collapse !important; border: 1px solid #1e293b !important; }');
        printWindow.document.write('  td { border-bottom: 1px solid #cbd5e1 !important; border-right: 1px solid #cbd5e1 !important; }');
        printWindow.document.write('  tr.border-double td { border-bottom: 4px double #1e293b !important; }');
        printWindow.document.write('  tr:last-child td { border-bottom: none !important; }');
        printWindow.document.write('  td:last-child { border-right: none !important; }');
        printWindow.document.write('  .bg-indigo-50\\/60 { background-color: #f0f9ff !important; color: #075985 !important; }');
        printWindow.document.write('  .bg-slate-50\\/50 { background-color: #f8fafc !important; }');
        printWindow.document.write('}');
        printWindow.document.write('<\/style>');
        printWindow.document.write('<\/head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('<\/body><\/html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
