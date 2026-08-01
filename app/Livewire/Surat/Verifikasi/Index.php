<?php

namespace App\Livewire\Surat\Verifikasi;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Lazy]
#[Title('Verifikasi Surat')]
class Index extends Component
{
    public string $tab = 'sp3';

    public function render()
    {
        $layout = auth()->check() ? 'components.layouts.app' : 'components.layouts.dashboard';
        return view('livewire.surat.verifikasi.index')->layout($layout);
    }
}
