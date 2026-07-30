<?php

namespace App\Livewire\Piutang;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class AddInvoice extends Component
{
    public $listDetailInvoice = [];

    public array $bankOptions = [
        ['value' => 'bni', 'label' => 'BNI'],
        ['value' => 'bri', 'label' => 'BRI'],
        ['value' => 'bca', 'label' => 'BCA'],
    ];

    public $bank;
    public string $foot_note = 'Harap konfirmasi pembayaran ke Tiara Januar Riska (0821 xxx xxx)';

    public function render()
    {
        return view('livewire.piutang.add-invoice');
    }

    public function submit() {}
}
