<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DigitalSignatureDocument;
use App\Models\DigitalSignatureApproval;
use TallStackUi\Traits\Interactions;

class MySubmissionsTable extends Component
{
    use WithPagination, Interactions;

    public string $search = '';
    public string $statusFilter = 'all';
    public ?DigitalSignatureDocument $selectedDocument = null;
    public bool $showDetailModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewDetail(int $documentId): void
    {
        $this->selectedDocument = DigitalSignatureDocument::with(['approvals.user.karyawan.jabatan', 'user'])->find($documentId);
        if ($this->selectedDocument) {
            $this->showDetailModal = true;
        }
    }

    public function render()
    {
        $userId = auth()->id();

        $query = DigitalSignatureDocument::with(['approvals.user', 'user'])
            ->mySubmissions($userId);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('document_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $documents = $query->latest()->paginate(10);

        return view('livewire.kepegawaian.digital-signature.partials.my-submissions-table', [
            'documents' => $documents,
        ]);
    }
}
