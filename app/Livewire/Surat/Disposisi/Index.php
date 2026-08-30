<?php

namespace App\Livewire\Surat\Disposisi;

use App\Models\Surat\SuratDisposisi;
use App\Services\SuratDisposisiService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use TallStackUi\Traits\Interactions;

#[Title('Surat Disposisi Direktur')]
class Index extends Component
{
    use WithPagination, Interactions;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    public bool $modalSettingPattern = false;
    public string $patternInput = '';

    public bool $modalPreview = false;
    public ?SuratDisposisi $selectedDisposisi = null;

    public function mount(SuratDisposisiService $service): void
    {
        $this->patternInput = $service->getNoAgendaPattern();
    }

    public function showPreview(int $id): void
    {
        $this->selectedDisposisi = SuratDisposisi::with(['details', 'direktur'])->find($id);
        if ($this->selectedDisposisi) {
            $this->modalPreview = true;
        }
    }

    public function openModalSetting(): void
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
    }

    public function deleteDisposisi(int $id): void
    {
        $disposisi = SuratDisposisi::find($id);
        if ($disposisi) {
            $disposisi->delete();
            $this->toast()->success('Berhasil', 'Data disposisi berhasil dihapus.')->send();
        }
    }

    public function render(SuratDisposisiService $service)
    {
        $query = SuratDisposisi::with(['details', 'creator'])
            ->orderBy('created_at', 'desc');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('no_agenda', 'like', '%' . $this->search . '%')
                    ->orWhere('no_surat', 'like', '%' . $this->search . '%')
                    ->orWhere('perihal', 'like', '%' . $this->search . '%')
                    ->orWhere('asal_surat', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $disposisiList = $query->paginate(10);
        $nextNoAgendaPreview = $service->getNextNoAgenda();

        return view('livewire.surat.disposisi.index', [
            'disposisiList' => $disposisiList,
            'nextNoAgendaPreview' => $nextNoAgendaPreview,
        ]);
    }
}
