<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Tugas - {{ $surat->no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 10mm 15mm 30mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            text-align: center;
            margin-bottom: 0;
            padding: 0;
        }
        .kop-logo {
            width: 5.54cm;
            height: 2.75cm;
            margin: 0 auto;
            display: block;
        }
        .footer-kontak {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 10px;
            text-align: center;
            font-size: 8pt;
            color: #000;
            line-height: 1.3;
        }
        table {
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .table-karyawan {
            width: 100%;
            border: 1.5px solid #000;
            font-size: 10pt;
            margin: 0;
        }
        .table-karyawan th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
        }
        .table-karyawan td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $logoPath = file_exists(public_path('logo-surat-rsba.jpg'))
            ? public_path('logo-surat-rsba.jpg')
            : (($rs && $rs->logo && file_exists(storage_path('app/public/' . $rs->logo))) 
                ? storage_path('app/public/' . $rs->logo) 
                : public_path('logo-fallback.png'));
        $logoSrc = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $tglSuratIndo = \Carbon\Carbon::parse($surat->tgl)->locale('id')->translatedFormat('d F Y');
        
        $namaDirektur = ($surat->direktur && !str_contains(strtolower($surat->direktur->nama ?? ''), 'super admin'))
            ? ($surat->direktur->full_nama ?: $surat->direktur->nama)
            : 'dr. Rachmawati, MPH';
        $nipDirektur = optional($surat->direktur)->nip ?: '24170002';

        $karyawanList = $surat->karyawanTugas ?? collect([]);
    @endphp

    <div style="position: relative; min-height: 255mm;">
        {{-- KOP SURAT (5.54cm x 2.75cm) --}}
        <div class="kop-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="RS BINTANG AMIN" class="kop-logo">
            @endif
        </div>

        {{-- Judul Surat --}}
        <div style="text-align: center; margin-bottom: 16px;">
            <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: underline;">
                SURAT PERINTAH TUGAS
            </div>
            <div style="font-size: 11pt; font-weight: bold; margin-top: 2px;">
                Nomor : {{ $surat->no }}
            </div>
        </div>

        {{-- Pemberi Perintah --}}
        <div style="margin-bottom: 10px; font-size: 11pt; line-height: 1.5;">
            <div style="margin-bottom: 4px;">Saya Yang Bertandatangan dibawah ini :</div>
            <table class="meta-table" style="width: 100%; font-size: 11pt; margin: 0; line-height: 1.35;">
                <tr>
                    <td style="width: 80px;">Nama</td>
                    <td style="width: 15px; text-align: center;">:</td>
                    <td style="font-weight: bold;">{{ $namaDirektur }}</td>
                </tr>
                <tr>
                    <td>NIP</td>
                    <td style="text-align: center;">:</td>
                    <td>{{ $nipDirektur }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: bold;">Direktur</td>
                </tr>
            </table>
        </div>

        {{-- Menugaskan Saudara --}}
        <div style="margin-bottom: 8px; font-size: 11pt; line-height: 1.5;">
            Menugaskan Saudara :
        </div>

        {{-- Tabel Karyawan yang Ditugaskan --}}
        <table class="table-karyawan" style="margin-bottom: 12px;">
            <thead>
                <tr style="background-color: #ffffff;">
                    <th style="width: 35px;">No</th>
                    <th style="text-align: left; padding-left: 8px;">Nama</th>
                    <th style="width: 140px;">NIP</th>
                    <th style="width: 180px; text-align: left; padding-left: 8px;">Jabatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawanList as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold; text-align: left;">{{ $item->nama ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->nip ?? '-' }}</td>
                        <td style="text-align: left;">{{ $item->jabatan_nama ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #666;">Belum ada karyawan yang ditugaskan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Isi Perintah Tugas --}}
        <div style="text-align: justify; margin: 0 0 8px 0; font-size: 11pt; line-height: 1.5;">
            {{ $surat->perihal }} :
        </div>

        {{-- Rincian Waktu & Tempat --}}
        <table class="meta-table" style="width: 100%; font-size: 11pt; margin-bottom: 12px; line-height: 1.4;">
            <tr>
                <td style="width: 120px;">Hari / Tanggal</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $surat->hari_tanggal_indo ?? $surat->hari_tanggal }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td style="text-align: center;">:</td>
                <td>{{ $surat->waktu }}</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $surat->tempat }}</td>
            </tr>
        </table>

        {{-- Penutup --}}
        <div style="text-align: justify; margin: 0 0 6px 0; font-size: 11pt; line-height: 1.5;">
            Demikian surat perintah ini dikeluarkan, agar dilaksanakan dengan penuh tanggungjawab.
        </div>
        <div style="text-align: justify; margin: 0 0 14px 0; font-size: 11pt; line-height: 1.5;">
            Atas perhatian serta kerjasamanya kami ucapkan terimakasih.
        </div>

        {{-- Kolom Tanda Tangan Direktur --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin-top: 10px;">
            <table class="meta-table" style="font-size: 11pt; margin: 0 0 4px 0; line-height: 1.25;">
                <tr>
                    <td style="width: 100px;">Dikeluarkan di</td>
                    <td style="width: 12px; text-align: center;">:</td>
                    <td style="font-weight: bold;">Bandar Lampung</td>
                </tr>
                <tr>
                    <td>Pada Tanggal</td>
                    <td style="text-align: center;">:</td>
                    <td>{{ $tglSuratIndo }}</td>
                </tr>
            </table>
            <div style="font-weight: bold; margin-top: 4px;">Direktur</div>

            @if(!empty($qrBase64))
                <div style="padding: 4px 0;">
                    <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 58px; height: 58px; display: block;" alt="QR Code Verifikasi">
                </div>
            @else
                <div style="height: 55px;"></div>
            @endif

            <div style="font-weight: bold;">{{ $namaDirektur }}</div>
            <div>{{ $nipDirektur }}</div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <div class="footer-kontak" style="position: absolute; bottom: 0; left: 0; right: 0;">
            Jl. Pramuka No. 27, Kemiling - Bandar Lampung, Telp (0721) 273 606, Call Center (0831 0851 1401), IGD 24 Jam (0821 7520 6573)
        </div>
    </div>
</body>
</html>
