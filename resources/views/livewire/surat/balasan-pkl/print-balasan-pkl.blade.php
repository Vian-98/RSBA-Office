<div id="print-balasan-pkl-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 0 10px;">
    @php
        $isDocstore = ($fromDocstore ?? false) && !empty($docstoreData);
        $doc = ($docstoreData ?? [])['document'] ?? [];
        $content = $isDocstore ? ($doc['content'] ?? []) : [];

        $tglSuratIndo = !empty($content['tgl']) ? \Carbon\Carbon::parse($content['tgl'])->translatedFormat('d F Y') : ($suratBalasanPkl->tgl ? \Carbon\Carbon::parse($suratBalasanPkl->tgl)->translatedFormat('d F Y') : '-');
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
        $univ = $content['tujuan_universitas'] ?? $suratBalasanPkl->display_universitas;
        $prodi = $content['prodi'] ?? $suratBalasanPkl->prodi;
        $tujuanNama = $content['tujuan_nama'] ?? $suratBalasanPkl->tujuan_nama;
        $tujuanAlamat = $content['tujuan_alamat'] ?? $suratBalasanPkl->tujuan_alamat;
        $noSuratMasuk = $content['nomor_surat_masuk'] ?? $suratBalasanPkl->nomor_surat_masuk;
        $snapSk = $content['snap_nomor_sk'] ?? $suratBalasanPkl->snap_nomor_sk;
        $listMahasiswa = $suratBalasanPkl->mahasiswa ?? collect([]);
        $qrCode = $qrCode ?? null;
    @endphp

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 10mm 15mm 30mm;
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
            :version="($docstoreData['meta']['version'] ?? ($docstoreData['document']['version'] ?? 1))"
            :docstore-key="$suratBalasanPkl->docstore_key"
        />
    @elseif(!empty($suratBalasanPkl->docstore_key))
        <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 10px; font-weight: bold;" class="no-print">
            🔒 TERDAFTAR DI DOCSTORE VAULT — Key: {{ $suratBalasanPkl->docstore_key }}
        </div>
    @endif

    {{-- ============================================================
         HALAMAN 1: SURAT BALASAN PKL
         ============================================================ --}}
    <div style="min-height: 255mm; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            {{-- Kop Surat RSBA Logo Resmi (5.54cm x 2.75cm) --}}
            <x-surat.kop-resmi />

            {{-- Tanggal Surat (Rata Kiri) --}}
            <div style="text-align: left; font-size: 11pt; line-height: 1.5; margin: 0;">
                Bandar Lampung, {{ $tglSuratIndo }}
            </div>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            {{-- Metadata Surat --}}
            <table style="width: 100%; font-size: 11pt; margin: 0; border-collapse: collapse; line-height: 1.25;">
                <tr>
                    <td style="width: 75px; vertical-align: top; padding: 0;">Nomor</td>
                    <td style="width: 15px; vertical-align: top; padding: 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 0;">{{ $nomorSurat }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0;">Lampiran</td>
                    <td style="vertical-align: top; padding: 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 0;">1 (satu) Berkas</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0; font-weight: bold;">Perihal</td>
                    <td style="vertical-align: top; padding: 0; text-align: center; font-weight: bold;">:</td>
                    <td style="vertical-align: top; padding: 0; font-weight: bold;">Biaya dan Izin Praktik</td>
                </tr>
            </table>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            {{-- Blok Kepada Yth --}}
            <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
                <div>KepadaYth;</div>
                @if($tujuanNama)
                    <div>{{ $tujuanNama }}</div>
                @endif
                <div style="font-weight: bold;">{{ $univ }}</div>
                @if($tujuanAlamat)
                    <div>{{ $tujuanAlamat }}</div>
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

            {{-- Paragraf 1 (Justified, Line 1.5, Indent 0) --}}
            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                Menindaklanjuti surat {{ $univ }} dengan nomor surat : {{ $noSuratMasuk ?: '....................' }} tanggal {{ $tglMasukIndo }}, tentang Surat Izin Praktik dengan Jumlah Mahasiswa/i {{ $jumlahMhs }} orang. Pelaksanaan Praktik tersebut akan dilaksanakan pada tanggal <strong>{{ $tglMulaiIndo }} s.d {{ $tglSelesaiIndo }}.</strong>
            </div>

            {{-- Paragraf 2 (Justified, Line 1.5, Indent 0) --}}
            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                Pada dasarnya pihak RS Bintang Amin Lampung, <strong>Bersedia</strong> memberikan izin Kunjungan Rumah Sakit kepada Mahasiswa/i Prodi {{ $prodi }} {{ $univ }} dengan ketentuan sebagai berikut :
            </div>

            {{-- 5 Poin Ketentuan --}}
            @php $pNo = 1; @endphp
            <table style="width: 100%; font-size: 11pt; line-height: 1.5; text-align: justify; margin: 0; border-collapse: collapse;">
                <tr>
                    <td style="width: 24px; vertical-align: top; padding: 0;">{{ $pNo++ }}.</td>
                    <td style="vertical-align: top; padding: 0; text-align: justify;">
                        Biaya praktik Mahasiswa/i Rp. {{ number_format($snapBiayaPraktik, 0, ',', '.') }},-/Mahasiswa/i /Bulan (sesuai dengan Surat Keputusan Direktur Nomor {{ $snapSk ?: '023/Kpts-S4/PBA-A10/10.01.22' }})
                    </td>
                </tr>
                @if($hasOrientasi)
                    <tr>
                        <td style="width: 24px; vertical-align: top; padding: 0;">{{ $pNo++ }}.</td>
                        <td style="vertical-align: top; padding: 0; text-align: justify;">
                            Biaya Orientasi Rp {{ number_format($snapBiayaOrientasi, 0, ',', '.') }}./Mahasiswa/i
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="width: 24px; vertical-align: top; padding: 0;">{{ $pNo++ }}.</td>
                    <td style="vertical-align: top; padding: 0; text-align: justify;">
                        Biaya dapat di transfer melalui rekening RSBA dengan Nomor Rekening <strong><em>555 00 888 12</em></strong> BNI atas nama <strong>RS Bintang Amin</strong>.
                    </td>
                </tr>
                <tr>
                    <td style="width: 24px; vertical-align: top; padding: 0;">{{ $pNo++ }}.</td>
                    <td style="vertical-align: top; padding: 0; text-align: justify;">
                        Menyelesaikan biaya Administrasi PKL sebelum pelaksanaan praktik dimulai.
                    </td>
                </tr>
                <tr>
                    <td style="width: 24px; vertical-align: top; padding: 0;">{{ $pNo++ }}.</td>
                    <td style="vertical-align: top; padding: 0; text-align: justify;">
                        Selama kunjungan  mahasiswa/i Wajib menerapkan protokol kesehatan 3M yaitu Menjaga jarak, Memakai masker dan Mencuci tangan
                    </td>
                </tr>
            </table>

            {{-- Paragraf Penutup --}}
            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                Demikian kami sampaikan, Atas perhatian dan kerjasamanya kami ucapkan terimakasih.
            </div>

            {{-- Salam Penutup --}}
            <div style="font-size: 11pt; line-height: 1.5; margin: 0;">
                Wassalamu’alaikum Wr Wb.
            </div>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            @php
                $statusSurat = $suratBalasanPkl->status?->value ?? (string)$suratBalasanPkl->status;
                $isApproved = ($statusSurat === 'approved');
            @endphp

            {{-- Kolom TTD Direktur --}}
            <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
                <div style="font-weight: bold;">RS. Bintang Amin</div>
                <div>Direktur</div>
                @if($qrCode)
                    <div style="padding: 4px 0;">
                        <img src="data:image/png;base64,{{ $qrCode }}" style="width: 58px; height: 58px; display: block;" alt="QR Code Verifikasi">
                        @if(!$isApproved)
                            <div style="font-size: 7.5pt; font-weight: bold; color: #b45309; padding-top: 2px; letter-spacing: 0.02em;">[ DRAF / MENUNGGU PERSETUJUAN ]</div>
                        @endif
                    </div>
                @else
                    <div style="height: 55px;"></div>
                @endif
                <div style="font-weight: bold;">{{ $namaDirektur }}</div>
                @if(!$isApproved)
                    <div style="font-size: 8pt; color: #64748b; font-style: italic;">(Menunggu Otorisasi)</div>
                @endif
            </div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <x-surat.footer-resmi />
    </div>

    {{-- Page Break untuk Lampiran --}}
    <div class="page-break" style="page-break-before: always; break-before: page;"></div>

    {{-- ============================================================
         HALAMAN 2: LAMPIRAN RINCIAN BIAYA PRAKTIK
         ============================================================ --}}
    <div style="min-height: 255mm; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            {{-- Kop Surat RSBA Logo Resmi --}}
            <x-surat.kop-resmi />

            {{-- Header Lampiran --}}
            <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
                Lampiran Surat
            </div>
            <table style="width: 100%; font-size: 11pt; margin: 0; border-collapse: collapse; line-height: 1.25;">
                <tr>
                    <td style="width: 75px; vertical-align: top; padding: 0;">Nomor</td>
                    <td style="width: 15px; vertical-align: top; padding: 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 0;">{{ $nomorSurat }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0; font-weight: bold;">Perihal</td>
                    <td style="vertical-align: top; padding: 0; text-align: center; font-weight: bold;">:</td>
                    <td style="vertical-align: top; padding: 0; font-weight: bold;">Biaya dan Izin Praktik</td>
                </tr>
            </table>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            {{-- Judul Tabel (Tengah, Bold, En-dash) --}}
            <div style="text-align: center; font-weight: bold; font-size: 11pt; margin: 0;">
                Rincian Biaya Praktik di Rumah Sakit Bintang Amin – Lampung
            </div>

            <div style="height: 10px;"></div>

            {{-- Tabel Lampiran --}}
            <table style="width: 100%; border: 1.5px solid #000; border-collapse: collapse; font-size: 10pt; margin: 0;">
                <thead>
                    <tr style="background-color: #ffffff;">
                        <th style="border: 1px solid #000; padding: 6px 4px; width: 35px; text-align: center; font-weight: bold;">No</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-weight: bold;">Biaya Praktek Kerja Lapangan</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 95px; text-align: center; font-weight: bold;">Jumlah Siswa</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 95px; text-align: center; font-weight: bold;">Lama Praktik</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 130px; text-align: center; font-weight: bold;">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px 4px; text-align: center;">1</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                            <div>Izin Praktek</div>
                            <div>Rp. {{ number_format($snapBiayaPraktik, 0, ',', '.') }},-/ orang/ bulan</div>
                        </td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;">{{ $jumlahMhs }} Orang</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;">{{ $lamaBulan }} Bulan</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                            Rp. {{ number_format($totPraktik, 0, ',', '.') }},-
                        </td>
                    </tr>
                    @if($hasOrientasi)
                        <tr>
                            <td style="border: 1px solid #000; padding: 6px 4px; text-align: center;">2</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                                <div>Orientasi Rp. {{ number_format($snapBiayaOrientasi, 0, ',', '.') }},-/ orang</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;">{{ $jumlahMhs }} Orang</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;">-</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                                Rp. {{ number_format($totOrientasi, 0, ',', '.') }},-
                            </td>
                        </tr>
                    @endif
                    <tr style="font-weight: bold;">
                        <td colspan="4" style="border: 1px solid #000; padding: 6px 8px; text-align: center;">Total</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                            Rp. {{ number_format($grandTotal, 0, ',', '.') }},-
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Tabel Daftar Mahasiswa jika ada --}}
            @if($listMahasiswa->count() > 0)
                <div style="margin-top: 14px; margin-bottom: 6px; font-weight: bold; font-size: 10pt;">
                    Daftar Mahasiswa/i ({{ $listMahasiswa->count() }} Orang):
                </div>
                <table style="width: 100%; border: 1.5px solid #000; border-collapse: collapse; font-size: 10pt; margin: 0;">
                    <thead>
                        <tr style="background-color: #ffffff;">
                            <th style="border: 1px solid #000; padding: 6px 4px; width: 35px; text-align: center; font-weight: bold;">No</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; text-align: left; font-weight: bold;">Nama Mahasiswa/i</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; width: 160px; text-align: center; font-weight: bold;">NPM / NIM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listMahasiswa as $mIdx => $mhs)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px 4px; text-align: center;">{{ $mIdx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">{{ $mhs->nama }}</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;">{{ $mhs->npm ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="height: 18px;"></div>

            {{-- Kolom TTD Direktur Lampiran --}}
            <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
                <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
                <div style="font-weight: bold;">RS. Bintang Amin</div>
                <div>Direktur</div>
                @if($qrCode)
                    <div style="padding: 4px 0;">
                        <img src="data:image/png;base64,{{ $qrCode }}" style="width: 58px; height: 58px; display: block;" alt="QR Code Verifikasi">
                    </div>
                @else
                    <div style="height: 55px;"></div>
                @endif
                <div style="font-weight: bold;">{{ $namaDirektur }}</div>
            </div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <x-surat.footer-resmi />
    </div>
</div>
