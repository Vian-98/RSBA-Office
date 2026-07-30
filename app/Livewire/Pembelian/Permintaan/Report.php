<?php

namespace App\Livewire\Pembelian\Permintaan;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use App\Models\Gudang\PembelianRequest;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Gudang\PembelianRequestDetails;

#[Lazy]
class Report extends Component
{
    public Collection $pembelian_request_details;
    public ?int $pembelianReqId;
    public $pembelianRequest;
    public $pembelianRequestDetails;

    public function mount($beliReqIdSelected)
    {
        $this->pembelianReqId = $beliReqIdSelected;
        $this->getPembelianRequest();
        $this->getPembelianRequestDetils();
    }


    #[Computed]
    public function getPembelianRequest()
    {
        $this->pembelianRequest =  PembelianRequest::findOrFail($this->pembelianReqId);
    }

    #[Computed]
    public function getPembelianRequestDetils()
    {
        $this->pembelianRequestDetails = PembelianRequestDetails::with(
            [
                'barang',
                'barang.kategori',
                'barang.satuan'
            ]
        )
            ->where('pembelian_req_id', $this->pembelianReqId)
            ->get();
    }

    public function generatePdf()
    {
        $data = session('pdf_data', [
            'title' => 'Sample Document',
            'content' => 'Default content',
            'date' => date('m/d/Y H:i:s')
        ]);

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ])->loadView('pdf.document', $data);




        // direct download pdf
        // return response()->stream(function () use ($pdf) {
        //     echo $pdf->output();
        // }, 'document.pdf');

        // Stream
        // return response()->streamDownload(
        //     fn() => print($pdf->output()),
        //     'document.pdf',
        //     ['Content-Type' => 'application/pdf']
        // );

        // stream 
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->stream();
            },
            'document.pdf'
        );
    }


    public function render()
    {
        return view('livewire.pembelian.permintaan.report');
    }
}
