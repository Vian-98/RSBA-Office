<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Balasan PKL - {{ $surat->no }}</title>
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
        .page-break {
            page-break-before: always;
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
            padding: 0;
            vertical-align: top;
        }
        .table-lampiran {
            width: 100%;
            border: 1.5px solid #000;
            font-size: 10pt;
            margin: 0;
        }
        .table-lampiran th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
        }
        .table-lampiran td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .text-justify {
            text-align: justify;
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
        $tglMasukIndo = $surat->tgl_surat_masuk ? \Carbon\Carbon::parse($surat->tgl_surat_masuk)->locale('id')->translatedFormat('d F Y') : '....................';
        $tglMulaiIndo = $surat->tgl_mulai ? \Carbon\Carbon::parse($surat->tgl_mulai)->locale('id')->translatedFormat('d F Y') : '-';
        $tglSelesaiIndo = $surat->tgl_selesai ? \Carbon\Carbon::parse($surat->tgl_selesai)->locale('id')->translatedFormat('d F Y') : '-';
        
        $namaDirektur = ($surat->direktur && !str_contains(strtolower($surat->direktur->nama ?? ''), 'super admin'))
            ? ($surat->direktur->full_nama ?: $surat->direktur->nama)
            : 'dr. Rachmawati, MPH';

        $hasOrientasi = (float)$surat->snap_biaya_orientasi > 0;
        $namaUniv = $surat->display_universitas;
        $listMahasiswa = $surat->mahasiswa ?? collect([]);
    @endphp

    {{-- ============================================================
         HALAMAN 1: SURAT BALASAN PKL UTAMA
         ============================================================ --}}
    <div style="position: relative; min-height: 255mm;">
        {{-- KOP SURAT (5.54cm x 2.75cm) --}}
        <div class="kop-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="RS BINTANG AMIN" class="kop-logo">
            @endif
        </div>

        {{-- Tanggal Surat (Rata Kiri) --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.5; margin: 0;">
            Bandar Lampung, {{ $tglSuratIndo }}
        </div>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Metadata Surat (Nomor / Lampiran / Perihal) --}}
        <table class="meta-table" style="width: 100%; font-size: 11pt; line-height: 1.25; margin: 0;">
            <tr>
                <td style="width: 75px;">Nomor</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td>{{ $surat->no }}</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td style="text-align: center;">:</td>
                <td>1 (satu) Berkas</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Perihal</td>
                <td style="text-align: center; font-weight: bold;">:</td>
                <td style="font-weight: bold;">Biaya dan Izin Praktik</td>
            </tr>
        </table>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Blok Kepada Yth --}}
        <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
            <div>KepadaYth;</div>
            @if($surat->tujuan_nama)
                <div>{{ $surat->tujuan_nama }}</div>
            @endif
            <div style="font-weight: bold;">{{ $namaUniv }}</div>
            @if($surat->tujuan_alamat)
                <div>{{ $surat->tujuan_alamat }}</div>
            @endif
            <div>Di</div>
            <div style="padding-left: 28px;">Tempat</div>
        </div>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Salam Pembuka --}}
        <div style="font-size: 11pt; line-height: 1.5; margin: 0;">
            Assalamu’alaikum Wr Wb
        </div>

        {{-- Paragraf 1 (Justified, Line 1.5, Indent 0cm / Special none) --}}
        <div class="text-justify" style="font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
            Menindaklanjuti surat {{ $namaUniv }} dengan nomor surat : {{ $surat->nomor_surat_masuk ?: '....................' }} tanggal {{ $tglMasukIndo }}, tentang Surat Izin Praktik dengan Jumlah Mahasiswa/i {{ $surat->jumlah_mahasiswa }} orang. Pelaksanaan Praktik tersebut akan dilaksanakan pada tanggal <strong>{{ $tglMulaiIndo }} s.d {{ $tglSelesaiIndo }}.</strong>
        </div>

        {{-- Paragraf 2 (Justified, Line 1.5, Indent 0cm) --}}
        <div class="text-justify" style="font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
            Pada dasarnya pihak RS Bintang Amin Lampung, <strong>Bersedia</strong> memberikan izin Kunjungan Rumah Sakit kepada Mahasiswa/i Prodi {{ $surat->prodi }} {{ $namaUniv }} dengan ketentuan sebagai berikut :
        </div>

        {{-- 5 Poin Ketentuan (Justified, Line 1.5, Hanging indent) --}}
        @php $poinNo = 1; @endphp
        <table style="width: 100%; font-size: 11pt; line-height: 1.5; text-align: justify; margin: 0;">
            <tr>
                <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                <td style="vertical-align: top; padding: 0; text-align: justify;">
                    Biaya praktik Mahasiswa/i Rp. {{ number_format($surat->snap_biaya_praktik, 0, ',', '.') }},-/Mahasiswa/i /Bulan (sesuai dengan Surat Keputusan Direktur Nomor {{ $surat->snap_nomor_sk ?: '023/Kpts-S4/PBA-A10/10.01.22' }})
                </td>
            </tr>
            @if($hasOrientasi)
                <tr>
                    <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                    <td style="vertical-align: top; padding: 0; text-align: justify;">
                        Biaya Orientasi Rp {{ number_format($surat->snap_biaya_orientasi, 0, ',', '.') }}./Mahasiswa/i
                    </td>
                </tr>
            @endif
            <tr>
                <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                <td style="vertical-align: top; padding: 0; text-align: justify;">
                    Biaya dapat di transfer melalui rekening RSBA dengan Nomor Rekening <strong><em>555 00 888 12</em></strong> BNI atas nama <strong>RS Bintang Amin</strong>.
                </td>
            </tr>
            <tr>
                <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                <td style="vertical-align: top; padding: 0; text-align: justify;">
                    Menyelesaikan biaya Administrasi PKL sebelum pelaksanaan praktik dimulai.
                </td>
            </tr>
            <tr>
                <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                <td style="vertical-align: top; padding: 0; text-align: justify;">
                    Selama kunjungan  mahasiswa/i Wajib menerapkan protokol kesehatan 3M yaitu Menjaga jarak, Memakai masker dan Mencuci tangan
                </td>
            </tr>
        </table>

        {{-- Paragraf Penutup (Justified, Line 1.5, Indent 0cm) --}}
        <div class="text-justify" style="font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
            Demikian kami sampaikan, Atas perhatian dan kerjasamanya kami ucapkan terimakasih.
        </div>

        {{-- Salam Penutup --}}
        <div style="font-size: 11pt; line-height: 1.5; margin: 0;">
            Wassalamu’alaikum Wr Wb.
        </div>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Kolom TTD Direktur --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
            <div style="font-weight: bold;">RS. Bintang Amin</div>
            <div>Direktur</div>
            {{-- 3-4 ENTER spasi TTD --}}
            <div style="height: 55px;"></div>
            <div style="font-weight: bold;">{{ $namaDirektur }}</div>
        </div>

        {{-- Footer --}}
        <div class="footer-kontak" style="position: absolute; bottom: 0; left: 0; right: 0;">
            Jl. Pramuka No. 27, Kemiling - Bandar Lampung, Telp (0721) 273 606, Call Center (0831 0851 1401), IGD 24 Jam (0821 7520 6573)
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         HALAMAN 2: LAMPIRAN RINCIAN BIAYA PRAKTIK
         ============================================================ --}}
    <div style="position: relative; min-height: 255mm;">
        {{-- KOP SURAT (5.54cm x 2.75cm) --}}
        <div class="kop-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="RS BINTANG AMIN" class="kop-logo">
            @endif
        </div>

        {{-- Header Lampiran --}}
        <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
            Lampiran Surat
        </div>
        <table class="meta-table" style="width: 100%; font-size: 11pt; line-height: 1.25; margin: 0;">
            <tr>
                <td style="width: 75px;">Nomor</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td>{{ $surat->no }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Perihal</td>
                <td style="text-align: center; font-weight: bold;">:</td>
                <td style="font-weight: bold;">Biaya dan Izin Praktik</td>
            </tr>
        </table>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Judul Tabel (Tengah, Bold, En-dash) --}}
        <div style="text-align: center; font-weight: bold; font-size: 11pt; margin: 0;">
            Rincian Biaya Praktik di Rumah Sakit Bintang Amin – Lampung
        </div>

        {{-- Spasi sebelum tabel --}}
        <div style="height: 10px;"></div>

        {{-- Tabel Lampiran --}}
        <table class="table-lampiran">
            <thead>
                <tr style="background-color: #ffffff;">
                    <th style="width: 35px;">No</th>
                    <th>Biaya Praktek Kerja Lapangan</th>
                    <th style="width: 95px;">Jumlah Siswa</th>
                    <th style="width: 95px;">Lama Praktik</th>
                    <th style="width: 130px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: left;">
                        <div>Izin Praktek</div>
                        <div>Rp. {{ number_format($surat->snap_biaya_praktik, 0, ',', '.') }},-/ orang/ bulan</div>
                    </td>
                    <td style="text-align: center;">{{ $surat->jumlah_mahasiswa }} Orang</td>
                    <td style="text-align: center;">{{ $surat->lama_praktik_bulan }} Bulan</td>
                    <td style="text-align: left;">
                        Rp. {{ number_format($surat->total_biaya_praktik, 0, ',', '.') }},-
                    </td>
                </tr>
                @if($hasOrientasi)
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td style="text-align: left;">
                            <div>Orientasi Rp. {{ number_format($surat->snap_biaya_orientasi, 0, ',', '.') }},-/ orang</div>
                        </td>
                        <td style="text-align: center;">{{ $surat->jumlah_mahasiswa }} Orang</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: left;">
                            Rp. {{ number_format($surat->total_biaya_orientasi, 0, ',', '.') }},-
                        </td>
                    </tr>
                @endif
                <tr style="font-weight: bold;">
                    <td colspan="4" style="text-align: center;">Total</td>
                    <td style="text-align: left;">
                        Rp. {{ number_format($surat->grand_total_biaya, 0, ',', '.') }},-
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Tabel Daftar Mahasiswa jika ada --}}
        @if($listMahasiswa->count() > 0)
            <div style="margin-top: 14px; margin-bottom: 6px; font-weight: bold; font-size: 10pt;">
                Daftar Mahasiswa/i ({{ $listMahasiswa->count() }} Orang):
            </div>
            <table class="table-lampiran">
                <thead>
                    <tr style="background-color: #ffffff;">
                        <th style="width: 35px;">No</th>
                        <th style="text-align: left; padding-left: 8px;">Nama Mahasiswa/i</th>
                        <th style="width: 160px;">NPM / NIM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listMahasiswa as $mIdx => $mhs)
                        <tr>
                            <td style="text-align: center;">{{ $mIdx + 1 }}</td>
                            <td style="text-align: left;">{{ $mhs->nama }}</td>
                            <td style="text-align: center;">{{ $mhs->npm ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- 2 ENTER setelah tabel --}}
        <div style="height: 18px;"></div>

        {{-- Kolom TTD Direktur Lampiran --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
            <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
            <div style="font-weight: bold;">RS. Bintang Amin</div>
            <div>Direktur</div>
            {{-- 4 ENTER spasi TTD --}}
            <div style="height: 55px;"></div>
            <div style="font-weight: bold;">{{ $namaDirektur }}</div>
        </div>

        {{-- Footer --}}
        <div class="footer-kontak" style="position: absolute; bottom: 0; left: 0; right: 0;">
            Jl. Pramuka No. 27, Kemiling - Bandar Lampung, Telp (0721) 273 606, Call Center (0831 0851 1401), IGD 24 Jam (0821 7520 6573)
        </div>
    </div>
</body>
</html>
