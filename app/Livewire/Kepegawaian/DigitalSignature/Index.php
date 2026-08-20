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

    #[On('document-signed')]
    public function handleDocumentSigned($docstoreKey = null, $synced = true)
    {
        $msg = $synced 
            ? 'Dokumen PDF berhasil di-sign & terkirim ke Docstore dengan ID: ' . $docstoreKey 
            : 'Dokumen PDF berhasil di-sign secara lokal, namun belum tersinkron ke Docstore.';

        session()->flash($synced ? 'success' : 'warning', $msg);

        if ($synced) {
            $this->toast()->success('Tanda Tangan Berhasil', $msg)->send();
        } else {
            $this->toast()->warning('Tanda Tangan Lokal', $msg)->send();
        }
    }

    public function render()
    {
        return view('livewire.kepegawaian.digital-signature.index')
            ->layout('layouts.app', ['title' => 'Tanda Tangan Digital']);
    }
}
