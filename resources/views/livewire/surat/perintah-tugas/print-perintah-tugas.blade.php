<div id="print-perintah-tugas-content" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; background: #fff; width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px;">
    @if ($suratPerintahTugas)
        @php
            $tglSuratIndo = \Carbon\Carbon::parse($suratPerintahTugas->tgl)->translatedFormat('d F Y');
            $namaDirektur = optional($suratPerintahTugas->direktur)->full_nama ?? 'dr. Rachmawati, MPH';
            $nipDirektur  = optional($suratPerintahTugas->direktur)->nip ?? '24170002';
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

        <div style="min-height: 250mm; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                {{-- KOP SURAT RESMI RS BINTANG AMIN --}}
                <div style="display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px;">
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

                {{-- Judul Surat Sesuai Template Word (Center, 14pt, Bold, Underline) --}}
                <div style="text-align: center; margin-bottom: 22px;">
                    <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-decoration: underline;">
                        SURAT PERINTAH TUGAS
                    </div>
                    <div style="font-size: 11pt; font-weight: bold; margin-top: 3px;">
                        Nomor : {{ $suratPerintahTugas->no }}
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
                            <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="vertical-align: top; padding: 2px 0; font-family: monospace;">{{ $nipDirektur }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding: 2px 0;">Jabatan</td>
                            <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
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
                        @foreach($suratPerintahTugas->karyawanTugas as $idx => $item)
                            <tr>
                                <td style="border: 1px solid #000; padding: 7px 6px; text-align: center; font-weight: bold; vertical-align: top;">{{ $idx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; font-weight: bold; vertical-align: top;">{{ $item->nama }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; text-align: center; font-family: monospace; vertical-align: top;">{{ $item->nip }}</td>
                                <td style="border: 1px solid #000; padding: 7px 10px; vertical-align: top;">{{ $item->jabatan_nama }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Isi Perintah Tugas --}}
                <p style="text-align: justify; text-indent: 35px; margin: 0 0 10px 0; font-size: 11pt; line-height: 1.5;">
                    {{ $suratPerintahTugas->perihal }} :
                </p>

                {{-- Rincian Waktu & Tempat --}}
                <table style="width: calc(100% - 2rem); margin-left: 2rem; font-size: 11pt; margin-bottom: 16px; border-collapse: collapse; line-height: 1.4;">
                    <tr>
                        <td style="width: 140px; vertical-align: top; padding: 2px 0;">Hari / Tanggal</td>
                        <td style="width: 15px; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $suratPerintahTugas->hari_tanggal }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0;">Waktu</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0;">{{ $suratPerintahTugas->waktu }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 2px 0;">Tempat</td>
                        <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                        <td style="vertical-align: top; padding: 2px 0; font-weight: bold;">{{ $suratPerintahTugas->tempat }}</td>
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

            {{-- Kolom Tanda Tangan Direktur Sesuai Template Word --}}
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
                    <div style="font-weight: bold; text-align: center; margin-bottom: 75px;">Direktur</div>
                    <div style="font-weight: bold; text-decoration: underline; text-align: center;">{{ $namaDirektur }}</div>
                    <div style="font-family: monospace; font-size: 10.5pt; text-align: center;">{{ $nipDirektur }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
