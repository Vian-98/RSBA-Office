<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Disposisi - {{ $disposisi->no_agenda }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            border: 2px solid #000;
            padding: 0;
            max-width: 800px;
            margin: 0 auto;
        }
        .header-title {
            text-align: center;
            border-bottom: 2px solid #000;
            padding: 10px;
            font-weight: bold;
        }
        .header-title h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header-title h3 {
            margin: 2px 0 0 0;
            font-size: 14px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }
        .meta-table td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 100px;
            font-weight: bold;
        }
        .meta-table td.colon {
            width: 10px;
            font-weight: bold;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
            font-size: 11px;
        }
        .main-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .catatan-box {
            border-bottom: 2px solid #000;
            padding: 10px;
            min-height: 120px;
        }
        .catatan-title {
            font-weight: bold;
            margin-bottom: 6px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            padding: 6px 10px;
            vertical-align: top;
            width: 50%;
        }
        .check-mark {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
        }
        .qr-box {
            text-align: right;
            margin-top: 5px;
        }
        .qr-box img {
            width: 60px;
            height: 60px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <div class="no-print" style="position: fixed; top: 15px; right: 15px; background: #fff; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
        <button onclick="window.print()" style="background: #4f46e5; color: #fff; border: none; padding: 8px 16px; font-weight: bold; border-radius: 6px; cursor: pointer;">
            🖨️ Cetak Dokumen / Print
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header-title">
            <h2>LEMBAR PENERUS/DISPOSISI</h2>
            <h3>RS BINTANG AMIN</h3>
            <h3>DIREKTUR</h3>
        </div>

        <!-- Meta Info -->
        <table class="meta-table">
            <tr>
                <td class="label">No. Agenda</td>
                <td class="colon">:</td>
                <td style="font-weight: bold;">{{ $disposisi->no_agenda }}</td>
            </tr>
            <tr>
                <td class="label">Tgl Surat</td>
                <td class="colon">:</td>
                <td>{{ $disposisi->tgl_surat ? $disposisi->tgl_surat->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. Surat</td>
                <td class="colon">:</td>
                <td>{{ $disposisi->no_surat }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="colon">:</td>
                <td>{{ $disposisi->perihal }}</td>
            </tr>
            <tr>
                <td class="label">Asal Surat</td>
                <td class="colon">:</td>
                <td>{{ $disposisi->asal_surat }}</td>
            </tr>
        </table>

        <!-- Main Recipients Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 30px;">No</th>
                    <th rowspan="2">Kepada YTH</th>
                    <th colspan="3" style="width: 120px;">RTL</th>
                    <th colspan="2" style="width: 120px;">Tanda Terima</th>
                </tr>
                <tr>
                    <th style="width: 40px;">Info</th>
                    <th style="width: 40px;">Action</th>
                    <th style="width: 40px;">Arsip</th>
                    <th style="width: 60px;">Paraf</th>
                    <th style="width: 60px;">Tgl</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disposisi->details as $idx => $det)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ $det->nama_tujuan }}</td>
                        <td class="check-mark">@if($det->is_info) &#10003; @endif</td>
                        <td class="check-mark">@if($det->is_action) &#10003; @endif</td>
                        <td class="check-mark">@if($det->is_arsip) &#10003; @endif</td>
                        <td style="text-align: center; font-size: 10px;">
                            @if($det->status_tindak_lanjut === 'done') [PARAF] @endif
                        </td>
                        <td style="text-align: center; font-size: 9px;">
                            {{ $det->tgl_paraf ? $det->tgl_paraf->format('d/m/Y') : '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Catatan Direktur -->
        <div class="catatan-box">
            <div class="catatan-title">Catatan :</div>
            <div style="white-space: pre-line; line-height: 1.5;">
                {{ $disposisi->catatan ?: 'Terimakasih atas Laporannya.' }}
            </div>
        </div>

        <!-- Footer -->
        <table class="footer-table">
            <tr>
                <td>
                    <div>Diterima Oleh : {{ $disposisi->diterima_oleh ?: '...............' }}</div>
                    <div style="margin-top: 5px;">Tanggal : {{ $disposisi->tgl_diterima ? $disposisi->tgl_diterima->format('d/m/Y') : '...............' }}</div>
                </td>
                <td style="text-align: right;">
                    <div>Paraf Direktur :</div>
                    @if($disposisi->signature_hash)
                        @php
                            $verifyUrl = config('services.docstore.verify_app_url', env('VERIFY_APP_URL', 'http://localhost:5173'));
                            $targetVerifyLink = rtrim($verifyUrl, '/') . '/?hash=' . $disposisi->signature_hash;
                            if (!empty($disposisi->docstore_key)) {
                                $targetVerifyLink = rtrim($verifyUrl, '/') . '/?key=' . $disposisi->docstore_key;
                            }
                        @endphp
                        <div class="qr-box">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode($targetVerifyLink) }}" alt="QR Direktur" />
                            <div style="font-size: 8px; font-family: monospace;">HASH: {{ substr($disposisi->signature_hash, 0, 12) }}...</div>
                        </div>
                    @else
                        <div style="margin-top: 30px;">(...........................)</div>
                    @endif
                    <div style="margin-top: 5px;">Pukul : {{ $disposisi->jam_diterima ?: '...............' }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
