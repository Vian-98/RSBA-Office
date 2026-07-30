<?php

namespace App\Livewire\Hutang\Bayar;

use App\Models\Gudang\Pembelian;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Add extends Component
{
    public ?Pembelian $pembelian;

    public $tanggal, $nominal;
    public ?array $lampiran = [];
    public bool $status = false;

    public function mount($id)
    {
        $this->pembelian = Pembelian::findOrFail($id);
        $this->status = $this->pembelian->status == 'selesai' ? true : false;
    }

    public function boot()
    {
        $this->tanggal = date("Y-m-d");
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'nominal' => 'required',
            'lampiran' => 'required'
        ];
    }

    public function submit()
    {
        $this->validate();
        dd('hai');
    }

    public function render()
    {
        return view('livewire.hutang.bayar.add');
    }
}
