<?php

namespace App\Livewire\Surat\Disposisi;

use App\Services\SuratDisposisiService;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class ModalSettingPattern extends Component
{
    use Interactions;

    public bool $modalSettingPattern = false;
    public string $patternInput = '';

    #[On('open-setting-pattern')]
    public function openModal(): void
    {
        $service = app(SuratDisposisiService::class);
        $this->patternInput = $service->getNoAgendaPattern();
        $this->modalSettingPattern = true;
    }

    public function savePatternSetting(SuratDisposisiService $service): void
    {
        $this->validate([
            'patternInput' => 'required|string|max:100',
        ]);

        $service->saveNoAgendaPattern($this->patternInput);
        $this->modalSettingPattern = false;
        $this->toast()->success('Berhasil', 'Format No. Agenda berhasil diperbarui.')->send();
        $this->dispatch('pattern-updated');
    }

    public function render()
    {
        return view('livewire.surat.disposisi.modal-setting-pattern');
    }
}
