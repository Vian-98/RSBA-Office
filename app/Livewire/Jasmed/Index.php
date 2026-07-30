<?php

namespace App\Livewire\Jasmed;

use App\Traits\AuthorizesFromRoute;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Jasmed')]
class Index extends Component
{
    use AuthorizesFromRoute;

    public $content;

    function navigateTo($route)
    {
        $this->content = $route;
    }

    public function render()
    {
        // $this->authorize('view-jasmed');
        $this->authorizeFromRoute();
        return view('livewire.jasmed.index');
    }
}
