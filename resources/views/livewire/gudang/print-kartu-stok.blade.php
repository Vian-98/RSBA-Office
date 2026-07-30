<div>
    <div>
        <div class="print-wrapper">
            <div class="thermal-label">

                <!-- Header RS -->
                <div class="header">
                    <div class="barcode">
                        <img src="data:image/png;base64,{{ $this->generateBarcodeBarang }}" alt="barcode-barang">
                    </div>
                    <div class="barang-info">
                        <div class="barang-name">{{ $barang->nama }}</div>
                        <div class="barang-ident">{{ $barang->sku }}</div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- table stok-->
                <div class="list-stok">
                    <table>
                        <thead>
                            <th>No.</th>
                            <th>No. Stok</th>
                            <th>Tgl Masuk</th>
                            <th>Stok Masuk</th>
                            <th>Stok Keluar</th>
                            <th>Sisa Stok</th>
                        </thead>
                        <tbody>
                            @forelse ($barang->stoks as $item)
                                <tr style="text-align: center;">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/y') }}</td>
                                    <td>{{ $item->penerimaanDet->jumlah }}</td>
                                    <td>{{ $item->distribusiDetails->sum('jml') }}</td>
                                    <td>{{ $item->stok }}</td>
                                </tr>
                            @empty
                                <span>Tidak Ada Data.</span>
                            @endforelse


                        </tbody>

                    </table>
                </div>
            </div>
        </div>



        {{-- Print Styles --}}
        <style>
            @media print {
                @page {
                    size: A5 potrait;
                    margin: 0;
                }

                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    font-size: 10px;
                }

                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }

            .print-wrapper {
                width: 100%;
            }

            .thermal-label {
                width: 100%;
                padding: 4px;
                box-sizing: border-box;
            }

            .header {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .barang-info {
                /* text-align: center; */
                font-size: 8.5px;
                line-height: 1;
            }

            .barang-info div {
                margin: 0;
            }


            .barang-name {
                font-weight: bold;
                font-size: 11px;
                line-height: 1.1;
                text-transform: uppercase;
            }

            .barang-identi {
                font-size: 8px;
            }

            .divider {
                border-top: 1px dashed black;
                margin: 4px 0;
            }

            .list-stok table {
                width: 100%;
                font-size: 9px;
            }

            .barcode {
                text-align: center;
                margin: 4px 0;
            }

            .barcode img {
                max-width: 100%;
                height: 40px;
            }
        </style>

    </div>
</div>
