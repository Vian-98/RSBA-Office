<div id="print-sp3" style="width: 100%; margin: 0; padding: 15px; font-family: Arial, sans-serif;">
    <div align="center" class="mb-2">
        <img src="{{ ($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png') }}" style="height:60px;">
        <h2 class="bold text-lg uppercase">{{ $rs->nama }}</h2>
        <span class="text-sm">SURAT PERMINTAAN PROSES PEMBAYARAN<br>(Kontrak, Sundries, Material, dll.)</span>
    </div>
    <table cellpadding="3" align="center" style="width: 100%; font-size:11px; ">
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
        <tr>
            <td style="width:200px;" class="bold">Kepada</td>
            <td class="bold">:</td>
            <td>Wadir Keuangan</td>
            <td class="bold">Nomor</td>
            <td class="bold">:</td>
            <td><?= $suratSp3->no ?></td>
            {{-- style="<?= $data->hapus == 1 ? 'color:red; text-decoration: line-through;' : '' ?>" --}}
        </tr>
        <tr>
            <td><b>Dari</b></td>
            <td><b>:</b></td>
            <td><?= $suratSp3->jabatans->nama ?></td>
            <td><b>Tanggal</b></td>
            <td><b>:</b></td>
            <td><?= Carbon\Carbon::parse($suratSp3->tgl)->translatedFormat('d M Y') ?></td>
        </tr>
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
        <tr>
            <td colspan="6"><b>Terlampir dikirimkan dokumen pendukung pembayaran atas (Kontrak, Sundries, Material, dll) sbb:</b><br></td>
        </tr>
        <tr>
            <td valign="top"><b>Keterangan Pembayaran</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4"><?= nl2br(e($suratSp3->keterangan)) ?></td>
        </tr>
        <tr>
            <td><b>Nama Rekanan / Pelaksana</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $suratSp3->rekanan ?></td>
        </tr>
        <tr>
            <td valign="top"><b>Untuk Pembayaran</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4">
                <table cellpadding="3" border="1" style='font-size:10px;width:95%;border:1px solid black;'>
                    @foreach ($suratSp3->details as $item)
                        <tr style="border:1px solid black;">
                            <td>{{ $loop->iteration }}.</td>
                            <td style="width:60%">{{ $item->keterangan }}</td>
                            <td style="width:40%" align="right">{{ formatRupiah($item->nominal, true, false) }}</td>
                        </tr>
                    @endforeach
                    <tr style="border:1px solid black;font-weight:bold;">
                        <td colspan="2">Total</td>
                        <td align="right">{{ formatRupiah($suratSp3->details->sum('nominal'), true, false) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top"><b>Terbilang</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4">
                {{ terbilang($suratSp3->details->sum('nominal')) }}<br>
            </td>
        </tr>
        <tr>
            <td><b>Cara Pembayaran</b></td>
            <td><b>:</b></td>
            <td colspan="4">
                {{ $suratSp3->method_bayar }}
            </td>
        </tr>
        <tr>
            <td colspan="6"><b>Demikian untuk diterima dengan baik dan pelaksanaan pembuatan bukti kas / bank untuk proses pembayaran selanjutnya.<br>Atas perhatian dan kerjasamanya diucapkan
                    terima kasih.</b></td>
        </tr>
        @php
            $isManualSp3 = ($suratSp3->status === 'manual') || collect($this->approvals)->contains(function($item) {
                $st = strtolower(is_object($item['status'] ?? '') ? $item['status']->value : (string)($item['status'] ?? ''));
                return in_array($st, ['manual', 'approved manual']) || !empty($item['is_manual']);
            });
        @endphp

        @if ($isManualSp3 || $suratSp3->status !== 'approved')
        <tr>
            @forelse ($this->approvals as $item)
                <td colspan="6" align="right">
                    <table style="font-size:11px; width:33%; text-align: center;">
                        <tr>
                            <td>{{ ($item['status'] == 'Manual' || !empty($item['is_manual'])) ? 'Mengetahui' : $item['status'] . ' Oleh' }},</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <span style="display:block; height:60px; width:auto;"></span>
                                <span style="font-weight: bold; display:block; margin: 0 auto;">{{ $item['nama'] }}</span>
                                <span style="font-size:10px;">({{ $item['jabatan'][0]['nama'] ?? ' ' }})</span>
                            </td>
                        </tr>
                    </table>
                </td>
            @empty
                <td colspan="6" align="right">
                    <table style="font-size:11px; font-style:italic;">
                        <tr>
                            <td>Menunggu Persetujuan TTD Basah</td>
                        </tr>
                    </table>
                </td>
            @endforelse
        </tr>
        @endif
        <tr>
            <td colspan="6">
                <table style="font-size:8px;">
                    <tr>
                        <td>Tembusan:</td>
                    </tr>
                    <tr>
                        <td>1. Direktur</td>
                    </tr>
                    <tr>
                        <td>2. Arsip</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 5px;">
                            <img src="data:image/png;base64,{{ $this->generateHeaderQrCode }}" alt="QR Legalitas Dokumen" style="height: 55px; width: 55px; display: block;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <style>
        #print-sp3 .bold {
            font-weight: bold;
        }

        /* #print-sp3 thead th {
            border-bottom: 0.5px solid #666;
        }

        #print-sp3 tbody tr:last-child td {
            border-bottom: 0.5px solid #666;
        }

        #print-sp3 tfoot tr:last-child td {
            border-bottom: 0.5px solid #666;
        } */

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</div>
