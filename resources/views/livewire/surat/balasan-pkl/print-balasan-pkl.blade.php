<div id="print-balasan-pkl-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 170mm; margin: 0; padding: 0;">
    @if ($suratBalasanPkl)
        @php
            \Carbon\Carbon::setLocale('id');
            $tglSuratIndo = \Carbon\Carbon::parse($suratBalasanPkl->tgl)->locale('id')->translatedFormat('d F Y');
            $tglMasukIndo = $suratBalasanPkl->tgl_surat_masuk ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_surat_masuk)->locale('id')->translatedFormat('d F Y') : '....................';
            $tglMulaiIndo = $suratBalasanPkl->tgl_mulai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_mulai)->locale('id')->translatedFormat('d F Y') : '-';
            $tglSelesaiIndo = $suratBalasanPkl->tgl_selesai ? \Carbon\Carbon::parse($suratBalasanPkl->tgl_selesai)->locale('id')->translatedFormat('d F Y') : '-';
            
            $namaDirektur = ($suratBalasanPkl->direktur && !str_contains(strtolower($suratBalasanPkl->direktur->nama ?? ''), 'super admin'))
                ? ($suratBalasanPkl->direktur->full_nama ?: $suratBalasanPkl->direktur->nama)
                : 'dr. Rachmawati, MPH';

            $hasOrientasi = (float)$suratBalasanPkl->snap_biaya_orientasi > 0;
            $namaUniv = $suratBalasanPkl->display_universitas;
            $listMahasiswa = $suratBalasanPkl->mahasiswa ?? collect([]);
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
                    font-size: 11pt !important;
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
             HALAMAN 1: SURAT BALASAN PKL UTAMA
             ============================================================ --}}
        <div style="min-height: 260mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- KOP SURAT RESMI RS BINTANG AMIN (4cm total top margin) --}}
                <x-surat.kop-resmi />

                {{-- Tanggal Surat (Rata Kiri) --}}
                <div style="text-align: left; font-size: 11pt; line-height: 1.5; margin: 0;">
                    Bandar Lampung, {{ $tglSuratIndo }}
                </div>

                {{-- 1 ENTER --}}
                <div style="height: 14px;"></div>

                {{-- Metadata Surat (Nomor / Lampiran / Perihal) --}}
                <table style="width: 100%; font-size: 11pt; border-collapse: collapse; line-height: 1.25; margin: 0;">
                    <tr>
                        <td style="width: 75px; vertical-align: top; padding: 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; padding: 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 0;">{{ $suratBalasanPkl->no }}</td>
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
                    @if($suratBalasanPkl->tujuan_nama)
                        <div>{{ $suratBalasanPkl->tujuan_nama }}</div>
                    @endif
                    <div style="font-weight: bold;">{{ $namaUniv }}</div>
                    @if($suratBalasanPkl->tujuan_alamat)
                        <div>{{ $suratBalasanPkl->tujuan_alamat }}</div>
                    @endif
                    <div>Di</div>
                    <div style="padding-left: 28px;">Tempat</div>
                </div>

                {{-- 1 ENTER --}}
                <div style="height: 14px;"></div>

                {{-- Salam Pembuka (Regular, 1.5 line spacing) --}}
                <div style="font-size: 11pt; line-height: 1.5; margin: 0;">
                    Assalamu’alaikum Wr Wb
                </div>

                {{-- Paragraf 1 (Justified, Line 1.5, Indent 0cm / Special none) --}}
                <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                    Menindaklanjuti surat {{ $namaUniv }} dengan nomor surat : {{ $suratBalasanPkl->nomor_surat_masuk ?: '....................' }} tanggal {{ $tglMasukIndo }}, tentang Surat Izin Praktik dengan Jumlah Mahasiswa/i {{ $suratBalasanPkl->jumlah_mahasiswa }} orang. Pelaksanaan Praktik tersebut akan dilaksanakan pada tanggal <strong>{{ $tglMulaiIndo }} s.d {{ $tglSelesaiIndo }}.</strong>
                </div>

                {{-- Paragraf 2 (Justified, Line 1.5, Indent 0cm) --}}
                <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                    Pada dasarnya pihak RS Bintang Amin Lampung, <strong>Bersedia</strong> memberikan izin Kunjungan Rumah Sakit kepada Mahasiswa/i Prodi {{ $suratBalasanPkl->prodi }} {{ $namaUniv }} dengan ketentuan sebagai berikut :
                </div>

                {{-- 5 Poin Ketentuan (Justified, Line 1.5, Hanging indent) --}}
                @php $poinNo = 1; @endphp
                <table style="width: 100%; font-size: 11pt; border-collapse: collapse; line-height: 1.5; text-align: justify; margin: 0;">
                    <tr>
                        <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                        <td style="vertical-align: top; padding: 0; text-align: justify;">
                            Biaya praktik Mahasiswa/i Rp. {{ number_format($suratBalasanPkl->snap_biaya_praktik, 0, ',', '.') }},-/Mahasiswa/i /Bulan (sesuai dengan Surat Keputusan Direktur Nomor {{ $suratBalasanPkl->snap_nomor_sk ?: '023/Kpts-S4/PBA-A10/10.01.22' }})
                        </td>
                    </tr>
                    @if($hasOrientasi)
                        <tr>
                            <td style="width: 24px; vertical-align: top; padding: 0;">{{ $poinNo++ }}.</td>
                            <td style="vertical-align: top; padding: 0; text-align: justify;">
                                Biaya Orientasi Rp {{ number_format($suratBalasanPkl->snap_biaya_orientasi, 0, ',', '.') }}./Mahasiswa/i
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
                <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                    Demikian kami sampaikan, Atas perhatian dan kerjasamanya kami ucapkan terimakasih.
                </div>

                {{-- Salam Penutup (Regular, Line 1.5) --}}
                <div style="font-size: 11pt; line-height: 1.5; margin: 0;">
                    Wassalamu’alaikum Wr Wb.
                </div>

                {{-- 1 ENTER --}}
                <div style="height: 14px;"></div>

                {{-- Kolom Tanda Tangan Direktur (Rata Kiri) --}}
                <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
                    <div style="font-weight: bold;">RS. Bintang Amin</div>
                    <div>Direktur</div>
                    {{-- 3-4 ENTER spasi TTD --}}
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold;">{{ $namaDirektur }}</div>
                </div>
            </div>

            {{-- Running Footer Halaman 1 --}}
            <x-surat.footer-resmi />
        </div>

        {{-- Page Break untuk Lampiran --}}
        <div class="page-break" style="page-break-before: always; break-before: page; margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 15px;"></div>

        {{-- ============================================================
             HALAMAN 2: LAMPIRAN RINCIAN BIAYA PRAKTIK
             ============================================================ --}}
        <div style="min-height: 260mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- KOP SURAT RESMI RS BINTANG AMIN (HALAMAN 2) --}}
                <x-surat.kop-resmi :compact="true" />

                {{-- Header Lampiran --}}
                <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
                    Lampiran Surat
                </div>
                <table style="width: 100%; font-size: 11pt; border-collapse: collapse; line-height: 1.25; margin: 0;">
                    <tr>
                        <td style="width: 75px; vertical-align: top; padding: 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; padding: 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 0;">{{ $suratBalasanPkl->no }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 0; font-weight: bold;">Perihal</td>
                        <td style="vertical-align: top; padding: 0; text-align: center; font-weight: bold;">:</td>
                        <td style="vertical-align: top; padding: 0; font-weight: bold;">Biaya dan Izin Praktik</td>
                    </tr>
                </table>

                {{-- 1 ENTER --}}
                <div style="height: 14px;"></div>

                {{-- Judul Tabel Lampiran (Tengah, Bold, En-dash) --}}
                <div style="text-align: center; font-weight: bold; font-size: 11pt; margin: 0;">
                    Rincian Biaya Praktik di Rumah Sakit Bintang Amin – Lampung
                </div>

                {{-- Spasi sebelum tabel --}}
                <div style="height: 10px;"></div>

                {{-- Tabel Lampiran Sesuai Format Word & PDF --}}
                <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; font-size: 10pt; margin: 0;">
                    <thead>
                        <tr style="font-weight: bold; text-align: center;">
                            <th style="border: 1px solid #000; padding: 6px 4px; width: 35px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; text-align: center;">Biaya Praktek Kerja Lapangan</th>
                            <th style="border: 1px solid #000; padding: 6px 6px; width: 95px; text-align: center;">Jumlah Siswa</th>
                            <th style="border: 1px solid #000; padding: 6px 6px; width: 95px; text-align: center;">Lama Praktik</th>
                            <th style="border: 1px solid #000; padding: 6px 8px; width: 130px; text-align: center;">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: middle;">1</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: middle; text-align: left;">
                                <div>Izin Praktek</div>
                                <div>Rp. {{ number_format($suratBalasanPkl->snap_biaya_praktik, 0, ',', '.') }},-/ orang/ bulan</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 6px 6px; text-align: center; vertical-align: middle;">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                            <td style="border: 1px solid #000; padding: 6px 6px; text-align: center; vertical-align: middle;">{{ $suratBalasanPkl->lama_praktik_bulan }} Bulan</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle;">
                                Rp. {{ number_format($suratBalasanPkl->total_biaya_praktik, 0, ',', '.') }},-
                            </td>
                        </tr>
                        @if($hasOrientasi)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: middle;">2</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: middle; text-align: left;">
                                    <div>Orientasi Rp. {{ number_format($suratBalasanPkl->snap_biaya_orientasi, 0, ',', '.') }},-/ orang</div>
                                </td>
                                <td style="border: 1px solid #000; padding: 6px 6px; text-align: center; vertical-align: middle;">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                                <td style="border: 1px solid #000; padding: 6px 6px; text-align: center; vertical-align: middle;">-</td>
                                <td style="border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle;">
                                    Rp. {{ number_format($suratBalasanPkl->total_biaya_orientasi, 0, ',', '.') }},-
                                </td>
                            </tr>
                        @endif
                        <tr style="font-weight: bold;">
                            <td colspan="4" style="border: 1px solid #000; padding: 6px 8px; text-align: center;">Total</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: left;">
                                Rp. {{ number_format($suratBalasanPkl->grand_total_biaya, 0, ',', '.') }},-
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Tabel Daftar Mahasiswa jika ada --}}
                @if($listMahasiswa->count() > 0)
                    <div style="margin-top: 14px; margin-bottom: 6px; font-weight: bold; font-size: 10pt;">
                        Daftar Mahasiswa/i ({{ $listMahasiswa->count() }} Orang):
                    </div>
                    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; font-size: 10pt; margin: 0;">
                        <thead>
                            <tr style="font-weight: bold; text-align: center;">
                                <th style="border: 1px solid #000; padding: 5px 4px; width: 35px; text-align: center;">No</th>
                                <th style="border: 1px solid #000; padding: 5px 8px; text-align: left;">Nama Mahasiswa/i</th>
                                <th style="border: 1px solid #000; padding: 5px 8px; width: 160px; text-align: center;">NPM / NIM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listMahasiswa as $mIdx => $mhs)
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 4px; text-align: center;">{{ $mIdx + 1 }}</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px;">{{ $mhs->nama }}</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">{{ $mhs->npm ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- 2 ENTER setelah tabel --}}
                <div style="height: 18px;"></div>

                {{-- Kolom Tanda Tangan Lampiran --}}
                <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin: 0;">
                    <div style="margin-bottom: 2px;">Bandar Lampung, {{ $tglSuratIndo }}</div>
                    <div style="font-weight: bold;">RS. Bintang Amin</div>
                    <div>Direktur</div>
                    {{-- 4 ENTER spasi TTD --}}
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold;">{{ $namaDirektur }}</div>
                </div>
            </div>

            {{-- Running Footer Halaman 2 --}}
            <x-surat.footer-resmi />
        </div>
    @endif
</div>
