<div id="print-sp3" style="width: 100%; margin: 0; padding: 15px; font-family: Arial, sans-serif;">

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
                    @if (empty($suratSp3->docstore_key))
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

    {{-- Badge verifikasi bank surat (hanya tampil di layar, tidak tercetak) --}}
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
        &nbsp;|&nbsp; Key: {{ $suratSp3->docstore_key }}
    </div>

    @php
        // Gunakan data dari docstore sebagai source of truth
        $doc = $this->docstoreData['document'] ?? [];
        $content = $doc['content'] ?? [];
        $docStatus = $doc['status'] ?? 'pending';
        $items = $content['items'] ?? $suratSp3->details->map(fn($d) => ['keterangan' => $d->keterangan, 'nominal' => $d->nominal])->toArray();
        $totalNominal = collect($items)->sum('nominal');

        $isManualSp3 = ($docStatus === 'manual') || collect($this->approvals)->contains(function($item) {
            $st = strtolower(is_object($item['status'] ?? '') ? $item['status']->value : (string)($item['status'] ?? ''));
            return in_array($st, ['manual', 'approved manual']) || !empty($item['is_manual']);
        });
    @endphp

    <div align="center" class="mb-2">
        <img src="{{ ($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png') }}" style="height:60px;">
        <h2 class="bold text-lg uppercase">{{ $rs->nama }}</h2>
        <span class="text-sm">SURAT PERMINTAAN PROSES PEMBAYARAN<br>(Kontrak, Sundries, Material, dll.)</span>
    </div>
    <table cellpadding="3" align="center" style="width: 100%; font-size:11px; ">
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
        <tr>
            <td style="width:200px;" class="bold">Kepada</td>
            <td class="bold">:</td>
            <td>Wadir Keuangan</td>
            <td class="bold">Nomor</td>
            <td class="bold">:</td>
            <td><?= $content['no'] ?? $suratSp3->no ?></td>
        </tr>
        <tr>
            <td><b>Dari</b></td>
            <td><b>:</b></td>
            <td><?= $suratSp3->jabatans->nama ?></td>
            <td><b>Tanggal</b></td>
            <td><b>:</b></td>
            <td><?= \Carbon\Carbon::parse($content['tgl'] ?? $suratSp3->tgl)->translatedFormat('d M Y') ?></td>
        </tr>
        <tr>
            <td colspan="6" style="border-top:1px solid;"></td>
        </tr>
        <tr>
            <td colspan="6"><b>Terlampir dikirimkan dokumen pendukung pembayaran atas (Kontrak, Sundries, Material, dll) sbb:</b><br></td>
        </tr>
        <tr>
            <td valign="top"><b>Keterangan Pembayaran</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4"><?= nl2br(e($content['keterangan'] ?? $suratSp3->keterangan)) ?></td>
        </tr>
        <tr>
            <td><b>Nama Rekanan / Pelaksana</b></td>
            <td><b>:</b></td>
            <td colspan="4"><?= $content['rekanan'] ?? $suratSp3->rekanan ?></td>
        </tr>
        <tr>
            <td valign="top"><b>Untuk Pembayaran</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4">
                <table cellpadding="3" border="1" style='font-size:10px;width:95%;border:1px solid black;'>
                    @foreach ($items as $idx => $item)
                        <tr style="border:1px solid black;">
                            <td>{{ $idx + 1 }}.</td>
                            <td style="width:60%">{{ $item['keterangan'] }}</td>
                            <td style="width:40%" align="right">{{ formatRupiah($item['nominal'], true, false) }}</td>
                        </tr>
                    @endforeach
                    <tr style="border:1px solid black;font-weight:bold;">
                        <td colspan="2">Total</td>
                        <td align="right">{{ formatRupiah($totalNominal, true, false) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top"><b>Terbilang</b></td>
            <td valign="top"><b>:</b></td>
            <td colspan="4">
                {{ terbilang($totalNominal) }}<br>
            </td>
        </tr>
        <tr>
            <td><b>Cara Pembayaran</b></td>
            <td><b>:</b></td>
            <td colspan="4">
                {{ $suratSp3->method_bayar }}
            </td>
        </tr>
        <tr>
            <td colspan="6"><b>Demikian untuk diterima dengan baik dan pelaksanaan pembuatan bukti kas / bank untuk proses pembayaran selanjutnya.<br>Atas perhatian dan kerjasamanya diucapkan
                    terima kasih.</b></td>
        </tr>

        @if ($isManualSp3 || $docStatus !== 'approved')
        <tr>
            @forelse ($this->approvals as $item)
                <td colspan="6" align="right">
                    <table style="font-size:11px; width:33%; text-align: center;">
                        <tr>
                            <td>{{ ($item['status'] == 'Manual' || !empty($item['is_manual'])) ? 'Mengetahui' : $item['status'] . ' Oleh' }},</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <span style="display:block; height:60px; width:auto;"></span>
                                <span style="font-weight: bold; display:block; margin: 0 auto;">{{ $item['nama'] }}</span>
                                <span style="font-size:10px;">({{ is_array($item['jabatan'] ?? null) ? ($item['jabatan'][0]['nama'] ?? ' ') : ($item['jabatan'] ?? ' ') }})</span>
                            </td>
                        </tr>
                    </table>
                </td>
            @empty
                <td colspan="6" align="right">
                    <table style="font-size:11px; font-style:italic;">
                        <tr>
                            <td>Menunggu Persetujuan TTD Basah</td>
                        </tr>
                    </table>
                </td>
            @endforelse
        </tr>
        @endif

        <tr>
            <td colspan="6">
                <table style="font-size:8px;">
                    <tr>
                        <td>Tembusan:</td>
                    </tr>
                    <tr>
                        <td>1. Direktur</td>
                    </tr>
                    <tr>
                        <td>2. Arsip</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 5px;">
                            {{-- QR Code — embed docstore_key agar scan langsung ke bank surat --}}
                            <img src="data:image/png;base64,{{ $this->generateHeaderQrCode }}" alt="QR Verifikasi Bank Surat" style="height: 55px; width: 55px; display: block;">
                            <span style="font-size:7px; color:#555; display:block; margin-top:2px;">
                                Scan untuk verifikasi
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @endif {{-- end @if (!$this->canPrint) ... @else ... @endif --}}

    <style>
        #print-sp3 .bold {
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
