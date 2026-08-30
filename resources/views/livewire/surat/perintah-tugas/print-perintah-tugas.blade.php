<div id="print-perintah-tugas-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 0 10px;">
    @php
        $isDocstore = ($fromDocstore ?? false) && !empty($docstoreData);
        $doc = ($docstoreData ?? [])['document'] ?? [];
        $content = $isDocstore ? ($doc['content'] ?? []) : [];

        $tglSuratIndo = !empty($content['tgl']) ? \Carbon\Carbon::parse($content['tgl'])->translatedFormat('d F Y') : ($suratPerintahTugas->tgl ? \Carbon\Carbon::parse($suratPerintahTugas->tgl)->translatedFormat('d F Y') : '-');
        $namaDirektur = $content['nama_direktur'] ?? (optional($suratPerintahTugas->direktur)->full_nama ?? 'dr. Rachmawati, MPH');
        $nipDirektur  = $content['nip_direktur'] ?? (optional($suratPerintahTugas->direktur)->nip ?? '24170002');
        $nomorSurat   = $content['no'] ?? $suratPerintahTugas->no;
        $perihalSurat = $content['perihal'] ?? $suratPerintahTugas->perihal;
        $rawHariTanggal = $content['hari_tanggal'] ?? ($suratPerintahTugas->hari_tanggal_indo ?? $suratPerintahTugas->hari_tanggal);
        $dayMonthMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
            'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
            'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];
        $hariTanggal  = strtr($rawHariTanggal ?: '', $dayMonthMap);
        $waktu        = $content['waktu'] ?? $suratPerintahTugas->waktu;
        $tempat       = $content['tempat'] ?? $suratPerintahTugas->tempat;
        $karyawanList = $content['karyawan'] ?? $suratPerintahTugas->karyawanTugas->map(fn($k) => ['nama' => $k->nama, 'nip' => $k->nip, 'jabatan' => $k->jabatan_nama])->toArray();
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
        }
    </style>

    @if ($isDocstore)
        {{-- Docstore Verified Badge Component --}}
        <x-surat.docstore-badge
            :version="($docstoreData['meta']['version'] ?? ($docstoreData['document']['version'] ?? 1))"
            :docstore-key="$suratPerintahTugas->docstore_key"
        />
    @elseif(!empty($suratPerintahTugas->docstore_key))
        <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 10px; font-weight: bold;" class="no-print">
            🔒 TERDAFTAR DI DOCSTORE VAULT — Key: {{ $suratPerintahTugas->docstore_key }}
        </div>
    @endif

    <div style="min-height: 255mm; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            {{-- Kop Surat RSBA Logo Resmi (5.54cm x 2.75cm) --}}
            <x-surat.kop-resmi />

            {{-- Judul Surat Sesuai Template Word --}}
            <div style="text-align: center; margin-bottom: 16px;">
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: underline;">
                    SURAT PERINTAH TUGAS
                </div>
                <div style="font-size: 11pt; font-weight: bold; margin-top: 2px;">
                    Nomor : {{ $nomorSurat }}
                </div>
            </div>

            {{-- Pemberi Perintah --}}
            <div style="margin-bottom: 10px; font-size: 11pt; line-height: 1.5;">
                <div style="margin-bottom: 4px;">Saya Yang Bertandatangan dibawah ini :</div>
                <table style="width: 100%; font-size: 11pt; margin: 0; border-collapse: collapse; line-height: 1.35;">
                    <tr>
                        <td style="width: 80px; vertical-align: top; padding: 1px 0;">Nama</td>
                        <td style="width: 15px; vertical-align: top; padding: 1px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 1px 0; font-weight: bold;">{{ $namaDirektur }}</td>
                    </tr>
                    <tr>
                        <td style="width: 80px; vertical-align: top; padding: 1px 0;">NIP</td>
                        <td style="width: 15px; vertical-align: top; padding: 1px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 1px 0;">{{ $nipDirektur }}</td>
                    </tr>
                    <tr>
                        <td style="width: 80px; vertical-align: top; padding: 1px 0;">Jabatan</td>
                        <td style="width: 15px; vertical-align: top; padding: 1px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 1px 0; font-weight: bold;">Direktur</td>
                    </tr>
                </table>
            </div>

            {{-- Menugaskan Saudara --}}
            <div style="margin-bottom: 8px; font-size: 11pt; line-height: 1.5;">
                Menugaskan Saudara :
            </div>

            {{-- Tabel Karyawan yang Ditugaskan --}}
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; font-size: 10pt; margin-bottom: 12px;">
                <thead>
                    <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">
                        <th style="border: 1px solid #000; padding: 6px 4px; width: 35px; text-align: center;">No</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; text-align: left;">Nama</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 140px; text-align: center;">NIP</th>
                        <th style="border: 1px solid #000; padding: 6px 8px; width: 180px; text-align: left;">Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawanList as $idx => $item)
                        <tr>
                            <td style="border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: top;">{{ $idx + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; font-weight: bold; vertical-align: top;">{{ $item['nama'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; vertical-align: top;">{{ $item['nip'] ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 5px 8px; vertical-align: top;">{{ $item['jabatan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="border: 1px solid #000; padding: 8px; text-align: center; color: #666;">Belum ada karyawan yang ditugaskan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Isi Perintah Tugas --}}
            <div style="text-align: justify; margin: 0 0 8px 0; font-size: 11pt; line-height: 1.5;">
                {{ $perihalSurat }} :
            </div>

            {{-- Rincian Waktu & Tempat --}}
            <table style="width: 100%; font-size: 11pt; margin-bottom: 12px; border-collapse: collapse; line-height: 1.4;">
                <tr>
                    <td style="width: 120px; vertical-align: top; padding: 2px 0;">Hari / Tanggal</td>
                    <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $hariTanggal }}</td>
                </tr>
                <tr>
                    <td style="width: 120px; vertical-align: top; padding: 2px 0;">Waktu</td>
                    <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 2px 0;">{{ $waktu }}</td>
                </tr>
                <tr>
                    <td style="width: 120px; vertical-align: top; padding: 2px 0;">Tempat</td>
                    <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                    <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $tempat }}</td>
                </tr>
            </table>

            {{-- Penutup --}}
            <div style="text-align: justify; margin: 0 0 6px 0; font-size: 11pt; line-height: 1.5; text-indent: 0;">
                Demikian surat perintah ini dikeluarkan, agar dilaksanakan dengan penuh tanggungjawab.
            </div>
            <div style="text-align: justify; margin: 0 0 14px 0; font-size: 11pt; line-height: 1.5; text-indent: 0;">
                Atas perhatian serta kerjasamanya kami ucapkan terimakasih.
            </div>

            @php
                $statusSurat = $suratPerintahTugas->status?->value ?? (string)$suratPerintahTugas->status;
                $isApproved = ($statusSurat === 'approved');
            @endphp

            {{-- Kolom Tanda Tangan Direktur --}}
            <div style="text-align: left; font-size: 11pt; line-height: 1.35; margin-top: 10px;">
                <table style="font-size: 11pt; margin: 0 0 4px 0; border-collapse: collapse; line-height: 1.25;">
                    <tr>
                        <td style="width: 100px; padding: 0;">Dikeluarkan di</td>
                        <td style="width: 12px; text-align: center; padding: 0;">:</td>
                        <td style="padding: 0; font-weight: bold;">Bandar Lampung</td>
                    </tr>
                    <tr>
                        <td style="padding: 0;">Pada Tanggal</td>
                        <td style="text-align: center; padding: 0;">:</td>
                        <td style="padding: 0;">{{ $tglSuratIndo }}</td>
                    </tr>
                </table>
                <div style="font-weight: bold; margin-top: 4px;">Direktur</div>

                @if ($qrCode)
                    <div style="padding: 4px 0;">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi Bank Surat" style="height: 58px; width: 58px; display: block;">
                        @if(!$isApproved)
                            <div style="font-size: 7.5pt; font-weight: bold; color: #b45309; padding-top: 2px; letter-spacing: 0.02em;">[ DRAF / MENUNGGU PERSETUJUAN ]</div>
                        @endif
                    </div>
                @else
                    <div style="height: 55px;"></div>
                @endif

                <div style="font-weight: bold;">{{ $namaDirektur }}</div>
                <div>{{ $nipDirektur }}</div>
                @if(!$isApproved)
                    <div style="font-size: 8pt; color: #64748b; font-style: italic;">(Menunggu Otorisasi)</div>
                @endif
            </div>
        </div>

        {{-- Footer Kontak Resmi --}}
        <x-surat.footer-resmi />
    </div>
</div>
