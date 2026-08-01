<?php

namespace App\Livewire\Forms\Pembelian;

use Throwable;
use Exception;
use App\Models\Gudang\PembelianRequest;
use App\Models\Gudang\PembelianRequestDetails;
use Livewire\Form;
use Illuminate\Support\Facades\DB;

class PermintaanForm extends Form
{
    public ?string $keterangan;
    public ?string $priority = 'normal';
    public ?string $status = 'pending';
    public ?string $ket_reject;
    public ?array $lampirans = [];

    public ?array $items = [];

    public ?int $lastRequestId;

    public function rules(): array
    {
        return [
            'priority' => ['required'],
            'keterangan' => ['required', 'string', 'min:10'],
            'status' => ['required'],
            'ket_reject' => [$this->status === 'rejected' ? 'required' : 'nullable'],
            'items' => ['required', 'array', 'min:1']
        ];
    }

    public function messages(): array
    {
        return [
            'keterangan.required' => 'Isi Keterangan dengan jelas,ini sebagai dasar alasan pengajuan anda.',
            'keterangan.min' => 'Keterangan sepertinya terlalu pendek, harap isi dengan jelas dasar pengajuan anda.',
            'items.required' => 'Minimal ada satu item untuk diajukan.',
            'items.min' => 'Minimal ada satu item untuk diajukan.'
        ];
    }

    public function simpan()
    {
        DB::beginTransaction();
        try {

            // Check if there are any uploaded files
            $lampirans = collect($this->lampirans)->map(function ($file) {
                // Store each file and return the path
                return $file->store('pembelianReqs/lampiran', 'public');
            })->toArray();

            // dd($this->keterangan, $this->items);
            $pembelianReq = PembelianRequest::create([
                'user_req_id' => auth()->id(),
                'note' => $this->keterangan,
                'priority' => $this->priority ?? 'normal',
                'status' => 'pending',
                'lampirans' => $lampirans
            ]);

            $this->createDetail(
                pembelianReq: $pembelianReq,
                items: $this->items
            );

            DB::commit();

            $this->lastRequestId = $pembelianReq->id; //return id
        } catch (Throwable $e) {
            DB::rollback();

            throw new Exception($e->getMessage());
        }
    }

    private function createDetail($pembelianReq, $items)
    {
        if (empty($items)) {
            throw new Exception("Tidak ada detail item pengajuan.");
        }

        foreach ($items as $item) {
            PembelianRequestDetails::create([
                'pembelian_req_id' => $pembelianReq->id,
                'barang_id' => $item['id'], //barang_id
                'jml_req' => $item['jumlah'],
                'harga_est' => $item['harga_est'] ?? 0,
                'specs' => $item['specs'] ?? null
            ]);
        }
    }


    // FIXING: Verify validation to creaate signature_hash
    public function verify(PembelianRequest $pembelianReq)
    {
        $pembelianReq->user_verify_id = auth()->id();
        $pembelianReq->status = $this->status;
        $pembelianReq->ket_reject = $this->ket_reject;
        $pembelianReq->save();

        // update Detail 
        $pembelianReq->details();
    }
}
