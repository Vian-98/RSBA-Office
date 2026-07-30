<?php

namespace App\Livewire\Akreditasi\Documents;

use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class Index extends Component
{
    public ?int $element_id;

    public $file_is;

    public function mount($elementId)
    {
        $this->element_id = $elementId;
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.index');
    }
}
