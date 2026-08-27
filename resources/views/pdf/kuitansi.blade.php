<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KWITANSI - {{ $kuitansi->nomor }}</title>
    <style>
        @page {
            size: a5 landscape;
            margin: 8mm 12mm 6mm 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo {
            height: 52px;
            max-width: 80px;
        }
        .rs-name {
            font-size: 13.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .rs-address {
            font-size: 8pt;
            color: #333;
            margin-top: 2px;
            text-align: center;
        }
        .title-wrapper {
            text-align: center;
            margin: 8px 0 16px 0;
        }
        .title {
            font-size: 13pt;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            letter-spacing: 2px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .content-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .label-col {
            width: 155px;
            font-weight: bold;
            color: #000;
        }
        .colon-col {
            width: 15px;
            text-align: left;
            color: #000;
        }
        .value-col {
            color: #111;
        }
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 16px 0 14px 0;
        }
        .footer-table {
            width: 100%;
        }
        .nominal-box {
            background-color: #e5e9f2;
            padding: 10px 24px;
            font-size: 17pt;
            font-weight: bold;
            color: #0f172a;
            display: inline-block;
            text-align: center;
            min-width: 160px;
        }
        .audit-footer {
            margin-top: 20px;
            font-size: 7.5pt;
            color: #94a3b8;
            line-height: 1.3;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }
        .item-table td {
            padding: 2px 4px;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    {{-- Header / Kop Surat --}}
    <table class="header-table">
        <tr>
            <td style="width: 75px; vertical-align: middle;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" class="logo" alt="Logo" />
                @endif
            </td>
            <td style="vertical-align: middle; padding-right: 75px;">
                <div class="rs-name">RS. BINTANG AMIN</div>
                <div class="rs-address">
                    Jl. Pramuka No. 27 Kemiling Bandar Lampung, Bandar Lampung<br>
                    Telp : (0721) 273601-273608 Email : cs@rspba.co.id
                </div>
            </td>
        </tr>
    </table>

    {{-- Judul Kwitansi --}}
    <div class="title-wrapper">
        <span class="title">KWITANSI</span>
    </div>

    {{-- Tabel Isi Data --}}
    <table class="content-table">
        <tr>
            <td class="label-col">No.</td>
            <td class="colon-col">:</td>
            <td class="value-col">{{ $kuitansi->nomor }}</td>
        </tr>
        <tr>
            <td class="label-col">Telah Diterima Dari</td>
            <td class="colon-col">:</td>
            <td class="value-col">{{ $kuitansi->diterima_dari ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Banyaknya Uang</td>
            <td class="colon-col">:</td>
            <td class="value-col">{{ ucfirst(strtolower($kuitansi->terbilang)) }}.</td>
        </tr>
        <tr>
            <td class="label-col">Untuk Pembayaran</td>
            <td class="colon-col">:</td>
            <td class="value-col">
                {{ $kuitansi->keterangan }}
                @if($kuitansi->details && $kuitansi->details->count() > 1)
                    <table class="item-table">
                        @foreach($kuitansi->details as $idx => $item)
                            <tr>
                                <td style="width: 18px; color: #64748b;">{{ $idx + 1 }}.</td>
                                <td>{{ $item->keterangan }}</td>
                                <td style="text-align: right; font-family: monospace;">{{ formatRupiah($item->nominal, true, false) }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Nominal, QR Legalitas, & Tanda Tangan --}}
    <table class="footer-table">
        <tr>
            {{-- Kotak Nominal di Kiri --}}
            <td style="vertical-align: middle; width: 42%;">
                <div class="nominal-box">
                    {{ formatRupiah($kuitansi->jumlah, true, false) }}
                </div>
            </td>

            {{-- QR Code Legalitas Dokumen di Tengah --}}
            <td style="vertical-align: middle; text-align: center; width: 20%;">
                @if(!empty($qrBase64))
                    <img src="data:image/png;base64,{{ $qrBase64 }}" style="height: 52px; width: 52px; margin: 0 auto;" alt="QR" />
                    <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">Verifikasi Dokumen</div>
                @endif
            </td>


            {{-- Kolom TTD di Kanan --}}
            <td style="vertical-align: top; text-align: center; width: 38%;">
                <div style="font-size: 9.5pt; color: #111;">
                    Bandar lampung, {{ $kuitansi->tanggal ? \Carbon\Carbon::parse($kuitansi->tanggal)->translatedFormat('d M Y') : date('d M Y') }}
                </div>
                <div style="font-size: 9.5pt; margin-top: 2px; color: #111;">Penerima,-</div>
                
                {{-- Ruang tanda tangan basah / paraf kasir --}}
                <div style="height: 48px;"></div>

                <div style="font-size: 9.5pt; color: #111;">
                    {{ $kuitansi->penerima_nama }}
                </div>
            </td>
        </tr>
    </table>



    {{-- Audit Footer di Kiri Bawah --}}
    <div class="audit-footer">
        <div>Dicetak : {{ now()->translatedFormat('d M Y - H:i:s') }}</div>
        <div>Oleh : {{ auth()->user()->karyawan->full_nama ?? auth()->user()->name }}</div>
    </div>
</body>
</html>
