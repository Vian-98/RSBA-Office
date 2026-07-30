<?php

namespace App\Livewire\Akreditasi\Chapters;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use App\Models\Akreditasi\AkreBabElement;

#[Lazy]
class Stat extends Component
{
    private ?int $chapterId;


    public function mount($chapterId)
    {
        $this->chapterId = $chapterId;
    }

    #[Computed]
    public function getStatsBar()
    {
        $chapterStats = AkreBabElement::where('chapter_id', $this->chapterId)
            ->where('bab', 'sub')
            ->withCount([
                'elements', // total_ep
                'elements as total_ep_memiliki_file' => function ($query) {
                    $query->whereHas('documents');
                }
            ])
            ->withSum('elements', 'target_nilai') // total_target_nilai
            ->withSum('elements', 'nilai') // total_nilai
            ->get();

        if ($chapterStats->isEmpty()) {
            $stats = [
                'total_ep' => 0,
                'total_ep_memiliki_file' => 0,
                'total_target_nilai' => 0,
                'total_nilai' => 0,
                'percentage_ep_with_files' => 0,
                'percentage_nilai' => 0,
                'color_ep' => 'red',
                'color_nilai' => 'red'
            ];
        } else {
            $totalEp = 0;
            $totalEpMemilikiFile = 0;
            $totalTargetNilai = 0;
            $totalNilai = 0;

            foreach ($chapterStats as $bab) {
                $totalEp += $bab->elements_count ?? 0;
                $totalEpMemilikiFile += $bab->total_ep_memiliki_file ?? 0;
                $totalTargetNilai += $bab->elements_sum_target_nilai ?? 0;
                $totalNilai += $bab->elements_sum_nilai ?? 0;
                $persenEpMemilikiFile = $totalEp > 0 ? round(($totalEpMemilikiFile / $totalEp) * 100, 2) : 0;
                $persenNilai =  $totalTargetNilai > 0 ? round(($totalNilai / $totalTargetNilai) * 100, 2) : 0;

                $stats = [
                    'total_ep' => $totalEp,
                    'total_ep_memiliki_file' => $totalEpMemilikiFile,
                    'total_target_nilai' => $totalTargetNilai,
                    'total_nilai' => $totalNilai,

                    'percentage_ep_with_files' => $persenEpMemilikiFile,
                    'percentage_nilai' => $persenNilai,
                    'color_ep' => $this->getColor($persenEpMemilikiFile) ?? 'red',
                    'color_nilai' => $this->getColor($persenNilai) ?? 'red'
                ];
            }
        }
        return $stats;
    }


    public function getColor($persen)
    {
        $persentase = (int) $persen;
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
        return view('livewire.akreditasi.chapters.stat');
    }
}
