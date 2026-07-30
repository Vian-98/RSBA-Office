<?php

namespace App\Traits;

use App\Models\Gudang\OpnameStok;
use TallStackUi\Traits\Interactions;

trait BlocksTransactionDuringOpname
{
    use Interactions;

    public function blockIfOpnameActive()
    {
        if (OpnameStok::where('status', 'process')->orWhere('status', 'investigating')->exists()) {
            // dd('opname!');
            // $this->dispatch('toast', [
            //     'type' => 'error',
            //     'message' => 'Tidak dapat melakukan transaksi karena sedang berlangsung Stock Opname.'
            // ]);

            // $this->toast()
            //     ->error('Whoops!!', 'Tidak dapat melakukan transaksi karena sedang berlangsung Stock Opname.')
            //     ->send();

            // Reset form kalau perlu
            // $this->resetExcept(['someDataThatShouldStay']);
            // return view('components.opname-block');

            // Hentikan proses (misalnya: jangan simpan)
            return false;
        }
        return true;
    }
}
