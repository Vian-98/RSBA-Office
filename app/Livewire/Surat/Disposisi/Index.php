<?php

namespace App\Livewire\Surat\Disposisi;

use App\Models\Surat\SuratDisposisi;
use App\Services\SuratDisposisiService;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Surat Disposisi Direktur')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    #[On('pattern-updated')]
    public function refreshPattern(): void
    {
        // Refresh component view when agenda pattern is updated
    }

    public function deleteDisposisi(int $id): void
    {
        $disposisi = SuratDisposisi::find($id);
        if ($disposisi) {
            $disposisi->delete();
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
