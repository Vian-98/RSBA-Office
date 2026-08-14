<div>
    @if($kuitansi && !empty($kuitansi->id))
        {{-- Toolbar Tombol Cetak / Unduh (hidden saat print) --}}
        <div class="no-print mb-4 flex items-center justify-between rounded-lg bg-slate-50 border border-slate-200 p-3">
            <div class="flex items-center gap-2 text-xs text-slate-600">
                <x-tabler-info-circle class="size-4 text-indigo-500" />
                <span>Format cetak standar: <strong>A5 Landscape</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('keuangan.kuitansi.pdf', ['id' => $kuitansi->id ?? 0]) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <x-tabler-file-download class="size-4 text-rose-500" />
                    Unduh PDF
                </a>

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                    <x-tabler-printer class="size-4" />
                    Cetak Sekarang
                </button>
            </div>
        </div>

        {{-- Lembar Kuitansi Fisik --}}
        <div id="print-area-kuitansi" style="background:#fff; width:100%; max-width:800px; margin:0 auto; padding:20px; font-family:'Arial', sans-serif; color:#111; border: 1px solid #e2e8f0; border-radius: 8px;">

            {{-- Kop Surat RS --}}
            <div style="display:flex; align-items:center; border-bottom: 2px solid #000; padding-bottom:8px;">
                <div style="width:75px;">
                    @if($this->perusahaan?->logo && file_exists(storage_path('app/public/' . $this->perusahaan->logo)))
                        <img src="{{ asset('storage/' . $this->perusahaan->logo) }}" alt="Logo" style="height:52px; max-width:75px; object-fit:contain;">
                    @elseif(file_exists(public_path('logo-fallback.png')))
                        <img src="{{ asset('logo-fallback.png') }}" alt="Logo" style="height:52px; max-width:75px; object-fit:contain;">
                    @endif
                </div>
                <div style="flex:1; text-align:center; padding-right:75px;">
                    <h3 style="font-weight:bold; margin:0; font-size:17px; text-transform:uppercase; color:#000; letter-spacing:0.5px;">
                        RS. BINTANG AMIN
                    </h3>
                    <div style="font-size:10.5px; color:#333; margin-top:2px;">
                        Jl. Pramuka No. 27 Kemiling Bandar Lampung, Bandar Lampung<br>
                        Telp : (0721) 273601-273608 Email : cs@rspba.co.id
                    </div>
                </div>
            </div>

            {{-- Judul Kuitansi --}}
            <div style="text-align:center; margin: 10px 0 14px 0;">
                <span style="font-size:17px; font-weight:bold; font-style:italic; text-decoration:underline; letter-spacing:2px;">
                    KWITANSI
                </span>
            </div>


            {{-- Tabel Isi Data Kuitansi --}}
            <table style="width:100%; font-size:13px; line-height:1.6; border-collapse:collapse; margin-bottom:12px;">
                <tr>
                    <td style="width:170px; font-weight:bold; vertical-align:top;">No.</td>
                    <td style="width:15px; vertical-align:top;">:</td>
                    <td style="font-weight:bold; vertical-align:top; font-family:monospace; font-size:13.5px;">{{ $kuitansi->nomor }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Telah Diterima Dari</td>
                    <td style="vertical-align:top;">:</td>
                    <td style="vertical-align:top; font-weight:600;">{{ $kuitansi->diterima_dari ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Banyaknya Uang</td>
                    <td style="vertical-align:top;">:</td>
                    <td style="vertical-align:top; font-style:italic; background:#f8fafc; padding:2px 6px; border-radius:4px;">
                        {{ ucfirst(strtolower($kuitansi->terbilang)) }}.
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Untuk Pembayaran</td>
                    <td style="vertical-align:top;">:</td>
                    <td style="vertical-align:top;">
                        {{ $kuitansi->keterangan }}
                        @if($kuitansi->details && $kuitansi->details->count() > 1)
                            <table style="width:100%; border-collapse:collapse; font-size:11.5px; margin-top:6px;">
                                @foreach($kuitansi->details as $idx => $item)
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="width:20px; color:#64748b; padding:2px 0;">{{ $idx + 1 }}.</td>
                                        <td style="padding:2px 4px;">{{ $item->keterangan }}</td>
                                        <td style="text-align:right; font-family:monospace; padding:2px 0;">{{ formatRupiah($item->nominal, true, false) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                </tr>
                @if($kuitansi->metode_bayar)
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Metode Pembayaran</td>
                    <td style="vertical-align:top;">:</td>
                    <td style="vertical-align:top;">{{ $kuitansi->metode_bayar }}</td>
                </tr>
                @endif
            </table>

            {{-- Separator --}}
            <hr style="margin: 10px 0; border: none; border-top: 1px dashed #cbd5e1;">

            {{-- Bagian Nominal, QR Legalitas, & Tanda Tangan --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                {{-- Box Jumlah Nominal --}}
                <div style="background:#e5e9f2; padding:12px 24px; font-size:22px; font-weight:900; font-family:Arial, sans-serif; border-radius:4px; letter-spacing:0.5px;">
                    {{ formatRupiah($kuitansi->jumlah, true, false) }}
                </div>

                {{-- QR Code Legalitas Dokumen di Tengah --}}
                @if($this->generateQrCode)
                    <div style="text-align:center;">
                        <img src="data:image/png;base64,{{ $this->generateQrCode }}" alt="QR Legalitas" style="height:55px; width:55px; margin:0 auto;" />
                        <div style="font-size:8px; color:#64748b; margin-top:2px;">Verifikasi Dokumen</div>
                    </div>
                @endif


                {{-- Tanda Tangan Penerima (Ruang Kosong untuk TTD Basah / Paraf Kasir) --}}
                <div style="text-align:center; min-width:180px;">
                    <div style="font-size:12px; color:#111;">
                        Bandar lampung, {{ $kuitansi->tanggal ? \Carbon\Carbon::parse($kuitansi->tanggal)->translatedFormat('d M Y') : date('d M Y') }}
                    </div>
                    <div style="font-size:12px; font-weight:500; margin-top:2px;">Penerima,-</div>
                    
                    <div style="height:48px;"></div>

                    <div style="font-size:12px; color:#111;">
                        {{ $kuitansi->penerima_nama }}
                    </div>
                </div>
            </div>

            {{-- Footer Audit Trail Cetak --}}
            <div style="margin-top: 20px; font-size: 8.5px; color:#94a3b8; line-height:1.4;">
                <div>Dicetak : {{ now()->translatedFormat('d M Y - H:i:s') }}</div>
                <div>Oleh : {{ auth()->user()->karyawan->full_nama ?? auth()->user()->name }}</div>
            </div>


        </div>

        {{-- Print Stylesheet --}}
        <style>
            @media print {
                .no-print, header, nav, aside, .fi-topbar, .fi-sidebar {
                    display: none !important;
                }
                @page {
                    size: A5 landscape;
                    margin: 8mm;
                }
                body {
                    margin: 0;
                    padding: 0;
                    background: #fff !important;
                }
                #print-area-kuitansi {
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    max-width: 100% !important;
                    width: 100% !important;
                }
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>
    @else
        <div class="p-6 text-center text-slate-500 text-sm">
            Memuat data cetak kuitansi...
        </div>
    @endif
</div>
