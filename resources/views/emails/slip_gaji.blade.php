<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #334155; line-height: 1.4; margin: 0; padding: 10px; background-color: #f8fafc; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 12px 18px; border: 1px solid #cbd5e1; border-radius: 12px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: 1.5px; color: #0f172a; }
        
        .info-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; border-bottom: 4px double #1e293b; }
        .info-table tr { border-top: 1px solid #1e293b; }
        .info-table td { padding: 3px 0; font-weight: 600; }
        
        .main-table { width: 100%; border-collapse: collapse; border: 1px solid #1e293b; }
        .main-table td { padding: 4px 10px; border-bottom: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; }
        .main-table tr:last-child td { border-bottom: none; }
        .main-table td:last-child { border-right: none; }
        
        .border-double { border-bottom: 4px double #1e293b !important; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f8fafc; }
        .bg-indigo { background-color: #f0f9ff; color: #075985; }
        .signature-block { margin-top: 16px; font-size: 11px; color: #475569; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            @php
                $logoPath = null;
                if (isset($rs) && $rs && $rs->logo && file_exists(storage_path('app/public/' . $rs->logo))) {
                    $logoPath = storage_path('app/public/' . $rs->logo);
                }
                
                // Fallback to local PNG logo
                if (!$logoPath && file_exists(public_path('logo-fallback.png'))) {
                    $logoPath = public_path('logo-fallback.png');
                }
                
                $logoSrc = null;
                if ($logoPath) {
                    if (isset($message)) {
                        $logoSrc = $message->embed($logoPath);
                    } else {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $mime = mime_content_type($logoPath);
                        $logoSrc = 'data:' . $mime . ';base64,' . $logoData;
                    }
                }
            @endphp
            @if ($logoSrc)
                <img src="{{ $logoSrc }}" style="height: 40px; width: auto; margin-bottom: 6px;" alt="logo-RSBA" />
            @endif
            <h2>RS BINTANG AMIN</h2>
        </div>

        <table class="info-table">
            <tbody>
                <tr>
                    <td style="width: 90px;">Nama</td>
                    <td style="width: 10px;">:</td>
                    <td>{{ $slipData['nama'] }}</td>
                </tr>
                <tr>
                    <td>NIP</td>
                    <td>:</td>
                    <td>{{ $slipData['nip'] }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ $slipData['jabatan'] }}</td>
                </tr>
                <tr>
                    <td>Bulan</td>
                    <td>:</td>
                    <td>{{ $slipData['periode'] }}</td>
                </tr>
            </tbody>
        </table>

        <table class="main-table">
            <tbody>
                <tr>
                    <td style="width: 50%;">Gaji Pokok</td>
                    <td style="width: 15%;"></td>
                    <td style="width: 10%; text-align: center;">Rp</td>
                    <td style="width: 25%;" class="text-right">{{ number_format($slipData['gaji_pokok'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tj. Tetap</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Tj. Kehadiran</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Tj. Lain – Lain</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Tj. Jabatan</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">{{ number_format($slipData['tunjangan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tj. Shift</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Tj. Radiasi</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Uang Lembur</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #1e293b;">
                    <td>Tj. Hari Raya</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr class="font-bold bg-gray border-double">
                    <td>TOTAL GAJI</td>
                    <td></td>
                    <td class="text-center">Rp.</td>
                    <td class="text-right">{{ number_format($slipData['gaji_pokok'] + $slipData['tunjangan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="height: 6px; padding: 0; border: none;"></td>
                </tr>
                <tr>
                    <td>Pot. Absensi</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Cash Bon</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Pot. Obat / Perawatan</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Pot. BPJS Kesehatan</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">{{ number_format($slipData['bpjs_kes'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pot. BPJS TK</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">{{ number_format($slipData['bpjs_ket'], 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #1e293b;">
                    <td>Potongan Lain-lain</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr class="font-bold bg-gray border-double">
                    <td>TOTAL POTONGAN</td>
                    <td></td>
                    <td class="text-center">Rp.</td>
                    <td class="text-right">{{ number_format($slipData['bpjs_kes'] + $slipData['bpjs_ket'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="height: 6px; padding: 0; border: none;"></td>
                </tr>
                <tr>
                    <td>PPh Pasal 21</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">{{ number_format($slipData['pajak'], 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #1e293b;">
                    <td>Pot. Bank</td>
                    <td></td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">-</td>
                </tr>
                <tr class="font-bold bg-indigo border-double">
                    <td>PENGHASILAN NETTO</td>
                    <td></td>
                    <td class="text-center">Rp.</td>
                    <td class="text-right">{{ number_format($slipData['gaji_bersih'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-block">
            <p>Bandar Lampung, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Wadir SDM & Umum</p>
            <div style="height: 35px;"></div>
            <p class="font-bold" style="margin: 0;">Riyanti, SP., M.Kes</p>
        </div>
    </div>
</body>
</html>
