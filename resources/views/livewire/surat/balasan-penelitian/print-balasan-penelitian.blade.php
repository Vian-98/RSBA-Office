<div id="print-balasan-penelitian-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @php
        $isDocstore = $this->fromDocstore && !empty($this->docstoreData);
        $doc = $this->docstoreData['document'] ?? [];
        $content = $isDocstore ? ($doc['content'] ?? []) : [];

        $tglSuratIndo = !empty($content['tgl']) ? \Carbon\Carbon::parse($content['tgl'])->translatedFormat('d F Y') : ($suratBalasanPenelitian->tgl ? \Carbon\Carbon::parse($suratBalasanPenelitian->tgl)->translatedFormat('d F Y') : '-');
        $tglMasukIndo = !empty($content['tgl_surat_masuk']) ? \Carbon\Carbon::parse($content['tgl_surat_masuk'])->translatedFormat('d F Y') : ($suratBalasanPenelitian->tgl_surat_masuk ? \Carbon\Carbon::parse($suratBalasanPenelitian->tgl_surat_masuk)->translatedFormat('d F Y') : '....................');
        $namaDirektur = $content['nama_direktur'] ?? (optional($suratBalasanPenelitian->direktur)->full_nama ?? 'dr. Rachmawati, MPH');
        $nipDirektur  = $content['nip_direktur'] ?? (optional($suratBalasanPenelitian->direktur)->nip ?? '24170002');
        $nomorSurat   = $content['no'] ?? $suratBalasanPenelitian->no;
        $univ         = $content['tujuan_universitas'] ?? $suratBalasanPenelitian->tujuan_universitas;
        $fakultas     = $content['tujuan_fakultas'] ?? $suratBalasanPenelitian->tujuan_fakultas;
        $tujuanNama   = $content['tujuan_nama'] ?? $suratBalasanPenelitian->tujuan_nama;
        $tujuanAlamat = $content['tujuan_alamat'] ?? $suratBalasanPenelitian->tujuan_alamat;
        $noSuratMasuk = $content['nomor_surat_masuk'] ?? $suratBalasanPenelitian->nomor_surat_masuk;
        $perihalSurat = $content['perihal_surat_masuk'] ?? $suratBalasanPenelitian->perihal_surat_masuk;
        $mahasiswaList= $content['mahasiswa'] ?? $suratBalasanPenelitian->mahasiswa->toArray();
        $biayaList    = $content['biaya'] ?? $suratBalasanPenelitian->biaya->toArray();
        $totalBiaya   = (float) ($content['total_biaya'] ?? $suratBalasanPenelitian->total_biaya);
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

    @if ($isDocstore)
        {{-- Docstore Verified Badge Component --}}
        <x-surat.docstore-badge
            :version="$this->docstoreData['meta']['version'] ?? ($this->docstoreData['document']['version'] ?? 1)"
            :docstore-key="$suratBalasanPenelitian->docstore_key"
        />
    @elseif(!empty($suratBalasanPenelitian->docstore_key))
        <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 10px; font-weight: bold;" class="no-print">
            🔒 TERDAFTAR DI DOCSTORE VAULT — Key: {{ $suratBalasanPenelitian->docstore_key }}
        </div>
    @endif

    {{-- ============================================================
         HALAMAN 1: SURAT BALASAN PENELITIAN
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
                    <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $perihalSurat }}</td>
                </tr>
            </table>

            {{-- Kepada Yth --}}
            <div style="margin-bottom: 16px; font-size: 11pt; line-height: 1.4;">
                <div>Kepada Yth;</div>
                @if($tujuanNama)
                    <div style="font-weight: bold;">{{ $tujuanNama }}</div>
                @endif
                <div style="font-weight: bold;">Fakultas {{ $fakultas }} – Universitas {{ $univ }}</div>
                <div>Di &nbsp; tempat</div>
            </div>

            {{-- Salam Pembuka --}}
            <div style="margin-bottom: 10px; font-weight: bold; font-size: 11pt;">
                Dengan hormat,
            </div>

            {{-- Paragraf 1 --}}
            <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                Menindaklanjuti surat dari Fakultas {{ $fakultas }} - Universitas {{ $univ }}, Nomor: {{ $noSuratMasuk ?: '....................' }} tentang {{ $perihalSurat }} di RS. Bintang Amin Lampung, berdasarkan surat tersebut maka kami :
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
                Menyatakan bahwa kami <strong>bersedia</strong> menerima Mahasiswa/i Fakultas {{ $fakultas }} Universitas {{ $univ }} untuk Penelitian di RS. Bintang Amin.
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
                    @foreach($mahasiswaList as $mIdx => $mhs)
                        <tr>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $mIdx + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; font-weight: bold; vertical-align: top;">{{ $mhs['nama'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-family: monospace; vertical-align: top;">{{ $mhs['npm'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">{{ $mhs['fakultas_pt'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">{{ $mhs['judul_penelitian'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Penutup --}}
            <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                Demikian surat pemberitahuan ini, atas kerjasamanya diucapkan terima kasih.
            </p>
        </div>

        {{-- Kolom TTD Direktur + QR Code Verifikasi Component --}}
        <x-surat.signature-block
            title="Hormat Kami"
            role="Direktur,"
            :name="$namaDirektur"
            :qr-code="$this->generateHeaderQrCode"
        />
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
                    <td style="vertical-align: top; padding: 2px 0; font-weight: 500;">{{ $nomorSurat }}</td>
                </tr>
            </table>

            {{-- Tabel Lampiran Biaya Sesuai Format Word --}}
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5pt; margin-bottom: 16px;">
                <thead>
                    <tr style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                        <th style="border: 1px solid #000; padding: 8px 6px; width: 40px; text-align: center;">NO</th>
                        <th style="border: 1px solid #000; padding: 8px 10px; text-align: left;">Biaya Penelitian & Pendidikan</th>
                        <th style="border: 1px solid #000; padding: 8px 10px; width: 140px; text-right: right; text-align: right;">Jasa Sarana</th>
                        <th style="border: 1px solid #000; padding: 8px 10px; width: 140px; text-right: right; text-align: right;">Jasa Pelayanan</th>
                        <th style="border: 1px solid #000; padding: 8px 10px; width: 160px; text-align: right;">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($biayaList as $bIdx => $item)
                        @php
                            $sarana = (float) ($item['jasa_sarana'] ?? 0);
                            $pelayanan = (float) ($item['jasa_pelayanan'] ?? 0);
                            $jmlOrg = (int) ($item['jumlah_orang'] ?? 1);
                            $subtotal = $item['subtotal'] ?? ($item['total'] ?? (($sarana + $pelayanan) * ($jmlOrg ?: 1)));
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $bIdx + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 8px 10px; vertical-align: top;">
                                <div style="font-weight: bold;">{{ $item['keterangan'] ?? '-' }}</div>
                                <div style="font-size: 9.5pt; color: #475569;">{{ $jmlOrg }} org x Rp. {{ number_format($sarana + $pelayanan, 0, ',', '.') }}</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; vertical-align: top;">
                                Rp. {{ number_format($sarana, 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; vertical-align: top;">
                                Rp. {{ number_format($pelayanan, 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-weight: bold; vertical-align: top;">
                                Rp. {{ number_format($subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background-color: #f1f5f9;">
                        <td colspan="4" style="border: 1px solid #000; padding: 8px 10px; text-align: center; text-transform: uppercase;">Total</td>
                        <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-family: monospace; font-size: 11pt;">
                            Rp. {{ number_format($totalBiaya, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Catatan Administrasi Sesuai Template Word --}}
            <div style="font-style: italic; font-size: 11pt; margin-bottom: 30px; line-height: 1.4;">
                Menyelesaikan biaya Administrasi di kasir sebelum pelaksanaan penelitian dimulai.
            </div>
        </div>

        {{-- Kolom Tanda Tangan Lampiran + QR Code Component --}}
        <x-surat.signature-block
            role="Direktur,"
            :name="$namaDirektur"
            city="Bandar Lampung"
            :date="$tglSuratIndo"
            :qr-code="$this->generateHeaderQrCode"
        />
    </div>
</div>
