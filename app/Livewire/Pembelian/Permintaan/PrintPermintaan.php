<?php

namespace App\Livewire\Pembelian\Permintaan;

use Livewire\Component;
use Milon\Barcode\DNS2D;
use Livewire\Attributes\Computed;
use App\Models\SignatureLogs;

class PrintPermintaan extends Component
{
    public $pembelianRequest;
    public $pembelianRequestDetails;

    public function mount(
        $pembelianRequest,
        $pembelianRequestDetails
    ) {
        $this->pembelianRequest = $pembelianRequest;
        $this->pembelianRequestDetails = $pembelianRequestDetails;
    }

    #[Computed]
    public function getSignature()
    {
        $key = SignatureLogs::where(
            [
                'sign_type' => 'permintaan_beli_approval',
                'sign_id' => $this->pembelianRequest->id
            ]
        )->get();

        $barcode = new DNS2D();
        $sign = "http://172.17.150.160/verify/req_pengadaan/{$key[0]['data_hash']}";

        return [
            'qrcode' => $barcode->getBarcodePNG($sign, 'QRCODE'),
            'sign_by' => $key[0]['user_signer']
        ];
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.print-permintaan');
    }
}
