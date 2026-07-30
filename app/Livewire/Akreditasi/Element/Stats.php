<?php

namespace App\Livewire\Akreditasi\Element;

use Livewire\Component;
use App\Models\Akreditasi\AkreBabElement;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;

#[Lazy]
class Stats extends Component
{
    public ?int $babId;

    public function mount($babId)
    {
        $this->babId = $babId;
    }

    #[On('updated-nilai-element')]
    #[On('uploaded-files-element')]
    #[On('deleted-files_element')]
    public function refreshStats()
    {
        return $this->babs();
    }

    #[Computed]
    public function babs(): array
    {
        $subBabs = AkreBabElement::query()
            ->select('id')
            ->with([
                'elements' => function ($query) {
                    $query->select('id', 'akre_bab_id', 'target_nilai', 'nilai', 'tdd');
                },
                'elements.documents' => function ($query) {
                    $query->select('akre_element_documents.id', 'akre_element_documents.element_id');
                }
            ])
            ->withCount([
                'elements',
                'elements as elements_with_files_count' => function ($query) {
                    $query->whereHas('documents');
                },
            ])
            ->withSum('elements', 'tdd')
            ->withSum('elements', 'nilai')
            ->withSum('elements', 'target_nilai')
            ->where('id', $this->babId)
            ->get();

        // dd($subBabs);
        $tdd = 0;
        foreach ($subBabs as $sub) {
            $tdd += (int) $sub->tdd;

            $data = [
                'id' => $sub->id,
                'elements_count' => $sub->elements_count ?? 0,
                'elements_with_files_count' => $sub->elements_with_files_count ?? 0,
                'elements_tdd' => $sub->elements_sum_tdd ?? 0,
                'elements_nilai' => $sub->elements_sum_nilai ?? 0,
                'elements_target_nilai' => $sub->elements_sum_target_nilai ?? 0,
                'color_berkas' => $this->getColor(
                    stat: $sub->elements_with_files_count,
                    target: $sub->elements_count
                ),
                'color_nilai' => $this->getColor(
                    stat: $sub->elements_sum_nilai,
                    target: $sub->elements_sum_target_nilai
                )
            ];
        }
        return $data;
    }



    public function getColor($stat, $target)
    {
        $totalNilai = $stat ?? 0;
        $totalTarget = $target ?? 0;

        $persentase = $totalTarget > 0 ? round(($totalNilai / $totalTarget) * 100, 1) : 0;;
        if ($persentase < 50) {
            return 'red';
        } elseif ($persentase >= 50 && $persentase < 100) {
            return 'yellow';
        } else {
            return 'green';
        }
    }

    public function placeholder()
    {
        return <<<HTML
        <div class="flex animate-pulse flex-col gap-2 pt-2">
            <div class="relative h-2 w-full space-y-3 overflow-hidden rounded-md bg-neutral-300"></div> 
            <div class="relative h-2 w-full space-y-3 overflow-hidden rounded-md bg-neutral-300"></div> 
        </div>
        HTML;
    }


    public function render()
    {
        return view('livewire.akreditasi.element.stats');
    }
}
