<?php

namespace App\Services\Docstore\Synchronizers;

use App\Models\Surat\SuratSp3;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use App\Services\Docstore\Contracts\DocumentSynchronizerInterface;
use App\Services\Docstore\DocstoreClient;
use Illuminate\Database\Eloquent\Model;

class Sp3Synchronizer implements DocumentSynchronizerInterface
{
    public function __construct(protected DocstoreClient $client)
    {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof SuratSp3;
    }

    public function sync(Model $model): bool
    {
        if (!$this->supports($model)) {
            return false;
        }

        /** @var SuratSp3 $model */
        $model->loadMissing(['approvals', 'details', 'jabatans']);
        $payload = $this->buildPayload($model);

        $result = $this->client->postDocument($payload);

        if ($result && ($result['success'] ?? false)) {
            $docstoreKey = $result['docstore_key'] ?? ($result['data']['docstore_key'] ?? null);
            $model->updateQuietly([
                'docstore_key'       => $docstoreKey,
                'docstore_synced_at' => now(),
                'docstore_status'    => 'synced',
            ]);
            $this->client->invalidateCache($docstoreKey);
            return true;
        }

        $model->updateQuietly([
            'docstore_status' => 'failed',
        ]);
        return false;
    }

    public function buildPayload(Model $model): array
    {
        /** @var SuratSp3 $model */
        $details = $model->details->map(fn($d) => [
            'id'         => $d->id,
            'keterangan' => $d->keterangan,
            'nominal'    => (float) $d->nominal,
        ])->toArray();

        $statusDoc = $this->mapDocumentStatus($model);

        return [
            'document_number' => $model->no,
            'document_type'   => 'sp3',
            'status'          => $statusDoc,
            'docstore_key'    => $model->docstore_key ?: null,
            'content'         => [
                'sp3_id'     => $model->id,
                'no'         => $model->no,
                'tahun'      => $model->tahun,
                'tgl'        => $model->tgl ? $model->tgl->format('Y-m-d') : null,
                'rekanan'    => $model->rekanan,
                'bayar'      => $model->bayar,
                'keterangan' => $model->keterangan,
                'items'      => $details,
                'total'      => array_sum(array_column($details, 'nominal')),
            ],
            'signatures' => $this->buildSignatures($model),
        ];
    }

    public function buildSignatures(Model $model): array
    {
        /** @var SuratSp3 $model */
        $signatures = [];

        foreach ($model->approvals as $approval) {
            $user = User::find($approval->user_id);
            $sigLog = SignatureLogs::where('user_id', $approval->user_id)
                ->where('reference_id', $model->id)
                ->where('reference_type', 'sp3')
                ->latest()
                ->first();

            $cert = SignatureCerts::where('user_id', $approval->user_id)
                ->where('status', 'active')
                ->first();

            $statusText = 'PENDING';
            if ($approval->status === 'approved' || $approval->status === 'manual') {
                $statusText = 'SIGNED';
            } elseif ($approval->status === 'rejected') {
                $statusText = 'REJECTED';
            }

            $signatures[] = [
                'signer_name'    => $approval->user_name ?? optional($user)->name ?? 'Pejabat Approval',
                'signer_role'    => $approval->jabatan ?? 'Pejabat Penyetuju',
                'signer_order'   => (int) ($approval->order ?? 1),
                'status'         => $statusText,
                'signed_at'      => $approval->approved_at ? $approval->approved_at->toIso8601String() : null,
                'signature_hash' => $approval->qr_verification_hash ?: ($sigLog->signature_hash ?? null),
                'signature_data' => $sigLog->signature_data ?? null,
                'original_data'  => $sigLog->original_data ?? null,
                'public_key'     => optional($cert)->public_key ?? null,
                'is_manual'      => (bool) ($approval->is_manual ?? ($approval->status === 'manual')),
                'manual_note'    => $approval->catatan ?? null,
            ];
        }

        return $signatures;
    }

    protected function mapDocumentStatus(SuratSp3 $model): string
    {
        $approvals = $model->approvals;
        if ($approvals->isEmpty()) {
            return 'pending';
        }

        $allApproved = $approvals->every(fn($a) => in_array($a->status, ['approved', 'manual']));
        $anyRejected = $approvals->contains(fn($a) => $a->status === 'rejected');

        if ($anyRejected) {
            return 'rejected';
        }

        if ($allApproved) {
            return $approvals->contains(fn($a) => $a->status === 'manual' || !empty($a->is_manual))
                ? 'manual'
                : 'approved';
        }

        return 'pending';
    }
}
