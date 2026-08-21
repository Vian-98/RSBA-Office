<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DigitalSignatureDocument;
use App\Models\DigitalSignatureApproval;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\Hash;

use TallStackUi\Traits\Interactions;

class PendingApprovalsTable extends Component
{
    use WithPagination, Interactions;

    public string $search = '';
    public ?DigitalSignatureDocument $selectedDocument = null;
    public ?DigitalSignatureApproval $selectedApproval = null;
    
    public bool $showSignModal = false;
    public bool $showRejectModal = false;
    
    public string $accountPassword = '';
    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openSignModal(int $documentId): void
    {
        $userId = auth()->id();
        $doc = DigitalSignatureDocument::with(['approvals.user', 'user'])->find($documentId);

        if (!$doc || !$doc->isPendingForUser($userId)) {
            $this->toast()->error('Akses Ditolak', 'Dokumen tidak dalam giliran persetujuan Anda.')->send();
            return;
        }

        $this->selectedDocument = $doc;
        $this->selectedApproval = $doc->approvals()->where('user_id', $userId)->first();
        $this->accountPassword = '';
        $this->showSignModal = true;
    }

    public function approveDocument(DocstoreSyncService $docstoreSyncService): void
    {
        $user = auth()->user();

        if (empty($this->accountPassword)) {
            $this->toast()->error('Password Wajib Diisi', 'Masukkan password akun Anda untuk verifikasi penandatanganan.')->send();
            return;
        }

        if (!Hash::check($this->accountPassword, $user->password)) {
            $this->toast()->error('Password Salah', 'Password akun yang Anda masukkan tidak valid.')->send();
            return;
        }

        if (!$this->selectedDocument || !$this->selectedApproval) {
            return;
        }

        $sigHash = hash('sha256', $user->id . $this->selectedDocument->id . time());

        // Update current step approval
        $this->selectedApproval->update([
            'status' => 'approved',
            'signed_at' => now(),
            'signature_hash' => $sigHash,
        ]);

        // Check if all approval steps are now completed
        $remainingCount = $this->selectedDocument->approvals()->where('status', '!=', 'approved')->count();

        $signatureData = [
            'signature' => 'SIG_' . $sigHash,
            'original_data' => $this->selectedDocument->byte_counter_hash,
            'public_key' => 'RSA_PUB_KEY_' . $user->id,
        ];

        if ($remainingCount === 0) {
            // All tiers approved! Mark document as fully approved
            $this->selectedDocument->update(['status' => 'approved']);
            $docstoreSyncService->syncDigitalSignatureDoc($this->selectedDocument, '', $signatureData);
            $this->toast()->success('Dokumen Disetujui Sepenuhnya', 'Seluruh tingkat penandatangan telah menyetujui dokumen ini.')->send();
        } else {
            $docstoreSyncService->syncDigitalSignatureDoc($this->selectedDocument, '', $signatureData);
            $this->toast()->success('Persetujuan Berhasil', 'Tingkat persetujuan Anda berhasil dicatat. Dokumen berlanjut ke penandatangan berikutnya.')->send();
        }

        $this->showSignModal = false;
        $this->accountPassword = '';
    }

    public function openRejectModal(int $documentId): void
    {
        $userId = auth()->id();
        $doc = DigitalSignatureDocument::with(['approvals.user', 'user'])->find($documentId);

        if (!$doc || !$doc->isPendingForUser($userId)) {
            $this->toast()->error('Akses Ditolak', 'Dokumen tidak dalam giliran persetujuan Anda.')->send();
            return;
        }

        $this->selectedDocument = $doc;
        $this->selectedApproval = $doc->approvals()->where('user_id', $userId)->first();
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function rejectDocument(DocstoreSyncService $docstoreSyncService): void
    {
        if (empty(trim($this->rejectionReason))) {
            $this->toast()->error('Catatan Wajib Diisi', 'Anda wajib mengisi alasan/feedback penolakan pengajuan dokumen.')->send();
            return;
        }

        if (!$this->selectedDocument || !$this->selectedApproval) {
            return;
        }

        $user = auth()->user();

        // 1. Update approval step to rejected
        $this->selectedApproval->update([
            'status' => 'rejected',
            'rejection_reason' => trim($this->rejectionReason),
            'signed_at' => now(),
        ]);

        // 2. Update overall document status to rejected
        $this->selectedDocument->update([
            'status' => 'rejected',
        ]);

        // 3. Sync rejection status & feedback to docstore
        $docstoreSyncService->syncDigitalSignatureDoc($this->selectedDocument, '', [
            'signature' => 'REJECTED_BY_' . $user->id,
            'original_data' => trim($this->rejectionReason),
            'public_key' => 'REJECTED',
        ]);

        $this->toast()->warning('Pengajuan Ditolak', 'Dokumen berhasil ditolak dan catatan feedback telah dikirimkan ke pengirim.')->send();

        $this->showRejectModal = false;
        $this->rejectionReason = '';
    }

    public function render()
    {
        $userId = auth()->id();

        // Get documents where user is an active reviewer
        $allDocs = DigitalSignatureDocument::with(['approvals.user', 'user'])
            ->where('status', '!=', 'rejected')
            ->whereHas('approvals', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'pending');
            })
            ->latest()
            ->get();

        // Filter to only those pending for this user's turn
        $pendingDocs = $allDocs->filter(function ($doc) use ($userId) {
            return $doc->isPendingForUser($userId);
        });

        return view('livewire.kepegawaian.digital-signature.partials.pending-approvals-table', [
            'pendingDocs' => $pendingDocs,
        ]);
    }
}
