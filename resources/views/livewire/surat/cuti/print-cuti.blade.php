<div id="print-cuti" style="width: 100%; margin: 0; padding: 15px; font-family: Arial, sans-serif;">
    <div align="center" class="mb-2">
        <img src="{{ ($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png') }}" style="height:60px;">
        <h2 class="bold uppercase">{{ $rs->nama }}</h2>
        <span class="text-sm">PERMOHONAN PENGAJUAN CUTI</span>
    </div>
    <table cellpadding="3" align="center" style="width: 100%; font-size:12px; ">
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
        <tr>
            <td style="width:200px;" class="bold">Kepada</td>
            <td class="bold">:</td>
            <td>Wadir SDM dan Umum</td>
            <td class="bold">Nomor</td>
            <td class="bold">:</td>
            <td><?= $suratCuti->no_surat ?></td>
        </tr>
        <tr>
            <td><b>Dari</b></td>
            <td><b>: </b></td>
            <td>{{ $suratCuti->karyawan->nama }} </td>
            <td><b>Tanggal</b></td>
            <td><b>:</b></td>
            <td><?= Carbon\Carbon::parse($suratCuti->tgl_surat)->translatedFormat('d M Y') ?></td>
        </tr>
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
    </table>

    <table cellpadding="1" align="center" style="width: 100%; font-size:12px; ">
        <tr>
            <td colspan="6">Dengan hormat, <br> Saya yang bertanda tangan dibawah ini : <br>
            </td>
        </tr>
        <tr>
            <td style="width:200px;" valign="top"><b>Nama</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4"><?= nl2br(e($this->karyawan->nama)) ?></td>
        </tr>
        <tr>
            <td valign="top"><b>NIP</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4"><?= nl2br(e($this->karyawan->nip)) ?></td>
        </tr>
        <tr>
            <td><b>Telepon</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $this->karyawan->hp ?></td>
        </tr>
        <tr>
            <td><b>Alamat</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $this->karyawan->alamat ?></td>
        </tr>
        <tr>
            <td><b>Jabatan</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ $this->karyawan->jabatan?->first()?->nama ?? null }}</td>
        </tr>
        <tr>
            <td><b>Kepentingan</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ $suratCuti->jenis->nama }} {{ $suratCuti->keterangan ? sprintf('(%s)', $suratCuti->keterangan) : '' }} </td>
        </tr>
        <tr>
            <td><b>Periode / Lama Cuti</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ \Carbon\Carbon::parse($suratCuti->tgl_mulai)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($suratCuti->tgl_akhir)->translatedFormat('d M Y') }}
                ({{ $suratCuti->lama_cuti }} hari)</td>
        </tr>
        @if ($suratCuti->is_penyesuaian_melahirkan && $suratCuti->tgl_melahirkan_aktual)
        <tr>
            <td><b>Tgl Melahirkan Aktual</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ \Carbon\Carbon::parse($suratCuti->tgl_melahirkan_aktual)->translatedFormat('d M Y') }} (Disesuaikan SDM H+45 hari persalinan)</td>
        </tr>
        @endif

        <tr>
            <td colspan="6">Demikian surat izin cuti ini saya ajukan. Atas perhatian dan diberikannya permohonan izin sajya ini, saya mengucapkan terima kasih.
            </td>
        </tr>
    </table>

    @php
        $isManualCuti = ($suratCuti->status->value === 'manual') || collect($this->approvals)->contains(fn($item) => !empty($item['is_manual']));
    @endphp

    @if ($isManualCuti || $suratCuti->status->value !== 'approved')
    <table style="width:100%;font-size:12px;margin-top:8px;" cellpadding="5">
        <tr>
            {{-- Pemohon --}}
            <td style="width:33%;text-align:center;">
                <p style="margin:0;"><strong>Pemohon,</strong></p>

                <span style="height:70px;display:block;"></span>

                <p style="margin:0;border-top:1px solid #777;display:inline-block;padding-top:5px;">
                    {{ $this->karyawan->nama ?? '_______________' }}
                </p>
            </td>

            {{-- Approvers --}}
            @foreach ($this->approvals as $approver)
                <td style="width:33%;text-align:center;">
                    <p style="margin:0;"><strong>{{ $approver['is_manual'] ? 'Mengetahui' : $approver['status'] . ' Oleh' }},</strong></p>

                    <span style="height:70px;display:block;"></span>

                    <p style="margin:0;border-top:1px solid #777;display:inline-block;padding-top:2px;">
                        {{ $approver['nama'] ?? '_______________' }}<br>
                        <span style="font-size:10px;">({{ $approver['jabatan'] ?? '' }})</span>
                    </p>
                </td>
            @endforeach
        </tr>
    </table>
    @endif


    <table style="width:100%;font-size:12px;margin-top:4px;" cellpadding="5">
        <tr>
            <td colspan="6">
                <table style="font-size:8px;">
                    <tr>
                        <td>Tembusan:</td>
                    </tr>
                    @if ($isManualCuti)
                        <tr><td>1. Direktur</td></tr>
                        <tr><td>2. Arsip</td></tr>
                    @else
                        @foreach ($this->approvals as $i => $approver)
                        <tr>
                            <td>{{ $i + 1 }}. {{ $approver['nama'] }} ({{ $approver['is_manual'] ? 'Mengetahui' : 'Menyetujui' }})</td>
                        </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td style="padding-top:5px;">
                            <img src="data:image/png;base64,{{ $this->generateHeaderQrCode }}" alt="QR Legalitas Dokumen" style="height:80px; width:80px; display:block;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <style>
        #print-cuti .bold {
            font-weight: bold;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</div>
