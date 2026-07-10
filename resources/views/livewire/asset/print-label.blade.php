 <div>
     <div class="thermal-wrapper">
         <div class="thermal-label">

             <!-- Header RS -->
             <div class="header">
                 <img src="{{ asset('storage/' . $rs->logo) }}" class="logo">
                 <div class="rs-info">
                     <div class="rs-name">{{ $rs->nama }}</div>
                     <div class="rs-address">{{ $rs->alamat }}</div>
                 </div>
             </div>

             <div class="divider"></div>

             <!-- Barcode -->

             @if($this->generateBarcode)
             <div class="barcode">
                 <img src="data:image/png;base64,{{ $this->generateBarcode }}" alt="barcode">
             </div>
             @endif

             <!-- Asset Info -->
             <div class="asset-info">
                 <div class="kode">{{ $assetBarang->kode }}</div>
                 <div>{{ $assetBarang->barang->nama }}</div>
                 <div>{{ $assetBarang->ruangan->nama }}</div>
                 {{-- <div>{{ Carbon\Carbon::parse($assetBarang->tanggal_catat)->locale('ID')->translatedFormat('d M Y') }}</div> --}}
             </div>

         </div>
     </div>



     {{-- Print Styles --}}
     <style>
         @media print {

             @page {
                 size: 58mm auto;
                 margin: 0;
             }

             html,
             body {
                 width: 58mm;
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

         .thermal-wrapper {
             width: 58mm;
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

         .logo {
             height: 28px;
             width: auto;
         }

         .rs-name {
             font-weight: bold;
             font-size: 11px;
             line-height: 1.1;
             text-transform: uppercase;
         }

         .rs-address {
             font-size: 8px;
         }

         .divider {
             border-top: 1px dashed black;
             margin: 4px 0;
         }

         .barcode {
             text-align: center;
             margin: 4px 0;
         }

         .barcode img {
             max-width: 100%;
             height: 40px;
         }

         .asset-info {
             text-align: center;
             font-size: 8.5px;
             line-height: 1;
         }

         .asset-info div {
             margin: 0;
         }

         .kode {
             font-weight: bold;
             font-size: 10px;
             margin-bottom: 1px;
         }
     </style>

 </div>
