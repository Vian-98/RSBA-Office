<div id="print-balasan-pkl-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @if (!$this->canPrint)
        {{-- Docstore Error Alert Component --}}
        <x-surat.docstore-error-alert
            :error="$this->docstoreError"
            :docstore-key="$suratBalasanPkl->docstore_key ?? null"
        />
    @else
        {{-- Docstore Success State --}}
        @php
            $doc = $this->docstoreData['document'] ?? [];
            $content = $doc['content'] ?? [];

            $tglSuratIndo = !empty($content['tgl']) ? \Carbon\Carbon::parse($content['tgl'])->translatedFormat('d F Y') : \Carbon\Carbon::parse($suratBalasanPkl->tgl)->translatedFormat('d F Y');
            $tglMasukIndo = !empty($content['tgl_surat_masuk']) ? \Carbon\Carbon::parse($content['tgl_surat_masuk'])->translatedFormat('d F Y') : ($suratBalasanPkl->tgl_surat_masuk ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_surat_masuk)->translatedFormat('d F Y') : '....................');
            $tglMulaiIndo = !empty($content['tgl_mulai']) ? \Carbon\Carbon::parse($content['tgl_mulai'])->translatedFormat('d F Y') : ($suratBalasanPkl->tgl_mulai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_mulai)->translatedFormat('d F Y') : '-');
            $tglSelesaiIndo = !empty($content['tgl_selesai']) ? \Carbon\Carbon::parse($content['tgl_selesai'])->translatedFormat('d F Y') : ($suratBalasanPkl->tgl_selesai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_selesai)->translatedFormat('d F Y') : '-');
            $namaDirektur = $content['nama_direktur'] ?? (optional($suratBalasanPkl->direktur)->full_nama ?? 'dr. Rachmawati, MPH');
            $nipDirektur  = $content['nip_direktur'] ?? (optional($suratBalasanPkl->direktur)->nip ?? '24170002');
            $snapBiayaPraktik = (float) ($content['snap_biaya_praktik'] ?? $suratBalasanPkl->snap_biaya_praktik);
            $snapBiayaOrientasi = (float) ($content['snap_biaya_orientasi'] ?? $suratBalasanPkl->snap_biaya_orientasi);
            $hasOrientasi = $snapBiayaOrientasi > 0;
            $jumlahMhs = (int) ($content['jumlah_mahasiswa'] ?? $suratBalasanPkl->jumlah_mahasiswa);
            $lamaBulan = (int) ($content['lama_praktik_bulan'] ?? $suratBalasanPkl->lama_praktik_bulan);
            $totPraktik = (float) ($content['total_biaya_praktik'] ?? $suratBalasanPkl->total_biaya_praktik);
            $totOrientasi = (float) ($content['total_biaya_orientasi'] ?? $suratBalasanPkl->total_biaya_orientasi);
            $grandTotal = (float) ($content['grand_total_biaya'] ?? $suratBalasanPkl->grand_total_biaya);
            $nomorSurat = $content['no'] ?? $suratBalasanPkl->no;
            $univ = $content['tujuan_universitas'] ?? $suratBalasanPkl->tujuan_universitas;
            $prodi = $content['prodi'] ?? $suratBalasanPkl->prodi;
            $tujuanNama = $content['tujuan_nama'] ?? $suratBalasanPkl->tujuan_nama;
            $tujuanAlamat = $content['tujuan_alamat'] ?? $suratBalasanPkl->tujuan_alamat;
            $noSuratMasuk = $content['nomor_surat_masuk'] ?? $suratBalasanPkl->nomor_surat_masuk;
            $snapSk = $content['snap_nomor_sk'] ?? $suratBalasanPkl->snap_nomor_sk;
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

        {{-- Docstore Verified Badge Component --}}
        <x-surat.docstore-badge
            :version="$this->docstoreData['meta']['version'] ?? ($this->docstoreData['document']['version'] ?? 1)"
            :docstore-key="$suratBalasanPkl->docstore_key"
        />

        {{-- ============================================================
             HALAMAN 1: SURAT BALASAN PKL
             ============================================================ --}}
        <div style="min-height: 250mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- Kop Surat RSBA Component --}}
                <x-surat.kop-surat />

                {{-- Tanggal Surat --}}
                <div style="text-align: right; margin-bottom: 14px; font-size: 11pt;">
                    Bandar Lampung, {{ $tglSuratIndo }}
                </div>

                {{-- Metadata Surat --}}
                <table style="width: 100%; font-size: 11pt; margin-bottom: 16px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 90px; vertical-align: top; padding: 2px 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $nomorSurat }}</td>
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
                    @if($tujuanNama)
                        <div style="font-weight: bold;">{{ $tujuanNama }}</div>
                    @endif
                    <div style="font-weight: bold;">Universitas {{ $univ }}</div>
                    @if($tujuanAlamat)
                        <div>{{ $tujuanAlamat }}</div>
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
                    Menindaklanjuti surat Universitas {{ $univ }} dengan nomor surat : {{ $noSuratMasuk ?: '....................' }} tanggal {{ $tglMasukIndo }}, tentang Surat Izin Praktik dengan Jumlah Mahasiswi {{ $jumlahMhs }} orang. Pelaksanaan Praktik tersebut akan dilaksanakan pada tanggal {{ $tglMulaiIndo }} s.d {{ $tglSelesaiIndo }}.
                </p>

                {{-- Paragraf 2 --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    Pada dasarnya pihak RS Bintang Amin Lampung, Bersedia memberikan izin Kunjungan Rumah Sakit kepada Mahasiswa Prodi {{ $prodi }} Universitas {{ $univ }} dengan ketentuan sebagai berikut :
                </p>

                {{-- Poin Ketentuan --}}
                <ol style="margin: 0 0 12px 0; padding-left: 30px; font-size: 11pt; line-height: 1.5; text-align: justify;">
                    <li style="margin-bottom: 4px;">
                        Biaya praktik Mahasiswa/i Rp. {{ number_format($snapBiayaPraktik, 0, ',', '.') }},-/Mahasiswa /Bulan (sesuai dengan Surat Keputusan Direktur Nomor {{ $snapSk ?: '023/Kpts-S4/PBA-A10/10.01.22' }})
                    </li>
                    @if($hasOrientasi)
                        <li style="margin-bottom: 4px;">
                            Biaya Orientasi Rp {{ number_format($snapBiayaOrientasi, 0, ',', '.') }}./Mahasiswa/i
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

            {{-- Kolom Tanda Tangan Direktur + QR Code Verifikasi Component --}}
            <x-surat.signature-block
                title="RS. Bintang Amin"
                role="Direktur"
                :name="$namaDirektur"
                :qr-code="$this->generateHeaderQrCode"
            />
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
                        <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $nomorSurat }}</td>
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
                                <div style="font-size: 9.5pt; color: #475569;">Rp. {{ number_format($snapBiayaPraktik, 0, ',', '.') }},-/ orang/ bulan</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $jumlahMhs }} Orang</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $lamaBulan }} Bulan</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 10.5pt; vertical-align: top;">
                                Rp. {{ number_format($totPraktik, 0, ',', '.') }},-
                            </td>
                        </tr>
                        @if($hasOrientasi)
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold; vertical-align: top;">2</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; vertical-align: top;">
                                    <div style="font-weight: bold;">Orientasi</div>
                                    <div style="font-size: 9.5pt; color: #475569;">Rp. {{ number_format($snapBiayaOrientasi, 0, ',', '.') }},-/ orang</div>
                                </td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">{{ $jumlahMhs }} Orang</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; vertical-align: top;">-</td>
                                <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 10.5pt; vertical-align: top;">
                                    Rp. {{ number_format($totOrientasi, 0, ',', '.') }},-
                                </td>
                            </tr>
                        @endif
                        <tr style="font-weight: bold; background-color: #f1f5f9;">
                            <td colspan="4" style="border: 1px solid #000; padding: 8px 10px; text-align: center; text-transform: uppercase;">Total</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 11pt;">
                                Rp. {{ number_format($grandTotal, 0, ',', '.') }},-
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kolom Tanda Tangan Lampiran + QR Code Component --}}
            <x-surat.signature-block
                title="RS. Bintang Amin"
                role="Direktur"
                :name="$namaDirektur"
                city="Bandar Lampung"
                :date="$tglSuratIndo"
                :qr-code="$this->generateHeaderQrCode"
            />
        </div>
    @endif
</div>
