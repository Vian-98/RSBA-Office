<div id="print-balasan-pkl-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @if ($suratBalasanPkl)
        @php
            $tglSuratIndo = \Carbon\Carbon::parse($suratBalasanPkl->tgl)->translatedFormat('d F Y');
            $tglMasukIndo = $suratBalasanPkl->tgl_surat_masuk ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_surat_masuk)->translatedFormat('d F Y') : '....................';
            $tglMulaiIndo = $suratBalasanPkl->tgl_mulai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_mulai)->translatedFormat('d F Y') : '-';
            $tglSelesaiIndo = $suratBalasanPkl->tgl_selesai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_selesai)->translatedFormat('d F Y') : '-';
            $namaDirektur = optional($suratBalasanPkl->direktur)->full_nama ?? 'dr. Rachmawati, MPH';
            $nipDirektur  = optional($suratBalasanPkl->direktur)->nip ?? '24170002';
            $hasOrientasi = (float)$suratBalasanPkl->snap_biaya_orientasi > 0;
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
             HALAMAN 1: SURAT BALASAN PKL
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
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $suratBalasanPkl->no }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0;">Lampiran</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0;">1 (satu) Berkas</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Perihal</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center; font-weight: bold;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Biaya dan Izin Praktik</td>
                    </tr>
                </table>

                {{-- Kepada Yth --}}
                <div style="margin-bottom: 16px; font-size: 11pt; line-height: 1.4;">
                    <div>Kepada Yth;</div>
                    @if($suratBalasanPkl->tujuan_nama)
                        <div style="font-weight: bold;">{{ $suratBalasanPkl->tujuan_nama }}</div>
                    @endif
                    <div style="font-weight: bold;">Universitas {{ $suratBalasanPkl->tujuan_universitas }}</div>
                    @if($suratBalasanPkl->tujuan_alamat)
                        <div>{{ $suratBalasanPkl->tujuan_alamat }}</div>
                    @endif
                    <div>Di</div>
                    <div style="padding-left: 20px;">Tempat</div>
                </div>

                {{-- Salam Pembuka --}}
                <div style="margin-bottom: 10px; font-style: italic; font-weight: bold; font-size: 11pt;">
                    Assalamu’alaikum Wr Wb
                </div>

                {{-- Paragraf 1 --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Menindaklanjuti surat Universitas {{ $suratBalasanPkl->tujuan_universitas }} dengan nomor surat : {{ $suratBalasanPkl->nomor_surat_masuk ?: '....................' }} tanggal {{ $tglMasukIndo }}, tentang Surat Izin Praktik dengan Jumlah Mahasiswi {{ $suratBalasanPkl->jumlah_mahasiswa }} orang. Pelaksanaan Praktik tersebut akan dilaksanakan pada tanggal {{ $tglMulaiIndo }} s.d {{ $tglSelesaiIndo }}.
                </p>

                {{-- Paragraf 2 --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Pada dasarnya pihak RS Bintang Amin Lampung, Bersedia memberikan izin Kunjungan Rumah Sakit kepada Mahasiswa Prodi {{ $suratBalasanPkl->prodi }} Universitas {{ $suratBalasanPkl->tujuan_universitas }} dengan ketentuan sebagai berikut :
                </p>

                {{-- Poin Ketentuan --}}
                <ol style="margin: 0 0 12px 0; padding-left: 30px; font-size: 11pt; line-height: 1.5; text-align: justify;">
                    <li style="margin-bottom: 4px;">
                        Biaya praktik Mahasiswa/i Rp. {{ number_format($suratBalasanPkl->snap_biaya_praktik, 0, ',', '.') }},-/Mahasiswa /Bulan (sesuai dengan Surat Keputusan Direktur Nomor {{ $suratBalasanPkl->snap_nomor_sk ?: '023/Kpts-S4/PBA-A10/10.01.22' }})
                    </li>
                    @if($hasOrientasi)
                        <li style="margin-bottom: 4px;">
                            Biaya Orientasi Rp {{ number_format($suratBalasanPkl->snap_biaya_orientasi, 0, ',', '.') }}./Mahasiswa/i
                        </li>
                    @endif
                    <li style="margin-bottom: 4px;">
                        Biaya dapat di transfer melalui rekening RSBA dengan Nomor Rekening 555 00 888 12 BNI atas nama RS Bintang Amin.
                    </li>
                    <li style="margin-bottom: 4px;">
                        Menyelesaikan biaya Administrasi PKL sebelum pelaksanaan praktik dimulai.
                    </li>
                    <li style="margin-bottom: 4px;">
                        Selama kunjungan mahasiswa Wajib menerapkan protokol kesehatan 3M yaitu Menjaga jarak, Memakai masker dan Mencuci tangan
                    </li>
                </ol>

                {{-- Paragraf Penutup --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Demikian kami sampaikan, Atas perhatian dan kerjasamanya kami ucapkan terimakasih.
                </p>

                <div style="font-style: italic; font-weight: bold; font-size: 11pt; margin-bottom: 20px;">
                    Wassalamu’alaikum Wr Wb.
                </div>
            </div>

            {{-- Kolom Tanda Tangan Direktur --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                <div style="text-align: center; min-width: 220px; font-size: 11pt;">
                    <div style="font-weight: bold;">RS. Bintang Amin</div>
                    <div style="margin-bottom: 75px;">Direktur</div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaDirektur }}</div>
                </div>
            </div>
        </div>

        {{-- Page Break untuk Lampiran --}}
        <div class="page-break" style="page-break-before: always; break-before: page; margin-top: 25px; border-top: 1px dashed #cbd5e1; padding-top: 15px;"></div>

        {{-- ============================================================
             HALAMAN 2: LAMPIRAN RINCIAN BIAYA PRAKTIK
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
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $suratBalasanPkl->no }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Perihal</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center; font-weight: bold;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Biaya dan Izin Praktik</td>
                    </tr>
                </table>

                {{-- Judul Tabel Lampiran --}}
                <div style="text-align: center; font-weight: bold; font-size: 12pt; text-transform: uppercase; margin-bottom: 18px;">
                    Rincian Biaya Praktik di Rumah Sakit Bintang Amin – Lampung
                </div>

                {{-- Tabel Lampiran Sesuai Format Word --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5pt; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                            <th style="border: 1px solid #000; padding: 8px 6px; width: 40px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; text-align: left;">Biaya Praktek Kerja Lapangan</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 120px; text-align: center;">Jumlah Siswa</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 120px; text-align: center;">Lama Praktik</th>
                            <th style="border: 1px solid #000; padding: 8px 10px; width: 160px; text-align: right;">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold; vertical-align: top;">1</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; vertical-align: top;">
                                <div style="font-weight: bold;">Izin Praktek</div>
                                <div style="font-size: 9.5pt; color: #475569;">Rp. {{ number_format($suratBalasanPkl->snap_biaya_praktik, 0, ',', '.') }},-/ orang/ bulan</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $suratBalasanPkl->lama_praktik_bulan }} Bulan</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 10.5pt; vertical-align: top;">
                                Rp. {{ number_format($suratBalasanPkl->total_biaya_praktik, 0, ',', '.') }},-
                            </td>
                        </tr>
                        @if($hasOrientasi)
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold; vertical-align: top;">2</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; vertical-align: top;">
                                    <div style="font-weight: bold;">Orientasi</div>
                                    <div style="font-size: 9.5pt; color: #475569;">Rp. {{ number_format($suratBalasanPkl->snap_biaya_orientasi, 0, ',', '.') }},-/ orang</div>
                                </td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">-</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 10.5pt; vertical-align: top;">
                                    Rp. {{ number_format($suratBalasanPkl->total_biaya_orientasi, 0, ',', '.') }},-
                                </td>
                            </tr>
                        @endif
                        <tr style="font-weight: bold; background-color: #f1f5f9;">
                            <td colspan="4" style="border: 1px solid #000; padding: 8px 10px; text-align: center; text-transform: uppercase;">Total</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 11pt;">
                                Rp. {{ number_format($suratBalasanPkl->grand_total_biaya, 0, ',', '.') }},-
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kolom Tanda Tangan Lampiran --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <div style="text-align: center; min-width: 220px; font-size: 11pt;">
                    <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
                    <div style="font-weight: bold;">RS. Bintang Amin</div>
                    <div style="margin-bottom: 75px;">Direktur</div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaDirektur }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
