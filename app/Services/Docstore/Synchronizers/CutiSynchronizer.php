<?php

namespace App\Services\Docstore\Synchronizers;

use App\Models\Surat\SuratCuti;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use App\Services\Docstore\Contracts\DocumentSynchronizerInterface;
use App\Services\Docstore\DocstoreClient;
use Illuminate\Database\Eloquent\Model;

class CutiSynchronizer implements DocumentSynchronizerInterface
{
    public function __construct(protected DocstoreClient $client)
    {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof SuratCuti;
    }

    public function sync(Model $model): bool
    {
        if (!$this->supports($model)) {
            return false;
        }

        /** @var SuratCuti $model */
        $model->loadMissing(['approvals', 'karyawan', 'jenis']);
        $payload = $this->buildPayload($model);

        $result = $this->client->postDocument($payload);

        if ($result && ($result['success'] ?? false)) {
            $docstoreKey = $result['docstore_key'] ?? ($result['data']['docstore_key'] ?? null);
            $model->updateQuietly([
                'docstore_key'       => $docstoreKey,
                'docstore_synced_at' => now(),
            ]);
            if (!empty($docstoreKey)) {
                $this->client->invalidateCache($docstoreKey);
            }
            return true;
        }

        return false;
    }

    public function buildPayload(Model $model): array
    {
        /** @var SuratCuti $model */
        $karyawan = $model->karyawan;
        $jenis    = $model->jenis;
        $statusDoc = $this->mapDocumentStatus($model);

        return [
            'document_id'     => $model->id,
            'document_number' => $model->no_surat,
            'document_type'   => 'cuti',
            'status'          => $statusDoc,
            'docstore_key'    => $model->docstore_key ?: null,
            'content'         => [
                'surat_cuti_id'      => $model->id,
                'karyawan_id'        => $model->karyawan_id,
                'karyawan_name'      => optional($karyawan)->nama ?? $model->user_name,
                'karyawan_nip'       => optional($karyawan)->nip,
                'karyawan_jabatan'   => optional($karyawan)->jabatan_nama,
                'karyawan_hp'        => optional($karyawan)->no_hp ?? optional($karyawan)->phone,
                'no_surat'           => $model->no_surat,
                'tgl_surat'          => $model->tgl_surat ? $model->tgl_surat->format('Y-m-d') : null,
                'tgl_mulai'          => $model->tgl_mulai ? $model->tgl_mulai->format('Y-m-d') : null,
                'tgl_akhir'          => $model->tgl_akhir ? $model->tgl_akhir->format('Y-m-d') : null,
                'lama_cuti'          => $model->lama_cuti,
                'jenis_cuti_id'      => $model->jenis_cuti_id,
                'jenis_cuti'         => optional($jenis)->nama,
                'urgensi'            => $model->urgensi,
                'keterangan'         => $model->keterangan,
                'alamat'             => $model->alamat,
                'tgl_cuti'           => $model->tgl_cuti,
                'is_emergency'       => (bool) $model->is_emergency,
                'sisa_cuti_tahunan'  => $model->sisa_cuti_tahunan,
            ],
            'signatures' => $this->buildSignatures($model),
        ];
    }

    public function buildSignatures(Model $model): array
    {
        /** @var SuratCuti $model */
        $signatures = [];

        foreach ($model->approvals as $approval) {
            $user = User::find($approval->user_id);
            $sigLog = SignatureLogs::where('user_id', $approval->user_id)
                ->where('sign_id', $model->id)
                ->where(function ($q) {
                    $q->where('sign_type', 'cuti')
                      ->orWhere('sign_type', 'surat_cuti');
                })
                ->latest('id')
                ->first();

            $cert = SignatureCerts::where('user_id', $approval->user_id)
                ->where('is_active', 1)
                ->first();

            $statusText = 'PENDING';
            if ($approval->status === 'approved' || $approval->status === 'manual') {
                $statusText = 'SIGNED';
            } elseif ($approval->status === 'rejected') {
                $statusText = 'REJECTED';
            }

            $signatures[] = [
                'signer_name'    => $approval->user_name ?? optional($user)->name ?? 'Pejabat Approval',
                'signer_role'    => $approval->jabatan ?? $approval->role ?? 'Pejabat Penyetuju',
                'signer_order'   => (int) ($approval->order ?? 1),
                'status'         => $statusText,
                'signed_at'      => $approval->approved_at ? $approval->approved_at->toIso8601String() : null,
                'signature_hash' => $approval->qr_verification_hash ?: ($sigLog->data_hash ?? null),
                'signature'      => $sigLog->signature ?? '',
                'signature_data' => $sigLog->signature ?? '',
                'original_data'  => $sigLog->data ?? ($sigLog->signature ?? ''),
                'public_key'     => optional($cert)->public_key ?? null,
                'is_manual'      => (bool) ($approval->is_manual ?? ($approval->status === 'manual')),
                'manual_note'    => $approval->catatan ?? null,
            ];
        }

        return $signatures;
    }

    protected function mapDocumentStatus(SuratCuti $model): string
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
