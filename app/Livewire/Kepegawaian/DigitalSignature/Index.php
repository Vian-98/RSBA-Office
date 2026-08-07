<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

class Index extends Component
{
    use Interactions;

    public $activeTab = 'list'; // 'list' or 'upload'

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('document-signed')]
    public function handleDocumentSigned($docstoreKey = null, $synced = true)
    {
        $this->activeTab = 'list';

        $msg = $synced 
            ? 'Dokumen PDF berhasil di-sign & terkirim ke Docstore Vault dengan ID: ' . $docstoreKey 
            : 'Dokumen PDF berhasil di-sign, namun gagal sinkronisasi ke Docstore Vault.';

        session()->flash($synced ? 'success' : 'warning', $msg);
    }

    public function render()
    {
        return view('livewire.kepegawaian.digital-signature.index')
            ->layout('layouts.app', ['title' => 'Tanda Tangan Digital']);
    }
}

