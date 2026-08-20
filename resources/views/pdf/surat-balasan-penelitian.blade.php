<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Balasan Penelitian - {{ $surat->no }}</title>
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
            padding: 5px 8px;
            vertical-align: middle;
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
        
        $namaDirektur = ($surat->direktur && !str_contains(strtolower($surat->direktur->nama ?? ''), 'super admin'))
            ? ($surat->direktur->full_nama ?: $surat->direktur->nama)
            : 'dr. Rachmawati, MPH';

        $mahasiswaList = $surat->mahasiswa ?? collect([]);
        $biayaList = $surat->biaya ?? collect([]);
        $totalBiaya = (float) $surat->total_biaya;
    @endphp

    {{-- ============================================================
         HALAMAN 1: SURAT BALASAN PENELITIAN UTAMA
         ============================================================ --}}
    <div style="position: relative; min-height: 255mm;">
        {{-- KOP SURAT (5.54cm x 2.75cm) --}}
        <div class="kop-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="RS BINTANG AMIN" class="kop-logo">
            @endif
        </div>

        {{-- Tanggal Surat --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.5; margin: 0;">
            Bandar Lampung, {{ $tglSuratIndo }}
        </div>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Metadata Surat --}}
        <table class="meta-table" style="width: 100%; font-size: 11pt; margin: 0; line-height: 1.25;">
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
                <td style="font-weight: bold;">{{ $surat->perihal_surat_masuk ?: 'Izin Penelitian dan Pengambilan Data' }}</td>
            </tr>
        </table>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Kepada Yth --}}
        <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
            <div>Kepada Yth;</div>
            @if($surat->tujuan_nama)
                <div>{{ $surat->tujuan_nama }}</div>
            @endif
            <div style="font-weight: bold;">Fakultas {{ $surat->tujuan_fakultas }} – Universitas {{ $surat->tujuan_universitas }}</div>
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
            Dengan hormat,
        </div>

        {{-- Paragraf 1 --}}
        <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
            Menindaklanjuti surat dari Fakultas {{ $surat->tujuan_fakultas }} - Universitas {{ $surat->tujuan_universitas }}, Nomor: {{ $surat->nomor_surat_masuk ?: '....................' }} tentang {{ $surat->perihal_surat_masuk ?: 'Izin Penelitian' }} di RS. Bintang Amin Lampung, berdasarkan surat tersebut maka kami :
        </div>

        {{-- Identitas RS --}}
        <table class="meta-table" style="width: 100%; font-size: 11pt; margin: 4px 0; line-height: 1.35;">
            <tr>
                <td style="width: 200px;">Nama Perusahaan/Instansi</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td style="font-weight: bold;">RS. Bintang Amin Lampung</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td style="text-align: center;">:</td>
                <td>Jl. Pramuka No. 27, Kemiling – Bandar Lampung</td>
            </tr>
        </table>

        {{-- Paragraf Pernyataan Bersedia --}}
        <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
            Menyatakan bahwa kami <strong>bersedia</strong> menerima Mahasiswa/i Fakultas {{ $surat->tujuan_fakultas }} Universitas {{ $surat->tujuan_universitas }} untuk Penelitian di RS. Bintang Amin.
        </div>

        <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 4px 0 6px 0;">
            Adapun identitas mahasiswa tersebut adalah sebagai berikut :
        </div>

        {{-- Tabel Identitas Mahasiswa --}}
        <table class="table-lampiran" style="margin-bottom: 8px;">
            <thead>
                <tr style="background-color: #ffffff;">
                    <th style="width: 30px;">No.</th>
                    <th style="text-align: left; padding-left: 8px;">NAMA</th>
                    <th style="width: 90px;">NPM</th>
                    <th style="width: 140px; text-align: left; padding-left: 8px;">FAKULTAS /<br>PERGURUAN TINGGI</th>
                    <th style="text-align: left; padding-left: 8px;">JUDUL/TOPIK<br>PENELITIAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswaList as $mIdx => $mhs)
                    <tr>
                        <td style="text-align: center;">{{ $mIdx + 1 }}</td>
                        <td style="font-weight: bold; text-align: left;">{{ $mhs->nama ?? '-' }}</td>
                        <td style="text-align: center;">{{ $mhs->npm ?? '-' }}</td>
                        <td style="text-align: left;">{{ $mhs->fakultas_pt ?? "Fakultas {$surat->tujuan_fakultas} / {$surat->tujuan_universitas}" }}</td>
                        <td style="text-align: left;">{{ $mhs->judul_penelitian ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">Belum ada data mahasiswa</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Penutup --}}
        <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0 0 10px 0; text-indent: 0;">
            Demikian surat pemberitahuan ini, atas kerjasamanya diucapkan terima kasih.
        </div>

        {{-- Kolom TTD Direktur --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin-top: 10px;">
            <div>Hormat Kami</div>
            <div>Direktur,</div>

            @if(!empty($qrBase64))
                <div style="padding: 4px 0;">
                    <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 58px; height: 58px; display: block;" alt="QR Code Verifikasi">
                </div>
            @else
                <div style="height: 55px;"></div>
            @endif

            <div style="font-weight: bold;">{{ $namaDirektur }}</div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <div class="footer-kontak" style="position: absolute; bottom: 0; left: 0; right: 0;">
            Jl. Pramuka No. 27, Kemiling - Bandar Lampung, Telp (0721) 273 606, Call Center (0831 0851 1401), IGD 24 Jam (0821 7520 6573)
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         HALAMAN 2: LAMPIRAN RINCIAN BIAYA PENELITIAN
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
        <table class="meta-table" style="width: 100%; font-size: 11pt; margin: 0; line-height: 1.25;">
            <tr>
                <td style="width: 75px;">Nomor</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td>{{ $surat->no }}</td>
            </tr>
        </table>

        {{-- 1 ENTER --}}
        <div style="height: 14px;"></div>

        {{-- Catatan Administrasi --}}
        <div style="font-style: italic; font-size: 11pt; margin-bottom: 12px; line-height: 1.4;">
            Menyelesaikan biaya Administrasi di kasir sebelum pelaksanaan penelitian dimulai.
        </div>

        {{-- Tabel Lampiran Biaya Sesuai Format Word --}}
        <table class="table-lampiran" style="margin-bottom: 16px;">
            <thead>
                <tr style="background-color: #ffffff;">
                    <th style="width: 35px;">NO</th>
                    <th style="text-align: left; padding-left: 8px;">Biaya Penelitian & Pendidikan</th>
                    <th style="width: 110px; text-align: right; padding-right: 8px;">Jasa Sarana</th>
                    <th style="width: 110px; text-align: right; padding-right: 8px;">Jasa Pelayanan</th>
                    <th style="width: 120px; text-align: right; padding-right: 8px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($biayaList as $bIdx => $item)
                    @php
                        $sarana = (float) ($item->jasa_sarana ?? 0);
                        $pelayanan = (float) ($item->jasa_pelayanan ?? 0);
                        $jmlOrg = (int) ($item->jumlah_orang ?? 1);
                        $subtotal = ($sarana + $pelayanan) * ($jmlOrg ?: 1);
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $bIdx + 1 }}</td>
                        <td style="text-align: left;">
                            <div>{{ $item->keterangan ?? '-' }}</div>
                            <div style="font-size: 9pt; color: #555;">{{ $jmlOrg }} org x Rp. {{ number_format($sarana + $pelayanan, 0, ',', '.') }}</div>
                        </td>
                        <td style="text-align: right;">
                            Rp. {{ number_format($sarana, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            Rp. {{ number_format($pelayanan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            Rp. {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">Belum ada rincian biaya</td>
                    </tr>
                @endforelse
                <tr style="font-weight: bold;">
                    <td colspan="4" style="text-align: center; text-transform: uppercase;">Total</td>
                    <td style="text-align: right;">
                        Rp. {{ number_format($totalBiaya, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="height: 18px;"></div>

        {{-- Kolom TTD Direktur Lampiran --}}
        <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
            <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
            <div>Direktur,</div>

            @if(!empty($qrBase64))
                <div style="padding: 4px 0;">
                    <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 58px; height: 58px; display: block;" alt="QR Code Verifikasi">
                </div>
            @else
                <div style="height: 55px;"></div>
            @endif

            <div style="font-weight: bold;">{{ $namaDirektur }}</div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <div class="footer-kontak" style="position: absolute; bottom: 0; left: 0; right: 0;">
            Jl. Pramuka No. 27, Kemiling - Bandar Lampung, Telp (0721) 273 606, Call Center (0831 0851 1401), IGD 24 Jam (0821 7520 6573)
        </div>
    </div>
</body>
</html>
