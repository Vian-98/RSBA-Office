<div id="print-po" style="width: 100%; margin: 0; padding: 15px; font-family: Arial, sans-serif;">
    {{-- Header --}}
    <div align="center">
        <img src="{{ ($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png') }}" style="height: 60px;">
        <h2 style="font-size: 18px; font-weight: bold; text-transform: uppercase; margin:4px 0;">{{ $rs->nama }}</h2>
        <p style="margin: 0; font-size: 11px;">{{ $rs->alamat ?? '' }}</p>
        <p style="margin: 0; font-size: 11px;">Telp: {{ $rs->telepon ?? '' }}</p>
        <hr style="margin: 15px 0; border: 1px solid #000;">
        <h3 style="margin: 6px 0; font-size: 16px; font-weight: bold;">SURAT PESANAN (<span style="font-style: italic">PURCHASE ORDER</span>)</h3>
    </div>

    {{-- Info Table --}}
    <table style="width: 100%; font-size: 11px; margin-bottom: 10px;" cellpadding="1">
        <tr>
            <td style="width: 15%;"><strong>Kepada</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 43%;">{{ $pembelian->supplier->nama ?? '-' }}</td>
            <td style="width: 15%;"><strong>Nomor PO</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">{{ $pembelian->no }}</td>
        </tr>
        <tr>
            <td><strong>Alamat</strong></td>
            <td>:</td>
            <td>{{ $pembelian->supplier->alamat ?? '-' }}</td>
            <td><strong>Tanggal</strong></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($pembelian->tgl)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Telepon</strong></td>
            <td>:</td>
            <td>{{ $pembelian->supplier->telp ?? '-' }}</td>
            <td><strong>Dari</strong></td>
            <td>:</td>
            <td>{{ $rs->nama }}</td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px;" border="1">
        <colgroup>
            <col style="width:5%">
            <col style="width:20%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:14%">
            <col style="width:15%">
            <col style="width:15%">
            <col style="width:15%">
        </colgroup>
        <thead>
            <tr style="background-color: #eeeeee;">
                <th style="text-align: center;">No</th>
                <th style="text-align: left;">Nama Barang / Deskripsi</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: center;">Satuan</th>
                <th style="text-align: right;">Harga Satuan</th>
                <th style="text-align: right;">Diskon</th>
                <th style="text-align: right;">PPN</th>
                <th style="text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $item['nama_barang'] }}</td>
                    <td style="text-align: center;">{{ $item['qty'] }}</td>
                    <td style="text-align: center;">{{ $item['satuan'] }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item['diskon'], 0, ',', '.') }}</td>
                    <td style="text-align: right;"> ({{ $item['ppn'] > 0 ? $item['ppn'] . '%' : '-' }}) Rp {{ number_format($item['ppn_amount'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item['qty'] * $item['harga'] - $item['diskon'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @foreach ($this->getSummaryBayar as $item)
                <tr>
                    <td colspan="7" style="text-align: right; font-weight: bold;">{{ $item['label'] }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item['nilai'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tfoot>
    </table>

    {{-- Notes --}}
    @if (isset($catatan) && $catatan)
        <div style="margin-bottom: 20px; font-size: 11px;">
            <strong>Catatan:</strong>
            <p style="margin: 5px 0;">{{ $catatan }}</p>
        </div>
    @endif

    {{-- Terms --}}
    <div style="margin-bottom: 30px; font-size: 9px;">
        <strong>Syarat & Ketentuan:</strong>
        <ol style="margin: 5px 0; padding-left: 20px;">
            <li>1. Barang dikirim sesuai spesifikasi yang dipesan</li>
            <li>2. Pembayaran {{ $term_pembayaran ?? 'sesuai kesepakatan' }}</li>
            <li>3. Barang yang rusak atau tidak sesuai dapat dikembalikan</li>
        </ol>
    </div>

    {{-- Signature --}}
    <table style="width: 100%; font-size: 11px; margin-top: 28px;" cellpadding="5">
        <tr>
            <td style="width: 25%; text-align: center;">
                <p style="margin: 0; height: 50px;"><strong>Dibuat Oleh,</strong></p>
                <p style="margin: 0; border-top: 1px solid #777; display: inline-block; padding-top: 5px;">
                    {{ $pembelian->user_created ?? '_______________' }}
                </p>
            </td>
            <td style="width: 25%; text-align: center;">
                <p style="margin: 0; height: 50px;"><strong>Mengetahui,</strong></p>
                <p style="margin: 0; border-top: 1px solid #777; display: inline-block; padding-top: 5px;">
                    {{ $mengetahui ?? '_______________' }}
                </p>
            </td>
            <td style="width: 25%; text-align: center;">
                <p style="margin: 0; height: 50px;"><strong>Menyetujui,</strong></p>

                <p style="margin: 0; border-top: 1px solid #777; display: inline-block; padding-top: 5px;">
                    {{ $menyetujui ?? '_______________' }}
                </p>
            </td>
            <td style="width: 25%; text-align: center;">
                <p style="margin: 0; height: 50px;"><strong>Verifikator,</strong></p>

                <p style="margin: 0; border-top: 1px solid #777; display: inline-block; padding-top: 5px;">
                    {{ $verifikator ?? '_______________' }}
                </p>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #666;">
        <p style="margin: 0;">Dokumen ini dibuat secara elektronik dan sah tanpa tanda tangan basah</p>
    </div>

    {{-- Print Styles --}}
    <style>
        #print-po thead th {
            border-bottom: 0.5px solid #666;
        }

        #print-po tbody tr:last-child td {
            border-bottom: 0.5px solid #666;
        }

        #print-po tfoot tr:last-child td {
            border-bottom: 0.5px solid #666;
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
