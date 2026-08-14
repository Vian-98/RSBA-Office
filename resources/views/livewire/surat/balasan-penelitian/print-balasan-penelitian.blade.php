<div id="print-balasan-penelitian-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @if ($suratBalasanPenelitian)
        @php
            $tglSuratIndo = \Carbon\Carbon::parse($suratBalasanPenelitian->tgl)->translatedFormat('d F Y');
            $tglMasukIndo = $suratBalasanPenelitian->tgl_surat_masuk ? \Carbon\Carbon::parse($suratBalasanPenelitian->tgl_surat_masuk)->translatedFormat('d F Y') : '....................';
            $namaDirektur = optional($suratBalasanPenelitian->direktur)->full_nama ?? 'dr. Rachmawati, MPH';
            $nipDirektur  = optional($suratBalasanPenelitian->direktur)->nip ?? '24170002';
        @endphp

        <style>
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 15mm 20mm 15mm 20mm;
                }
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    font-family: Arial, Helvetica, sans-serif !important;
                    color: #000 !important;
                    background: #fff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .no-print {
                    display: none !important;
                }
                .page-break {
                    page-break-before: always !important;
                    break-before: page !important;
                    height: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                }
            }
        </style>

        {{-- ============================================================
             HALAMAN 1: SURAT BALASAN PENELITIAN
             ============================================================ --}}
        <div style="min-height: 250mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- KOP SURAT RESMI RS BINTANG AMIN --}}
                <div style="display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 18px;">
                    <div style="width: 80px; text-align: center; flex-shrink: 0;">
                        <img src="{{ asset('logo-fallback.png') }}" alt="Logo RSBA" style="height: 60px; max-width: 80px; object-fit: contain;" onerror="this.style.display='none'">
                    </div>
                    <div style="flex: 1; text-align: center; padding-right: 80px;">
                        <div style="font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #000; letter-spacing: 0.5px; margin: 0; line-height: 1.2;">
                            RUMAH SAKIT BINTANG AMIN
                        </div>
                        <div style="font-size: 11pt; font-weight: bold; color: #111; margin: 2px 0 0 0; line-height: 1.2;">
                            PT. BINTANG AMIN HUSADA
                        </div>
                        <div style="font-size: 9pt; color: #333; margin-top: 3px; line-height: 1.3;">
                            Jl. Pramuka No. 27 Kemiling – Bandar Lampung | Telp: (0721) 273601 - 273608<br>
                            Email: cs@rspba.co.id / sdm@rspba.co.id | Website: www.rspba.co.id
                        </div>
                    </div>
                </div>

                {{-- Tanggal Surat --}}
                <div style="text-align: right; margin-bottom: 14px; font-size: 11pt;">
                    Bandar Lampung, {{ $tglSuratIndo }}
                </div>

                {{-- Metadata Surat --}}
                <table style="width: 100%; font-size: 11pt; margin-bottom: 16px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 90px; vertical-align: top; padding: 2px 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $suratBalasanPenelitian->no }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0;">Lampiran</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0;">1 (satu) Berkas</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Perihal</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center; font-weight: bold;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $suratBalasanPenelitian->perihal_surat_masuk }}</td>
                    </tr>
                </table>

                {{-- Kepada Yth --}}
                <div style="margin-bottom: 16px; font-size: 11pt; line-height: 1.4;">
                    <div>Kepada Yth;</div>
                    @if($suratBalasanPenelitian->tujuan_nama)
                        <div style="font-weight: bold;">{{ $suratBalasanPenelitian->tujuan_nama }}</div>
                    @endif
                    <div style="font-weight: bold;">Fakultas {{ $suratBalasanPenelitian->tujuan_fakultas }} – Universitas {{ $suratBalasanPenelitian->tujuan_universitas }}</div>
                    <div>Di &nbsp; tempat</div>
                </div>

                {{-- Salam Pembuka --}}
                <div style="margin-bottom: 10px; font-weight: bold; font-size: 11pt;">
                    Dengan hormat,
                </div>

                {{-- Paragraf 1 --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Menindaklanjuti surat dari Fakultas {{ $suratBalasanPenelitian->tujuan_fakultas }} - Universitas {{ $suratBalasanPenelitian->tujuan_universitas }}, Nomor: {{ $suratBalasanPenelitian->nomor_surat_masuk ?: '....................' }} tentang {{ $suratBalasanPenelitian->perihal_surat_masuk }} di RS. Bintang Amin Lampung, berdasarkan surat tersebut maka kami :
                </p>

                {{-- Identitas RS --}}
                <table style="width: calc(100% - 2rem); margin-left: 2rem; font-size: 11pt; margin-bottom: 10px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 220px; vertical-align: top; padding: 2px 0;">Nama Perusahaan/Instansi</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">RS. Bintang Amin Lampung</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0;">Alamat</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0;">Jl. Pramuka No. 27, Kemiling – Bandar Lampung</td>
                    </tr>
                </table>

                {{-- Paragraf Pernyataan Bersedia --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Menyatakan bahwa kami <strong>bersedia</strong> menerima Mahasiswa/i Fakultas {{ $suratBalasanPenelitian->tujuan_fakultas }} Universitas {{ $suratBalasanPenelitian->tujuan_universitas }} untuk Penelitian di RS. Bintang Amin.
                </p>

                <p style="text-align: justify; margin: 0 0 10px 0; font-size: 11pt;">
                    Adapun identitas mahasiswa tersebut adalah sebagai berikut :
                </p>

                {{-- Tabel Identitas Mahasiswa --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10pt; margin-bottom: 16px;">
                    <thead>
                        <tr style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                            <th style="border: 1px solid #000; padding: 6px; width: 35px; text-align: center;">No.</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">NAMA</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; width: 100px; text-align: center;">NPM</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">FAKULTAS /<br>PERGURUAN TINGGI</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">JUDUL/TOPIK<br>PENELITIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suratBalasanPenelitian->mahasiswa as $mIdx => $mhs)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $mIdx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; font-weight: bold; vertical-align: top;">{{ $mhs->nama }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-family: monospace; vertical-align: top;">{{ $mhs->npm ?: '-' }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">{{ $mhs->fakultas_pt }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">{{ $mhs->judul_penelitian ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Penutup --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Demikian surat pemberitahuan ini, atas kerjasamanya diucapkan terima kasih.
                </p>
            </div>

            {{-- Kolom TTD Direktur --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                <div style="text-align: center; min-width: 220px; font-size: 11pt;">
                    <div>Hormat Kami</div>
                    <div style="margin-bottom: 75px;">Direktur,</div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaDirektur }}</div>
                </div>
            </div>
        </div>

        {{-- Page Break untuk Lampiran --}}
        <div class="page-break" style="page-break-before: always; break-before: page; margin-top: 25px; border-top: 1px dashed #cbd5e1; padding-top: 15px;"></div>

        {{-- ============================================================
             HALAMAN 2: LAMPIRAN RINCIAN BIAYA PENELITIAN
             ============================================================ --}}
        <div style="min-height: 250mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- Header Lampiran --}}
                <div style="font-weight: bold; font-size: 11pt; margin-bottom: 4px;">
                    Lampiran Surat
                </div>
                <table style="width: 100%; font-size: 11pt; margin-bottom: 20px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 90px; vertical-align: top; padding: 2px 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $suratBalasanPenelitian->no }}</td>
                    </tr>
                </table>

                {{-- Tabel Lampiran Biaya Sesuai Format Word --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5pt; margin-bottom: 16px;">
                    <thead>
                        <tr style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                            <th style="border: 1px solid #000; padding: 8px 6px; width: 40px; text-align: center;">NO</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; text-align: left;">Biaya Penelitian & Pendidikan</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 140px; text-align: right;">Jasa Sarana</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 140px; text-align: right;">Jasa Pelayanan</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 160px; text-align: right;">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suratBalasanPenelitian->biaya as $bIdx => $item)
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $bIdx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; vertical-align: top;">
                                    <div style="font-weight: bold;">{{ $item->keterangan }}</div>
                                    <div style="font-size: 9.5pt; color: #475569;">{{ $item->jumlah_orang }} org x Rp. {{ number_format($item->jasa_sarana + $item->jasa_pelayanan, 0, ',', '.') }}</div>
                                </td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; vertical-align: top;">
                                    Rp. {{ number_format($item->jasa_sarana, 0, ',', '.') }}
                                </td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; vertical-align: top;">
                                    Rp. {{ number_format($item->jasa_pelayanan, 0, ',', '.') }}
                                </td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-weight: bold; vertical-align: top;">
                                    Rp. {{ number_format($item->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; background-color: #f1f5f9;">
                            <td colspan="4" style="border: 1px solid #000; padding: 8px 10px; text-align: center; text-transform: uppercase;">Total</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 11pt;">
                                Rp. {{ number_format($suratBalasanPenelitian->total_biaya, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Catatan Administrasi Sesuai Template Word --}}
                <div style="font-style: italic; font-size: 11pt; margin-bottom: 30px; line-height: 1.4;">
                    Menyelesaikan biaya Administrasi di kasir sebelum pelaksanaan penelitian dimulai.
                </div>
            </div>

            {{-- Kolom Tanda Tangan Lampiran --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <div style="text-align: center; min-width: 220px; font-size: 11pt;">
                    <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
                    <div style="margin-bottom: 75px;">Direktur,</div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaDirektur }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
