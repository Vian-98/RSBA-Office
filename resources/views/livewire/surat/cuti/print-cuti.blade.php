<div id="print-cuti" style="width: 100%; margin: 0; padding: 15px; font-family: Arial, sans-serif;">

    {{-- ============================================================
         DOCSTORE ERROR STATE — tampil jika belum sync ke bank surat
         ============================================================ --}}
    @if (!$this->canPrint)
        <div style="
            background: linear-gradient(135deg, #fef2f2, #fff0f0);
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        ">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <svg style="width:24px;height:24px;color:#dc2626;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p style="font-weight: bold; color: #991b1b; margin: 0 0 6px 0; font-size: 14px;">
                        Surat Tidak Dapat Dicetak
                    </p>
                    <p style="color: #7f1d1d; margin: 0; font-size: 13px; line-height: 1.5;">
                        {{ $this->docstoreError ?? 'Data surat tidak dapat dimuat dari bank surat (docstore).' }}
                    </p>
                    @if (empty($suratCuti->docstore_key))
                        <p style="color: #b91c1c; margin: 8px 0 0 0; font-size: 11px;">
                            ℹ️ Pastikan proses approval telah dilakukan. Surat akan otomatis tersinkronisasi ke bank surat setelah ada perubahan status.
                        </p>
                    @else
                        <p style="color: #b91c1c; margin: 8px 0 0 0; font-size: 11px;">
                            ℹ️ Pastikan server bank surat (docstore) aktif di <code>{{ env('DOCSTORE_API_URL', 'http://localhost:8000/api') }}</code>
                        </p>
                    @endif
                </div>
            </div>
        </div>

    @else
    {{-- ============================================================
         DOCSTORE SUCCESS STATE — render surat dari bank surat
         ============================================================ --}}

    {{-- Badge verifikasi bank surat --}}
    <div style="
        background: linear-gradient(135deg, #064e3b, #065f46);
        color: #d1fae5;
        border-radius: 6px;
        padding: 6px 12px;
        margin-bottom: 12px;
        font-size: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.05em;
    " class="no-print">
        🔒 DATA TERVERIFIKASI DARI BANK SURAT (DOCSTORE) — Versi: v{{ $this->docstoreData['meta']['version'] ?? 1 }}
        &nbsp;|&nbsp; Key: {{ $suratCuti->docstore_key }}
    </div>

    @php
        // Gunakan data dari docstore sebagai source of truth
        $doc = $this->docstoreData['document'] ?? [];
        $content = $doc['content'] ?? [];
    @endphp

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
            <td><?= $content['no_surat'] ?? $suratCuti->no_surat ?></td>
        </tr>
        <tr>
            <td><b>Dari</b></td>
            <td><b>: </b></td>
            <td>{{ $content['karyawan_name'] ?? $suratCuti->karyawan->nama }}</td>
            <td><b>Tanggal</b></td>
            <td><b>:</b></td>
            <td><?= $content['tgl_surat'] ? \Carbon\Carbon::parse($content['tgl_surat'])->translatedFormat('d M Y') : \Carbon\Carbon::parse($suratCuti->tgl_surat)->translatedFormat('d M Y') ?></td>
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
            <td colspan="4"><?= nl2br(e($content['karyawan_name'] ?? $this->karyawan->nama)) ?></td>
        </tr>
        <tr>
            <td valign="top"><b>NIP</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4"><?= nl2br(e($content['karyawan_nip'] ?? $this->karyawan->nip)) ?></td>
        </tr>
        <tr>
            <td><b>Telepon</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $content['karyawan_hp'] ?? $this->karyawan->hp ?></td>
        </tr>
        <tr>
            <td><b>Alamat</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $content['alamat'] ?? $this->karyawan->alamat ?></td>
        </tr>
        <tr>
            <td><b>Jabatan</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ $content['karyawan_jabatan'] ?? ($this->karyawan->jabatan?->first()?->nama ?? null) }}</td>
        </tr>
        <tr>
            <td><b>Kepentingan</b></td>
            <td><b>:</b></td>
            <td colspan="4">{{ $content['jenis_cuti'] ?? $suratCuti->jenis->nama }} {{ ($content['keterangan'] ?? $suratCuti->keterangan) ? sprintf('(%s)', $content['keterangan'] ?? $suratCuti->keterangan) : '' }} </td>
        </tr>
        <tr>
            <td><b>Periode / Lama Cuti</b></td>
            <td><b>:</b></td>
            <td colspan="4">
                {{ \Carbon\Carbon::parse($content['tgl_mulai'] ?? $suratCuti->tgl_mulai)->translatedFormat('d M Y') }}
                s/d
                {{ \Carbon\Carbon::parse($content['tgl_akhir'] ?? $suratCuti->tgl_akhir)->translatedFormat('d M Y') }}
                ({{ $content['lama_cuti'] ?? $suratCuti->lama_cuti }} hari)
            </td>
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
        $docStatus = $this->docstoreData['document']['status'] ?? 'pending';
        $isManualCuti = ($docStatus === 'manual') || collect($this->approvals)->contains(fn($item) => !empty($item['is_manual']));
    @endphp

    @if ($isManualCuti || $docStatus !== 'approved')
    <table style="width:100%;font-size:12px;margin-top:8px;" cellpadding="5">
        <tr>
            {{-- Pemohon --}}
            <td style="width:33%;text-align:center;">
                <p style="margin:0;"><strong>Pemohon,</strong></p>

                <span style="height:70px;display:block;"></span>

                <p style="margin:0;border-top:1px solid #777;display:inline-block;padding-top:5px;">
                    {{ $content['karyawan_name'] ?? ($this->karyawan->nama ?? '_______________') }}
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
                            {{-- QR Code — embed docstore_key agar scan langsung ke bank surat --}}
                            <img src="data:image/png;base64,{{ $this->generateHeaderQrCode }}" alt="QR Verifikasi Bank Surat" style="height:80px; width:80px; display:block;">
                            <span style="font-size:7px; color:#555; display:block; margin-top:2px;">
                                Scan untuk verifikasi keaslian surat
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @endif {{-- end @if (!$this->canPrint) ... @else ... @endif --}}

    <style>
        #print-cuti .bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }

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
