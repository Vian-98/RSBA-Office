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

    public string $subTab = 'pending'; // 'pending' (Perlu Tindakan Saya) or 'history' (History / Semua Assign Ke Saya)
    public string $search = '';
    
    public ?DigitalSignatureDocument $selectedDocument = null;
    public ?DigitalSignatureApproval $selectedApproval = null;
    
    public bool $showSignModal = false;
    public bool $showRejectModal = false;
    public bool $showDetailModal = false;
    
    public string $accountPassword = '';
    public string $rejectionReason = '';

    public function setSubTab(string $tab): void
    {
        $this->subTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewDetail(int $documentId): void
    {
        $doc = DigitalSignatureDocument::with(['approvals.user.karyawan.jabatan', 'user'])->find($documentId);
        if ($doc) {
            $this->selectedDocument = $doc;
            $this->showDetailModal = true;
        }
    }

    public function openSignModal(int $documentId): void
    {
        $userId = auth()->id();
        $doc = DigitalSignatureDocument::with(['approvals.user', 'user'])->find($documentId);

        if (!$doc || !$doc->isPendingForUser($userId)) {
            $this->toast()->error('Akses Ditolak', 'Dokumen belum dalam giliran persetujuan Anda.')->send();
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
            $this->toast()->error('Akses Ditolak', 'Dokumen belum dalam giliran persetujuan Anda.')->send();
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

        // Base query: documents where current user is assigned as a signer
        $baseQuery = DigitalSignatureDocument::with(['approvals.user.karyawan.jabatan', 'user'])
            ->whereHas('approvals', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

        // Search filter (Title, Document Number, or Applicant Name via karyawan or email)
        if (!empty(trim($this->search))) {
            $searchTerm = trim($this->search);
            $baseQuery->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('document_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($qu) use ($searchTerm) {
                      $qu->where('email', 'like', "%{$searchTerm}%")
                        ->orWhereHas('karyawan', function ($qk) use ($searchTerm) {
                            $qk->where('nama', 'like', "%{$searchTerm}%");
                        });
                  });
            });
        }

        // Count pending action items (where it is currently this user's turn)
        $pendingActionCount = DigitalSignatureDocument::where('status', '!=', 'rejected')
            ->whereHas('approvals', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'pending');
            })
            ->get()
            ->filter(fn($doc) => $doc->isPendingForUser($userId))
            ->count();

        // Total count of all assigned documents to this user
        $totalAssignedCount = DigitalSignatureDocument::whereHas('approvals', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        if ($this->subTab === 'pending') {
            // Get pending documents where user is an active reviewer
            $allDocs = (clone $baseQuery)
                ->where('status', '!=', 'rejected')
                ->whereHas('approvals', function ($q) use ($userId) {
                    $q->where('user_id', $userId)->where('status', 'pending');
                })
                ->latest()
                ->get();

            // Filter to only those where it's currently this user's turn
            $documents = $allDocs->filter(fn($doc) => $doc->isPendingForUser($userId));
            $paginatedDocs = null;
        } else {
            // SubTab 'history' (History / Semua Assign Ke Saya: includes pending turn, waiting turn, approved, rejected)
            $documents = null;
            $paginatedDocs = (clone $baseQuery)->latest()->paginate(10);
        }

        return view('livewire.kepegawaian.digital-signature.partials.pending-approvals-table', [
            'documents'          => $documents,
            'paginatedDocs'      => $paginatedDocs,
            'pendingActionCount' => $pendingActionCount,
            'totalAssignedCount' => $totalAssignedCount,
        ]);
    }
}
