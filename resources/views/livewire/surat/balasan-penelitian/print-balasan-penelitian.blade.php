<div id="print-balasan-penelitian-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 0 10px;">
    @php
        $isDocstore = ($fromDocstore ?? false) && !empty($docstoreData);
        $doc = ($docstoreData ?? [])['document'] ?? [];
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
        $mahasiswaList= $content['mahasiswa'] ?? ($suratBalasanPenelitian->mahasiswa ? $suratBalasanPenelitian->mahasiswa->toArray() : []);
        $biayaList    = $content['biaya'] ?? ($suratBalasanPenelitian->biaya ? $suratBalasanPenelitian->biaya->toArray() : []);
        $totalBiaya   = (float) ($content['total_biaya'] ?? $suratBalasanPenelitian->total_biaya);
        $qrCode       = $qrCode ?? null;
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
            :docstore-key="$suratBalasanPenelitian->docstore_key"
        />
    @elseif(!empty($suratBalasanPenelitian->docstore_key))
        <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 10px; font-weight: bold;" class="no-print">
            🔒 TERDAFTAR DI DOCSTORE VAULT — Key: {{ $suratBalasanPenelitian->docstore_key }}
        </div>
    @endif

    {{-- ============================================================
         HALAMAN 1: SURAT BALASAN PENELITIAN UTAMA
         ============================================================ --}}
    <div style="min-height: 255mm; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            {{-- Kop Surat RSBA Logo Resmi (5.54cm x 2.75cm) --}}
            <x-surat.kop-resmi />

            {{-- Tanggal Surat --}}
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
                    <td style="vertical-align: top; padding: 0; font-weight: bold;">{{ $perihalSurat ?: 'Izin Penelitian dan Pengambilan Data' }}</td>
                </tr>
            </table>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            {{-- Kepada Yth --}}
            <div style="font-size: 11pt; line-height: 1.25; margin: 0;">
                <div>Kepada Yth;</div>
                @if($tujuanNama)
                    <div>{{ $tujuanNama }}</div>
                @endif
                <div style="font-weight: bold;">Fakultas {{ $fakultas }} – Universitas {{ $univ }}</div>
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
                Dengan hormat,
            </div>

            {{-- Paragraf 1 --}}
            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                Menindaklanjuti surat dari Fakultas {{ $fakultas }} - Universitas {{ $univ }}, Nomor: {{ $noSuratMasuk ?: '....................' }} tentang {{ $perihalSurat ?: 'Izin Penelitian' }} di RS. Bintang Amin Lampung, berdasarkan surat tersebut maka kami :
            </div>

            {{-- Identitas RS --}}
            <table style="width: 100%; font-size: 11pt; margin: 4px 0; border-collapse: collapse; line-height: 1.35;">
                <tr>
                    <td style="width: 200px; vertical-align: top; padding: 1px 0;">Nama Perusahaan/Instansi</td>
                    <td style="width: 15px; vertical-align: top; padding: 1px 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 1px 0; font-weight: bold;">RS. Bintang Amin Lampung</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 1px 0;">Alamat</td>
                    <td style="vertical-align: top; padding: 1px 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 1px 0;">Jl. Pramuka No. 27, Kemiling – Bandar Lampung</td>
                </tr>
            </table>

            {{-- Paragraf Pernyataan Bersedia --}}
            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 0; text-indent: 0;">
                Menyatakan bahwa kami <strong>bersedia</strong> menerima Mahasiswa/i Fakultas {{ $fakultas }} Universitas {{ $univ }} untuk Penelitian di RS. Bintang Amin.
            </div>

            <div style="text-align: justify; font-size: 11pt; line-height: 1.5; margin: 4px 0 6px 0;">
                Adapun identitas mahasiswa tersebut adalah sebagai berikut :
            </div>

            {{-- Tabel Identitas Mahasiswa --}}
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; font-size: 10pt; margin-bottom: 8px;">
                <thead>
                    <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">
                        <th style="border: 1px solid #000; padding: 6px 4px; width: 30px; text-align: center;">No.</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">NAMA</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 90px; text-align: center;">NPM</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 140px; text-align: left;">FAKULTAS /<br>PERGURUAN TINGGI</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">JUDUL/TOPIK<br>PENELITIAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaList as $mIdx => $mhs)
                        <tr>
                            <td style="border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: top;">{{ $mIdx + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; font-weight: bold; vertical-align: top;">{{ $mhs['nama'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; vertical-align: top;">{{ $mhs['npm'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; vertical-align: top;">{{ $mhs['fakultas_pt'] ?? "Fakultas {$fakultas} / {$univ}" }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; vertical-align: top;">{{ $mhs['judul_penelitian'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="border: 1px solid #000; padding: 8px; text-align: center; color: #666;">Belum ada data mahasiswa</td>
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

                @if ($qrCode)
                    <div style="padding: 4px 0;">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi Bank Surat" style="height: 58px; width: 58px; display: block;">
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

    {{-- Page Break untuk Lampiran --}}
    <div class="page-break" style="page-break-before: always; break-before: page;"></div>

    {{-- ============================================================
         HALAMAN 2: LAMPIRAN RINCIAN BIAYA PENELITIAN
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
            </table>

            {{-- 1 ENTER --}}
            <div style="height: 14px;"></div>

            {{-- Catatan Administrasi --}}
            <div style="font-style: italic; font-size: 11pt; margin-bottom: 12px; line-height: 1.4;">
                Menyelesaikan biaya Administrasi di kasir sebelum pelaksanaan penelitian dimulai.
            </div>

            {{-- Tabel Lampiran Biaya Sesuai Format Word --}}
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; font-size: 10pt; margin-bottom: 16px;">
                <thead>
                    <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">
                        <th style="border: 1px solid #000; padding: 6px 4px; width: 35px; text-align: center;">NO</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">Biaya Penelitian & Pendidikan</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 110px; text-align: right;">Jasa Sarana</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 110px; text-align: right;">Jasa Pelayanan</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 120px; text-align: right;">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($biayaList as $bIdx => $item)
                        @php
                            $sarana = (float) ($item['jasa_sarana'] ?? 0);
                            $pelayanan = (float) ($item['jasa_pelayanan'] ?? 0);
                            $jmlOrg = (int) ($item['jumlah_orang'] ?? 1);
                            $subtotal = $item['subtotal'] ?? ($item['total'] ?? (($sarana + $pelayanan) * ($jmlOrg ?: 1)));
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: top;">{{ $bIdx + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">
                                <div>{{ $item['keterangan'] ?? '-' }}</div>
                                <div style="font-size: 9pt; color: #555;">{{ $jmlOrg }} org x Rp. {{ number_format($sarana + $pelayanan, 0, ',', '.') }}</div>
                            </td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: right; vertical-align: top;">
                                Rp. {{ number_format($sarana, 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: right; vertical-align: top;">
                                Rp. {{ number_format($pelayanan, 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 6px 8px; text-align: right; font-weight: bold; vertical-align: top;">
                                Rp. {{ number_format($subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="border: 1px solid #000; padding: 8px; text-align: center; color: #666;">Belum ada rincian biaya</td>
                        </tr>
                    @endforelse
                    <tr style="font-weight: bold;">
                        <td colspan="4" style="border: 1px solid #000; padding: 6px 8px; text-align: center; text-transform: uppercase;">Total</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: right;">
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

                @if ($qrCode)
                    <div style="padding: 4px 0;">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi Bank Surat" style="height: 58px; width: 58px; display: block;">
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
