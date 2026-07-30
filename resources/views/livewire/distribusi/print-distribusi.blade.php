<div class="h-full w-full">
    {{-- FIXME: style pdf & print popup tidak sama --}}
    <style>
        .invoice-container {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        /* Alignment */
        .invoice-container .text-right {
            text-align: right;
        }

        .invoice-container .text-center {
            text-align: center;
        }

        /* Table Styling */
        .invoice-container .invoice-table {
            width: 100%;
            border-collapse: collapse;
            overflow-wrap: break-word;
        }

        .invoice-container .invoice-table th,
        .invoice-container .invoice-table td {
            padding: 2mm 0mm,
                /* padding: 2mm 0; */
                /* text-align: left; */
                border-left: 0;
            border-right: 0;
        }

        /* Borders */
        .invoice-container .border-b {
            border-bottom: 1px solid #333;
        }

        .invoice-container .border-b-d {
            border-bottom: 1px dashed #999;
        }

        /* Padding */
        .invoice-container .pb-0 {
            padding-bottom: 0;
        }

        .invoice-container .pb-1 {
            padding-bottom: 1mm;
        }

        .invoice-container .pb-2 {
            padding-bottom: 2mm;
        }

        .invoice-container .pt-0 {
            padding-top: 0;
        }

        .invoice-container .p-x-1 {
            padding: 0 1mm;
        }

        .invoice-container .p-x-2 {
            padding: 0 2mm;
        }

        /* Typography */
        .invoice-container .font-bold {
            font-weight: bold;
        }

        .invoice-container h2 {
            padding: 0.5mm 0 0;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        /* Header */
        .invoice-header {
            text-align: center;
            /* margin-bottom: 3mm; */
            display: flex;
            flex-direction: column;
        }

        .invoice-header h1 {
            font-size: 16px;
            margin-top: 3mm;
            text-transform: uppercase;
            margin-bottom: 0;
        }

        .invoice-header h2 {
            font-size: 14px;
            text-transform: uppercase;
            padding: 0mm 0mm;
        }

        .invoice-header span {
            font-size: 14px;
            padding: 0mm 0mm;
        }

        /* .invoice-header p {
            font-size: 14px;
            margin: 0;
        } */

        /* Footer */
        .invoice-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10mm;
        }

        /* Table Header */
        .invoice-container .invoice-table th {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
        }

        /* Margin */
        .invoice-container .mt-0 {
            margin-top: 0;
        }
    </style>


    <div class="invoice-container">

        <div class="invoice-header">
            <span style="margin-top: 2mm">&nbsp;&nbsp;</span>
            <h1 class="mb-0 text-center uppercase">
                {{ $rs->nama }}
            </h1>
            <h2 class="text-xl">BAGIAN UMUM</h2>
            <span class="font-semibold">#{{ str_pad($distribusi->id, 6, '0', STR_PAD_LEFT) }}</span>
            <span>Tgl: {{ $distribusi->tanggal }}</span>
            <span>Operator: {{ $distribusi->pengirim_nama }}</span>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty Harga</th>
                    <th class="p-x-1 text-center">Disc</th>
                    <th class="text-right">Sub Total</th>
                </tr>
                <tr>
                    <th class="border-b-d" colspan="4" style="height: 0; padding:0;">
                        </td>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 0;
                    $total_price = 0;
                    $item_disc = [];
                    $total_tax = [];
                    $grand_total = 0;
                @endphp


                @foreach ($distribusi->details as $item)
                    <tr>
                        <td class="pb-0" style="" colspan="4">{{ $item->stoks->barang->nama }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="pt-0 text-center">{{ $item->jml . ' x ' . number_format($item->stoks->harga_satuan) }}</td>
                        <td class="pt-0 text-center">-</td>
                        <?php
                        $sub_total = $item->jml * $item->stoks->harga_satuan;
                        $total_price += $sub_total;
                        $item_disc = 0;
                        $total_tax = 0;
                        $grand_total = $total_price + $total_tax;
                        ?>
                        <td class="pt-0 text-right" style="">{{ number_format($sub_total) }}</td>
                    </tr>
                    <tr>
                        <td class="border-b-d" colspan="4" style="height: 0; padding:0;"></td>
                    </tr>
                @endforeach

                <tr>
                    <td class="text-center" colspan="4" style="padding-top:3mm; padding-bottom:0; font-size:16px">
                        SUMMARY
                    </td>
                </tr>

                <tr>
                    <td class="mt-2" colspan="3" style="font-size:16px; padding-top:0; padding-bottom:0">
                        Total Item
                    </td>
                    <td class="pt-0" style="text-align: right; font-size:16px padding-top:0; padding-bottom:0">
                        {{ number_format(count($distribusi->details)) }}
                    </td>
                </tr>
                <tr>
                    <td class="mt-2" colspan="3" style="font-size:16px; padding-top:0; padding-bottom:0">
                        Total
                    </td>
                    <td class="pt-0" style="text-align: right; font-size:16px; padding-top:0; padding-bottom:0">
                        {{ number_format($total_price) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size:16px; padding-top:0; padding-bottom:0">
                        Diskon
                    </td>
                    <td class="pb-0 pt-0" style=" font-size:16px; text-align: right; padding-top:0; padding-bottom:0; font-size:16px">
                        - {{ number_format(0) }}
                    </td>
                </tr>

                <tr>
                    <td class="border-b-d" colspan="4" style="height: 0; padding:0; padding-top:2mm"></td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size:16px">
                        Grand Total
                    </td>
                    <td class="text-right" style=" font-size:16px">
                        {{ formatRupiah($grand_total) }}
                    </td>
                <tr>
                <tr>
                    <td class="border-b-d" colspan="4" style="height: 0; padding:0;"></td>
                </tr>

            </tbody>
        </table>

        <div class="invoice-footer">
            <p>
                Thank you! <br>
                {{ "@$rs->singkatan" }}
            </p>
        </div>

    </div>

</div>
