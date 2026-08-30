<?php

namespace App\Services\Docstore\Synchronizers;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratCuti;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\Sdm\Karyawan;
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
        $model->loadMissing(['approvals.karyawan.jabatan', 'karyawan.jabatan', 'jenis']);
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
        $karyawan  = $model->karyawan;
        $jenis     = $model->jenis;
        $statusDoc = $this->mapDocumentStatus($model);

        // Format dates nicely
        $tglMulai = $model->tgl_mulai ? \Carbon\Carbon::parse($model->tgl_mulai)->format('Y-m-d') : null;
        $tglAkhir = $model->tgl_akhir ? \Carbon\Carbon::parse($model->tgl_akhir)->format('Y-m-d') : null;
        $tglSurat = $model->tgl_surat ? \Carbon\Carbon::parse($model->tgl_surat)->format('Y-m-d') : null;

        // Parse tgl_cuti array to human readable format
        $tglCutiDisplay = $model->tgl_cuti;
        if (is_string($tglCutiDisplay)) {
            $decoded = json_decode($tglCutiDisplay, true);
            if (is_array($decoded)) {
                $tglCutiDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y'), $decoded));
            }
        } elseif (is_array($tglCutiDisplay)) {
            $tglCutiDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y'), $tglCutiDisplay));
        }

        $jabatanName = optional($karyawan)->jabatan_nama;
        if (!$jabatanName && $karyawan && $karyawan->relationLoaded('jabatan')) {
            $jabatanName = optional($karyawan->jabatan->first())->nama;
        }

        return [
            'document_id'     => $model->id,
            'document_number' => $model->no_surat,
            'document_type'   => 'cuti',
            'status'          => $statusDoc,
            'docstore_key'    => $model->docstore_key ?: null,
            'content'         => [
                'surat_cuti_id'      => $model->id,
                'no'                 => $model->no_surat,
                'no_surat'           => $model->no_surat,
                'karyawan_id'        => $model->karyawan_id,
                'karyawan_name'      => optional($karyawan)->nama ?? $model->user_name ?? 'Karyawan',
                'karyawan_nip'       => optional($karyawan)->nip ?? '-',
                'karyawan_jabatan'   => $jabatanName ?? '-',
                'karyawan_hp'        => optional($karyawan)->no_hp ?? optional($karyawan)->phone ?? '-',
                'tgl_surat'          => $tglSurat,
                'tgl_mulai'          => $tglMulai,
                'tgl_akhir'          => $tglAkhir,
                'lama_cuti'          => (int) $model->lama_cuti,
                'jenis_cuti_id'      => $model->urgensi_id ?? $model->jenis_cuti_id,
                'jenis_cuti'         => optional($jenis)->nama ?? 'Cuti',
                'urgensi'            => optional($jenis)->nama ?? 'Cuti',
                'keterangan'         => $model->keterangan ?? '-',
                'alamat'             => $model->alamat ?? '-',
                'tgl_cuti'           => $tglCutiDisplay,
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

        $model->loadMissing(['approvals.karyawan.jabatan']);

        foreach ($model->approvals as $approval) {
            $karyawanId = $approval->disetujui_oleh;
            $karyawan   = $approval->karyawan ?: ($karyawanId ? Karyawan::with('jabatan')->find($karyawanId) : null);
            $user       = $karyawanId ? User::where('karyawan_id', $karyawanId)->first() : null;
            $userId     = $user?->id;

            $sigLog = SignatureLogs::where('sign_id', $model->id)
                ->where(function ($q) {
                    $q->where('sign_type', 'cuti')
                      ->orWhere('sign_type', 'surat_cuti');
                })
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->latest('id')
                ->first();

            $cert = $userId ? SignatureCerts::where('user_id', $userId)->where('is_active', 1)->first() : null;

            $rawStatus = is_object($approval->status) ? $approval->status->value : (string) $approval->status;
            $statusLower = strtolower($rawStatus);
            $statusText = match ($statusLower) {
                'approved', 'disetujui' => 'SIGNED',
                'rejected', 'ditolak'   => 'REJECTED',
                'manual'                => 'SIGNED',
                default                 => 'PENDING',
            };

            $signerName = $karyawan?->full_nama ?? $karyawan?->nama ?? optional($user)->name ?? 'Pejabat Penyetuju';
            $signerRole = $karyawan?->jabatan_nama ?? optional(optional($karyawan)->jabatan?->first())->nama ?? 'Atasan Langsung';

            $signatures[] = [
                'signer_name'    => $signerName,
                'signer_role'    => $signerRole,
                'signer_order'   => (int) ($approval->order ?? 1),
                'status'         => $statusText,
                'signed_at'      => $approval->approved_at ? \Carbon\Carbon::parse($approval->approved_at)->toIso8601String() : null,
                'signature_hash' => $approval->signature_hash ?: ($sigLog->data_hash ?? null),
                'signature'      => $sigLog->signature ?? '',
                'signature_data' => $sigLog->signature ?? '',
                'original_data'  => $sigLog->data ?? ($sigLog->signature ?? ''),
                'public_key'     => optional($cert)->public_key ?? null,
                'is_manual'      => (bool) ($statusLower === 'manual' || !empty($approval->is_manual)),
                'manual_note'    => $approval->keterangan ?? null,
            ];
        }

        return $signatures;
    }

    protected function mapDocumentStatus(SuratCuti $model): string
    {
        $rawStatus = $model->status ?? '';
        $statusStr = is_object($rawStatus) && isset($rawStatus->value) ? $rawStatus->value : (string) $rawStatus;
        if (in_array(strtolower($statusStr), ['cancelled', 'dibatalkan', 'batal'])) {
            return 'cancelled';
        }

        $approvals = $model->approvals;
        if ($approvals->isEmpty()) {
            return 'pending';
        }

        $anyCancelled = $approvals->contains(function ($a) {
            $status = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($status), ['cancelled', 'dibatalkan', 'batal']);
        });
        if ($anyCancelled) {
            return 'cancelled';
        }

        $allApproved = $approvals->every(function ($a) {
            $status = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($status), ['approved', 'disetujui', 'manual']);
        });

        $anyRejected = $approvals->contains(function ($a) {
            $status = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($status), ['rejected', 'ditolak']);
        });

        if ($anyRejected) {
            return 'rejected';
        }

        if ($allApproved) {
            return $approvals->contains(fn($a) => (is_object($a->status) ? $a->status->value : (string)$a->status) === 'manual')
                ? 'manual'
                : 'approved';
        }

        return 'pending';
    }
}
