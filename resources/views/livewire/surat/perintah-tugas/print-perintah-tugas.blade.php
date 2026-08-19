<div id="print-perintah-tugas-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @if (!$this->canPrint)
        {{-- Docstore Error Alert Component --}}
        <x-surat.docstore-error-alert
            :error="$this->docstoreError"
            :docstore-key="$suratPerintahTugas->docstore_key ?? null"
        />
    @else
        {{-- Docstore Success State --}}
        @php
            $doc = $this->docstoreData['document'] ?? [];
            $content = $doc['content'] ?? [];

            $tglSuratIndo = !empty($content['tgl']) ? \Carbon\Carbon::parse($content['tgl'])->translatedFormat('d F Y') : \Carbon\Carbon::parse($suratPerintahTugas->tgl)->translatedFormat('d F Y');
            $namaDirektur = $content['nama_direktur'] ?? (optional($suratPerintahTugas->direktur)->full_nama ?? 'dr. Rachmawati, MPH');
            $nipDirektur  = $content['nip_direktur'] ?? (optional($suratPerintahTugas->direktur)->nip ?? '24170002');
            $nomorSurat   = $content['no'] ?? $suratPerintahTugas->no;
            $perihalSurat = $content['perihal'] ?? $suratPerintahTugas->perihal;
            $hariTanggal  = $content['hari_tanggal'] ?? $suratPerintahTugas->hari_tanggal;
            $waktu        = $content['waktu'] ?? $suratPerintahTugas->waktu;
            $tempat       = $content['tempat'] ?? $suratPerintahTugas->tempat;
            $karyawanList = $content['karyawan'] ?? $suratPerintahTugas->karyawanTugas->map(fn($k) => ['nama' => $k->nama, 'nip' => $k->nip, 'jabatan' => $k->jabatan_nama])->toArray();
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
            }
        </style>

        {{-- Docstore Verified Badge Component --}}
        <x-surat.docstore-badge
            :version="$this->docstoreData['meta']['version'] ?? ($this->docstoreData['document']['version'] ?? 1)"
            :docstore-key="$suratPerintahTugas->docstore_key"
        />

        <div style="min-height: 250mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- Kop Surat RSBA Component --}}
                <x-surat.kop-surat />

                {{-- Judul Surat Sesuai Template Word (Center, 14pt, Bold, Underline) --}}
                <div style="text-align: center; margin-bottom: 22px;">
                    <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-decoration: underline;">
                        SURAT PERINTAH TUGAS
                    </div>
                    <div style="font-size: 11pt; font-weight: bold; margin-top: 3px;">
                        Nomor : {{ $nomorSurat }}
                    </div>
                </div>

                {{-- Pemberi Perintah --}}
                <div style="margin-bottom: 14px; font-size: 11pt;">
                    <div style="margin-bottom: 6px;">Saya Yang Bertandatangan dibawah ini :</div>
                    <table style="width: calc(100% - 2rem); margin-left: 2rem; font-size: 11pt; margin-bottom: 6px; border-collapse: collapse; line-height: 1.4;">
                        <tr>
                            <td style="width: 100px; vertical-align: top; padding: 2px 0;">Nama</td>
                            <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $namaDirektur }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding: 2px 0;">NIP</td>
                            <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="vertical-align: top; padding: 2px 0; font-family: monospace;">{{ $nipDirektur }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding: 2px 0;">Jabatan</td>
                            <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">Direktur</td>
                        </tr>
                    </table>
                </div>

                {{-- Menugaskan Saudara --}}
                <div style="margin-bottom: 8px; font-size: 11pt;">
                    Menugaskan Saudara :
                </div>

                {{-- Tabel Karyawan yang Ditugaskan Sesuai Template Word --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5pt; margin-bottom: 16px;">
                    <thead>
                        <tr style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                            <th style="border: 1px solid #000; padding: 7px 6px; width: 40px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 7px 10px; text-align: left;">Nama</th>
                            <th style="border: 1px solid #000; padding: 7px 10px; width: 140px; text-align: center;">NIP</th>
                            <th style="border: 1px solid #000; padding: 7px 10px; width: 200px; text-align: left;">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($karyawanList as $idx => $item)
                            <tr>
                                <td style="border: 1px solid #000; padding: 7px 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $idx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; font-weight: bold; vertical-align: top;">{{ $item['nama'] ?? '-' }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; text-align: center; font-family: monospace; vertical-align: top;">{{ $item['nip'] ?? '-' }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; vertical-align: top;">{{ $item['jabatan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Isi Perintah Tugas --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    {{ $perihalSurat }} :
                </p>

                {{-- Rincian Waktu & Tempat --}}
                <table style="width: calc(100% - 2rem); margin-left: 2rem; font-size: 11pt; margin-bottom: 16px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 140px; vertical-align: top; padding: 2px 0;">Hari / Tanggal</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $hariTanggal }}</td>
                    </tr>
                    <tr>
                        <td style="width: 140px; vertical-align: top; padding: 2px 0;">Waktu</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0;">{{ $waktu }}</td>
                    </tr>
                    <tr>
                        <td style="width: 140px; vertical-align: top; padding: 2px 0;">Tempat</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $tempat }}</td>
                    </tr>
                </table>

                {{-- Penutup --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 8px 0; font-size: 11pt; line-height: 1.5;">
                    Demikian surat perintah ini dikeluarkan, agar dilaksanakan dengan penuh tanggungjawab.
                </p>
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 16px 0; font-size: 11pt; line-height: 1.5;">
                    Atas perhatian serta kerjasamanya kami ucapkan terimakasih.
                </p>
            </div>

            {{-- Kolom Tanda Tangan Direktur + QR Code Verifikasi Component --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                <div style="text-align: left; min-width: 250px; font-size: 11pt;">
                    <table style="width: 100%; font-size: 11pt; margin-bottom: 6px; border-collapse: collapse; line-height: 1.3;">
                        <tr>
                            <td style="width: 110px; padding: 1px 0;">Dikeluarkan di</td>
                            <td style="width: 10px; text-align: center; padding: 1px 0;">:</td>
                            <td style="padding: 1px 0; font-weight: bold;">Bandar Lampung</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0; text-decoration: underline;">Pada Tanggal</td>
                            <td style="text-align: center; padding: 1px 0;">:</td>
                            <td style="padding: 1px 0; text-decoration: underline;">{{ $tglSuratIndo }}</td>
                        </tr>
                    </table>
                    <div style="font-weight: bold; text-align: center; margin-bottom: 4px;">Direktur</div>

                    @if ($this->generateHeaderQrCode)
                        <div style="text-align: center; margin: 6px 0;">
                            <img src="data:image/png;base64,{{ $this->generateHeaderQrCode }}" alt="QR Verifikasi Bank Surat" style="height:75px; width:75px; display:inline-block;">
                            <span style="font-size:7pt; color:#555; display:block; margin-top:1px;">Scan verifikasi keabsahan</span>
                        </div>
                    @else
                        <div style="margin-bottom: 75px;"></div>
                    @endif

                    <div style="font-weight: bold; text-decoration: underline; text-align: center;">{{ $namaDirektur }}</div>
                    <div style="font-family: monospace; font-size: 10.5pt; text-align: center;">{{ $nipDirektur }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
