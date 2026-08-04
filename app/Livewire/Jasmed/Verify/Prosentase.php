<?php

namespace App\Livewire\Jasmed\Verify;

use App\Models\JmProsentase;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Prosentase extends Component
{
    #[Reactive]
    public ?JmProsentase $jmProsentase = null;

    #[Computed]
    public function headers(): array
    {
        return [
            ['index' => 'jasa_p',         'label' => 'Jasa Pelayanan',    'format' => 'number'],
            ['index' => 'klaim_min_rincian', 'label' => 'Klaim (-) Rincian',  'format' => 'number'],
            ['index' => 'jasa_pelayanan', 'label' => 'Jasa Dibagikan',    'format' => 'number'],
            ['index' => 'jasa_rs',        'label' => 'Jasa RS (10%)',      'format' => 'float'],
            ['index' => 'jasa_medis',     'label' => 'Jasa Medis',        'format' => 'float'],
            ['index' => 'jasa_operator',  'label' => 'Jasa Operator',     'format' => 'float'],
            ['index' => 'jasa_anastesi',  'label' => 'Jasa Anastesi',     'format' => 'float'],
            ['index' => 'jasa_penata',    'label' => 'Jasa Penata',       'format' => 'float'],
            ['index' => 'jasa_resus',     'label' => 'Jasa Resus',        'format' => 'float'],
            ['index' => 'jasa_pekerja',   'label' => 'Jasa Pekerja',      'format' => 'float'],
        ];
    }

    #[Computed]
    public function rows(): array
    {
        $jmProsentase = $this->jmProsentase;

        return [[
            'jasa_p' =>  $jmProsentase->jasa_p,
            'klaim_min_rincian' =>  $jmProsentase->klaim_min_rincian,
            'jasa_pelayanan' =>  $jmProsentase->jasa_pelayanan,
            'jasa_rs' =>  $jmProsentase->jasa_rs,
            'jasa_medis' =>  $jmProsentase->jasa_medis,
            'jasa_operator' =>  $jmProsentase->jasa_operator,
            'jasa_anastesi' =>  $jmProsentase->jasa_anastesi,
            'jasa_penata' =>  $jmProsentase->jasa_penata,
            'jasa_resus' =>  $jmProsentase->jasa_resus,
            'jasa_pekerja' =>  $jmProsentase->jasa_pekerja,
        ]];
    }

    public function render()
    {
        return view('livewire.jasmed.verify.prosentase');
    }
}
