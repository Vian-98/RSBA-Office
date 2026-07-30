<?php

namespace App\Livewire\Akreditasi\Documents;

use App\Models\Akreditasi\AkreDocuments;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class View extends Component
{
    public ?AkreDocuments $file;

    public function mount($docSelectedId)
    {
        // $this->file = AkreElementDocuments::with('document')->where('id', $docSelectedId)->first();
        $this->file = AkreDocuments::findOrFail($docSelectedId);
        // dd($this->file, $docSelectedId);
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.view');
    }
}
