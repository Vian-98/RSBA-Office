<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public $activeTab = 'list'; // 'list' or 'upload'

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('document-signed')]
    public function handleDocumentSigned($docstoreKey = null, $synced = true)
    {
        $this->activeTab = 'list';
    }

    public function render()
    {
        return view('livewire.kepegawaian.digital-signature.index')
            ->layout('layouts.app', ['title' => 'Tanda Tangan Digital']);
    }
}
