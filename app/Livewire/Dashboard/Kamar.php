<?php

namespace App\Livewire\Dashboard;

use Throwable;
use GuzzleHttp\Client;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Layout('components.layouts.dashboard')]
#[Lazy]
#[Isolate]
class Kamar extends Component
{
    use Interactions;

    public $kapasitas = [];
    public $loading = FALSE;

    function mount(\App\Services\BpjsService $bpjsService)
    {
        $this->getData($bpjsService);
    }

    function getData(\App\Services\BpjsService $bpjsService)
    {
        $this->loading = true;

        try {
            // Optional: simulate network delay like before
            // sleep(3);

            $this->kapasitas = $bpjsService->getWards();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        } finally {
            $this->loading = false;
        }
    }


    function placeholder()
    {
        return view('components.skeleton');
    }


    public function render()
    {
        return view('livewire.dashboard.kamar',);
    }
}
