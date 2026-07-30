<?php

namespace App\Livewire\Pembelian\Permintaan;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Models\Gudang\PembelianRequest;
use App\Models\Gudang\PembelianRequestDetails;
use App\Services\DigitalSignatureService;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Approval extends Component
{
    use Interactions;

    public $PembelianReq;

    public $pembelian_request_details;

    public ?int $pembelianReqId;

    public ?array $approvedItems = [];

    public $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui', 'color' => 'indigo'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'red']
    ];

    public ?string $status = 'approved', $keterangan = null;
    public ?string $password;

    public function rules(): array
    {
        return [
            'password' => 'required',
            'status' => 'required|string',
            'keterangan' => $this->status === 'rejected' ? 'required|string' : 'nullable|string'
        ];
    }

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount($beliReqIdSelected)
    {
        $this->pembelianReqId = $beliReqIdSelected;

        $this->PembelianReq = $this->getPembelianRequest();

        $this->pembelian_request_details = $this->getPembelianRequestDetails();
    }


    #[Computed]
    public function getPembelianRequest(): PembelianRequest
    {
        return PembelianRequest::findOrFail($this->pembelianReqId);
    }

    #[Computed]
    public function getPembelianRequestDetails()
    {
        return PembelianRequestDetails::with(
            [
                'barang',
                'barang.kategori',
                'barang.satuan'
            ]
        )
            ->where('pembelian_req_id', $this->pembelianReqId)
            ->get();
    }


    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {

            // mapping data approved dengan result nama_barang
            $itemApproved = $this->getApprovedItemsWithName();

            // Data untuk ditandatangani
            $dataToSign = [
                'title' => "Approval Pengajuan {$this->pembelianReqId}, Note : {$this->getPembelianRequest()->note} ",
                'status' => $this->status,
                'ket_reject' => $this->keterangan,
                'items_disetujui' => $itemApproved,
                'verify_by' => auth()->user()->karyawan->nama
            ];

            // Tanda tangani
            $signature =  $this->digitalSignatureService->signData(
                user: auth()->user(),
                data: $dataToSign,
                password: $this->password,
                type: 'permintaan_beli_approval',
                id: $this->pembelianReqId
            );

            // Jika error
            if (!$signature['status']) {
                $this->toast()
                    ->error('Proses tanda tangan tidak berhasil.', "<i>{$signature['message']}</i>")
                    ->send();
                return;
            }

            //
            $PembelianReq = PembelianRequest::findOrFail($this->pembelianReqId);

            // Update pembelian data
            $PembelianReq->update([
                'status' => $this->status,
                'ket_reject' => $this->keterangan,
                'user_verify_id' => auth()->id(),
            ]);

            // each updated index details
            /** @var \App\Models\PembelianRequestDetails $item */
            foreach ($this->pembelian_request_details as $item) {
                $approvedData = $itemApproved[$item->id] ?? null;

                $approvedQty = $approvedData['jml_disetujui'] ?? 0;
                $approvedKet = $approvedData['keterangan'] ?? null;

                $item->update([
                    'jml_disetujui' => $approvedQty,
                    'keterangan' => $approvedKet
                ]);
            }

            DB::commit();
            $this->dispatch('submit-approval-beli-request');
            $this->dispatch('close-modal', id: 'modal-approval-pengajuan-pembelian');

            $this->toast()
                ->success('Berhasil', 'Pengajuan berhasil diverifikasi.')
                ->send();
        } catch (Throwable $th) {
            DB::rollback();
            $this->toast()
                ->error('Terjadi Kesalahan', "<i>{$th->getMessage()}</i> <br> Silahkan coba lagi.")
                ->send();
        }
    }


    public function getApprovedItemsWithName(): array
    {
        $result = [];

        foreach ($this->pembelian_request_details as $item) {
            $approvedData = $this->approvedItems[$item->id] ?? [];

            $result[$item->id] = [
                'nama_barang' => $item->barang->nama,
                'jml_disetujui' => $approvedData['jml_disetujui'] ?? 0,
                'keterangan' => $approvedData['keterangan'] ?? null
            ];
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.approval');
    }
}
