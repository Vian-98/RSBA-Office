<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Tanda Tangan Digital')]
class Index extends Component
{
    use Interactions;

    public string $activeTab = 'create'; // 'create', 'my_submissions', 'pending_approvals'
    public ?int $revisionDocId = null;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('init-revision')]
    public function handleInitRevision(int $rejectedDocId): void
    {
        $this->revisionDocId = $rejectedDocId;
        $this->activeTab = 'create';
    }

    #[On('switch-tab')]
    public function handleSwitchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('document-signed')]
    public function handleDocumentSigned($docstoreKey = null, $synced = true)
    {
        $this->revisionDocId = null;
        $msg = $synced 
            ? 'Dokumen PDF berhasil di-sign & terkirim ke Docstore dengan ID: ' . $docstoreKey 
            : 'Dokumen PDF berhasil di-sign secara lokal, namun belum tersinkron ke Docstore.';

        session()->flash($synced ? 'success' : 'warning', $msg);

        if ($synced) {
            $this->toast()->success('Tanda Tangan Berhasil', $msg)->send();
        } else {
            $this->toast()->warning('Tanda Tangan Lokal', $msg)->send();
        }

        $this->activeTab = 'my_submissions';
    }

    public function render()
    {
        return view('livewire.kepegawaian.digital-signature.index')
            ->layout('layouts.app', ['title' => 'Tanda Tangan Digital']);
    }
}
