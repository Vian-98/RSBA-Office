<?php

namespace App\Livewire\Pembelian;

use App\Models\Gudang\PembelianRequest;
use App\Traits\AuthorizesFromRoute;
use App\Traits\BlocksTransactionDuringOpname;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;

#[Title('Pembelian')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;
    use WithPagination;
    use BlocksTransactionDuringOpname;

    public $state;

    public $tab = 'Permintaan';
    public bool $stats = false;


    #[Computed]
    public function getRequestPembelianProperty()
    {
        return PembelianRequest::whereNotIn('status', ['completed', 'rejected'])
            ->whereHas('details', function ($query) {
                $query->whereNull('pembelian_id')
                    ->where(function ($q) {
                        $q->whereHas('request', function ($sub) {
                            $sub->where('status', '!=', 'approved');
                        })
                        ->orWhere('jml_disetujui', '>', 0);
                    });
            })
            ->count();
    }

    public function render()
    {

        if (!$this->blockIfOpnameActive()) {
            return view('components.opname-block');
        }

        $this->authorizeFromRoute();
        return view('livewire.pembelian.index');
    }
}
